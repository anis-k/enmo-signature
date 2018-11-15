import { Component, OnInit, ViewChild, ElementRef, Input, Inject } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import * as PDFJS from 'pdfjs-dist/build/pdf.js';
import { DomSanitizer } from '@angular/platform-browser';
import * as $ from 'jquery';
import { SignaturePad } from 'angular2-signaturepad/signature-pad';
import {
    MatDialogRef,
    MatDialog,
    MAT_DIALOG_DATA,
    MatBottomSheet,
    MatBottomSheetRef,
    MatBottomSheetConfig,
    MatSnackBar,
    MatSidenav
} from '@angular/material';
import { SignaturesComponent } from '../signatures/signatures.component';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';

@Component({
    selector: 'app-document',
    templateUrl: 'document.component.html',
    styleUrls: ['document.component.styl'],
})

export class DocumentComponent implements OnInit {

    pdf: any;
    pageNum = 1;
    signaturesContent: any = [];
    pageRendering = false;
    pageNumPending: null;
    scale = 1;
    totalPages: number;
    draggable: boolean;
    loadingDoc = true;
    renderingDoc = true;
    signaturePadPosX = 0;
    signaturePadPosY = 50;
    currentDoc = 0;
    docList: any = [];
    actionsList: any = [];
    currentAction = 0;
    lockSignaturePad = false;
    annotationPadOptions = {
        throttle: 0,
        minWidth: 0.8,
        maxWidth: 1,
        backgroundColor: 'rgba(255, 255, 255, 0)',
        canvasWidth: 768,
        canvasHeight: 270
    };
    freezeSidenavClose = false;
    penColors = [{ id: 'black' }, { id: '#1a75ff' }, { id: '#FF0000' }];

    @Input() mainDocument: any = {};

    @ViewChild('pdfpage') elPdfContainer: ElementRef;
    @ViewChild('snav') snav: MatSidenav;
    @ViewChild('snavRight') snavRight: MatSidenav;
    @ViewChild('canvas') canvas: ElementRef;
    @ViewChild('canvasWrapper') canvasWrapper: ElementRef;
    @ViewChild(SignaturePad) signaturePad: SignaturePad;

    public context: CanvasRenderingContext2D;

    constructor(private router: Router, private route: ActivatedRoute, public http: HttpClient,
        public snackBar: MatSnackBar, public signaturesService: SignaturesContentService,
        private sanitization: DomSanitizer, public dialog: MatDialog, private bottomSheet: MatBottomSheet) {
        this.draggable = false;
        PDFJS.GlobalWorkerOptions.workerSrc = './node_modules/pdfjs-dist/build/pdf.worker.js';
    }

    // TO DO REMOVE DEFINE EXEMPLE
    ngOnInit(): void {
        this.route.params.subscribe(params => {
            if (typeof params['id'] !== 'undefined') {
                this.renderingDoc = true;
                this.loadingDoc = true;
                this.http.get('../rest/documents/' + params['id'])
                    .subscribe((data: any) => {
                        this.docList = [];
                        this.signaturesService.signaturesContent = [];
                        this.signaturesService.notesContent = [];
                        this.signaturesService.currentAction = 0;
                        this.mainDocument = data.document;
                        this.signaturesService.mainDocumentId = this.mainDocument.id;
                        this.actionsList = data.document.actionsAllowed;
                        if (this.signaturesService.signaturesList.length === 0) {
                            this.http.get('../rest/users/' + '1' + '/signatures')
                            .subscribe((dataSign: any) => {
                                this.signaturesService.signaturesList = dataSign.signatures;
                                this.signaturesService.loadingSign = false;
                            });
                        }
                        this.docList.push({ 'id': this.mainDocument.id, 'encodedDocument': this.mainDocument.encodedDocument, 'subject': this.mainDocument.subject });
                        this.mainDocument.attachments.forEach((attach: any, index: any) => {
                            this.docList.push({ 'id': attach.id, 'encodedDocument': '', 'title': '' });
                        });
                        this.loadingDoc = false;
                        this.pdfRender(this.docList[this.currentDoc]);
                        this.context = (<HTMLCanvasElement>this.canvas.nativeElement).getContext('2d');
                        setTimeout(() => {
                            this.renderingDoc = false;
                        }, 500);
                        this.loadNextDoc();
                    }, (err: any) => {
                        if (err.status === 401) {
                            this.router.navigate(['/login']);
                            this.snackBar.open('Veuillez vous reconnecter', null,
                              {
                                duration: 3000,
                                panelClass: 'center-snackbar',
                                verticalPosition: 'top'
                              }
                            );
                          }
                    });
            } else {
                this.snav.open();
                this.freezeSidenavClose = true;
            }
        });
    }


    pdfRender(document: any) {
        const pdfData = atob(document.encodedDocument);
        PDFJS.getDocument({ data: pdfData }).then((_pdfDoc: any) => {
            this.pdf = _pdfDoc;
            this.totalPages = this.pdf.pdfInfo.numPages;
            this.signaturesService.totalPage = this.totalPages;
            this.renderPage(this.pageNum, this.canvas.nativeElement, 1);
        });
    }

    renderPage(pageNumber: any, canvas: any, scale: number) {
        this.pdf.getPage(pageNumber).then((page: any) => {
            const viewport = page.getViewport(scale);
            const ctx = this.context;
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            this.canvas.nativeElement.style.width = 768 * scale + 'px';
            this.canvas.nativeElement.style.height = 'auto';
            const renderTask = page.render(renderContext);
            renderTask.then(() => {
                this.signaturesService.signWidth = viewport.width / 5;
            });
        });
    }

    prevPage() {
        this.renderingDoc = true;
        this.pageNum--;
        if (this.pageNum === 0) {
            this.pageNum = 1;
        } else {
            this.renderPage(this.pageNum, this.canvas, 1);
            setTimeout(() => {
                this.renderingDoc = false;
            }, 500);
        }

        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }
    }

    nextPage() {
        this.renderingDoc = true;
        if (this.pageNum >= this.totalPages) {
            this.pageNum = this.totalPages;
        } else {
            this.pageNum++;
            this.renderPage(this.pageNum, this.canvas, 1);
            setTimeout(() => {
                this.renderingDoc = false;
            }, 500);
        }

        // only for main document
        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }
    }

    nextDoc() {
        this.renderingDoc = true;
        this.signaturesService.isTaggable = false;
        this.pageNum = 1;
        this.currentDoc++;
        this.pdfRender(this.docList[this.currentDoc]);
        setTimeout(() => {
            this.renderingDoc = false;
        }, 500);
        this.loadNextDoc();
    }

    prevDoc() {
        this.renderingDoc = true;
        this.pageNum = 1;
        this.currentDoc--;
        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = 1;
            this.signaturesService.isTaggable = true;
        }
        this.pdfRender(this.docList[this.currentDoc]);
        setTimeout(() => {
            this.renderingDoc = false;
        }, 500);
    }

    addAnnotation(e: any) {
        if (!this.signaturesService.lockNote && this.currentDoc === 0) {
            this.annotationPadOptions.canvasWidth = 768 * this.scale;
            this.annotationPadOptions.canvasHeight = $('.pdf-page-canvas').height();
            // this.scale = 1;
            this.signaturesService.annotationMode = true;
            // this.renderPage(this.pageNum, this.canvas.nativeElement, this.scale);
            this.signaturePadPosX = 0;
            this.signaturePadPosY = 0;
            this.signaturesService.lockNote = true;
        }
    }

    moveSign(event: any, i: number) {
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionX = event.x;
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionY = event.y;
    }

    moveNote(event: any, i: number) {
        this.signaturesService.notesContent[this.signaturesService.currentPage][i].positionX = event.x;
        this.signaturesService.notesContent[this.signaturesService.currentPage][i].positionY = event.y;
    }

    movePad(event: any) {
        this.signaturePadPosX = event.x;
        this.signaturePadPosY = event.y;
        $('.page-viewer, .canvas-wrapper, .mat-sidenav-content').css({ 'overflow': 'auto' });
    }

    deleteSignature(index: number) {
        this.signaturesService.signaturesContent[this.signaturesService.currentPage].splice(index, 1);
    }

    deleteNote(index: number) {
        this.signaturesService.notesContent[this.signaturesService.currentPage].splice(index, 1);
    }

    validateAnnotation() {
        if (!this.signaturesService.notesContent[this.signaturesService.currentPage]) {
            this.signaturesService.notesContent[this.signaturesService.currentPage] = [];
        }
        this.signaturesService.notesContent[this.signaturesService.currentPage].push(
            {
                'fullPath': this.signaturePad.toDataURL('image/npg'),
                'positionX': this.signaturePadPosX,
                'positionY': this.signaturePadPosY,
                'height': this.annotationPadOptions.canvasHeight,
                'width': this.annotationPadOptions.canvasWidth,
            }
        );
        this.signaturePad.clear();
        this.scale = 1;
        this.signaturesService.annotationMode = false;
        this.signaturesService.lockNote = false;
        this.renderPage(this.pageNum, this.canvas.nativeElement, this.scale);
        this.snackBar.open('Annotation ajoutée', null,
            {
                duration: 3000,
                panelClass: 'center-snackbar',
                verticalPosition: 'top'
            }
        );
    }

    cancelAnnotation() {
        this.signaturePad.clear();
        // this.scale = 1;
        this.signaturesService.annotationMode = false;
        this.signaturesService.lockNote = false;
        // this.renderPage(this.pageNum, this.canvas.nativeElement, this.scale);
    }

    freezDoc() {
        $('.page-viewer, .canvas-wrapper, .mat-sidenav-content').css({ 'overflow': 'hidden' });
    }

    onColorChange(entry: any) {
        this.signaturePad.set('penColor', entry.id);
    }

    onDotChange(entry: any) {
        this.signaturePad.set('minWidth', parseFloat(entry));
        this.signaturePad.set('maxWidth', parseFloat(entry) + 2);
    }

    openDialog(): void {
        const dialogRef = this.dialog.open(WarnModalComponent, {
            width: '350px',
            data: {}
        });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'sucess') {
                const config: MatBottomSheetConfig = {
                    disableClose: true,
                    direction: 'ltr'
                };
                this.bottomSheet.open(RejectInfoBottomSheetComponent, config);
            } else if (result === 'annotation') {
                this.signaturesService.annotationMode = true;
                this.signaturesService.lockNote = true;
            }
        });
    }

    confirmDialog(mode: any): void {
        const dialogRef = this.dialog.open(ConfirmModalComponent, {
            width: '350px',
            data: { msg: 'Êtes-vous sûr  ?' }
        });

        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                const config: MatBottomSheetConfig = {
                    disableClose: true,
                    direction: 'ltr'
                };
                this.bottomSheet.open(SuccessInfoValidBottomSheetComponent, config);
            }
        });
    }

    openDrawer(): void {
        if (this.currentDoc > 0) {
            this.currentDoc = 0;
            this.pageNum = 1;
            this.pdfRender(this.docList[this.currentDoc]);
        }
        this.signaturesService.showSign = true;
        const config: MatBottomSheetConfig = {
            disableClose: false,
            direction: 'ltr'
        };
        this.bottomSheet.open(SignaturesComponent, config);
    }

    removeTags() {
        this.signaturesService.currentAction = 0;
        const dialogRef = this.dialog.open(ConfirmModalComponent, {
            width: '350px',
            data: { msg: 'Effacer toutes les annotations et signatures ?' }
        });

        dialogRef.afterClosed().subscribe(result => {
            console.log('The dialog was closed');
            if (result) {
                this.signaturesService.signaturesContent = [];
                this.signaturesService.notesContent = [];
                this.snackBar.open('Annotations / signatures supprimées du document', null,
                    {
                        duration: 3000,
                        panelClass: 'center-snackbar',
                        verticalPosition: 'top'
                    }
                );
            }
        });
    }

    loadNextDoc () {
        if (this.docList[this.currentDoc + 1] && this.docList[this.currentDoc + 1].id && this.docList[this.currentDoc + 1].encodedDocument === '') {
            this.http.get('../rest/attachments/' + this.docList[this.currentDoc + 1].id)
                .subscribe((dataPj: any) => {
                    this.docList[this.currentDoc + 1] = dataPj.attachment;
                }, () => {
                    console.log('error !');
                });
        }
    }

    launchEvent(action: any) {
        this.signaturesService.currentAction = action.id;
        this[action.event]();
    }

    test() {
        if (this.lockSignaturePad) {
            this.lockSignaturePad = false;
            this.signaturePad.on();
        } else {
            this.lockSignaturePad = true;
            this.signaturePad.off();
        }
    }

    zoomPlus() {
        this.lockSignaturePad = true;
        this.scale = 2;
        this.renderPage(this.pageNum, this.canvas.nativeElement, this.scale);
        this.signaturePad.set('canvasWidth', 768 * this.scale);
        this.signaturePad.set('canvasHeight', this.annotationPadOptions.canvasHeight * this.scale);
    }

    zoomMinus() {
        this.lockSignaturePad = true;
        this.scale = 1;
        this.renderPage(this.pageNum, this.canvas.nativeElement, this.scale);
        this.signaturePad.set('canvasWidth', 768 * this.scale);
        this.signaturePad.set('canvasHeight', this.annotationPadOptions.canvasHeight * this.scale);
    }
}

@Component({
    templateUrl: '../modal/warn-modal.component.html',
    styleUrls: ['../modal/warn-modal.component.styl']
})
export class WarnModalComponent {
    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public http: HttpClient, public dialogRef: MatDialogRef<WarnModalComponent>, public signaturesService: SignaturesContentService) { }

    confirmDoc () {
        const signatures: any[] = [];
        console.log(this.signaturesService.currentAction);
        if (this.signaturesService.currentAction > 0) {
            for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                if (this.signaturesService.signaturesContent[index]) {
                    this.signaturesService.signaturesContent[index].forEach((signature: any) => {
                        signatures.push(
                            {
                                'fullPath': signature.encodedSignature,
                                'height': 'auto',
                                'width': this.signaturesService.signWidth,
                                'positionX': 1,
                                'positionY': 1,
                                'page': index,
                            }
                        );
                    });
                }
                if (this.signaturesService.notesContent[index]) {
                    this.signaturesService.notesContent[index].forEach((note: any) => {
                        signatures.push(
                            {
                                'fullPath': note.fullPath,
                                'height': note.height,
                                'width': note.width,
                                'positionX': note.positionX,
                                'positionY': note.positionY,
                                'page': index,
                            }
                        );
                    });
                }
                this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/action', {'action_id': this.signaturesService.currentAction, 'signatures': signatures})
                    .subscribe(() => {
                        this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                        this.signaturesService.documentsListCount--;
                        this.dialogRef.close('sucess');
                    }, (err: any) => {
                        console.log(err);
                    });
            }
        } else {
            this.dialogRef.close('sucess');
        }
    }
}

@Component({
    templateUrl: '../modal/confirm-modal.component.html',
    styleUrls: ['../modal/confirm-modal.component.styl']
})
export class ConfirmModalComponent {
    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public http: HttpClient, public dialogRef: MatDialogRef<ConfirmModalComponent>, public signaturesService: SignaturesContentService) { }

    confirmDoc () {
        const signatures: any[] = [];
        console.log(this.signaturesService.currentAction);
        if (this.signaturesService.currentAction > 0) {
            for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                if (this.signaturesService.signaturesContent[index]) {
                    this.signaturesService.signaturesContent[index].forEach((signature: any) => {
                        signatures.push(
                            {
                                'fullPath': signature.encodedSignature,
                                'height': 'auto',
                                'width': this.signaturesService.signWidth,
                                'positionX': 1,
                                'positionY': 1,
                                'page': index,
                            }
                        );
                    });
                }
                if (this.signaturesService.notesContent[index]) {
                    this.signaturesService.notesContent[index].forEach((note: any) => {
                        signatures.push(
                            {
                                'fullPath': note.fullPath,
                                'height': note.height,
                                'width': note.width,
                                'positionX': note.positionX,
                                'positionY': note.positionY,
                                'page': index,
                            }
                        );
                    });
                }
                this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/action', {'action_id': this.signaturesService.currentAction, 'signatures': signatures})
                    .subscribe(() => {
                        this.dialogRef.close('sucess');
                        this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                        this.signaturesService.documentsListCount--;
                    }, (err: any) => {
                        console.log(err);
                    });
            }
        } else {
            this.dialogRef.close('sucess');
        }
    }
}

@Component({
    templateUrl: '../modal/success-info-valid.html',
    styleUrls: ['../modal/success-info-valid.styl']
})
export class SuccessInfoValidBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(private router: Router, public signaturesService: SignaturesContentService, private bottomSheetRef: MatBottomSheetRef<SuccessInfoValidBottomSheetComponent>) { }
     ngOnInit(): void {
        setTimeout(() => {
            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList]) {
                this.router.navigate(['/document/' + this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].id]);
            }
            this.bottomSheetRef.dismiss();
        }, 2000);
     }
}

@Component({
    templateUrl: '../modal/reject-info.html',
    styleUrls: ['../modal/reject-info.styl']
})
export class RejectInfoBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(private router: Router, public signaturesService: SignaturesContentService, private bottomSheetRef: MatBottomSheetRef<RejectInfoBottomSheetComponent>) { }
    ngOnInit(): void {
        setTimeout(() => {
            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList]) {
                this.router.navigate(['/document/' + this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].id]);
            }
            this.bottomSheetRef.dismiss();
        }, 2000);
     }
}
