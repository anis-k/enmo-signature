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
import { ActivatedRoute } from '@angular/router';
import { HttpClient } from '@angular/common/http';

@Component({
    selector: 'app-document',
    templateUrl: 'document.component.html',
    styleUrls: ['document.component.styl']
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
    signaturePadPosX = 0;
    signaturePadPosY = 0;
    currentDoc = 0;
    docList: any = [];
    annotationPadOptions = {
        throttle: 0,
        minWidth: 0.8,
        maxWidth: 1,
        backgroundColor: 'rgba(255, 255, 255, 0)',
        canvasWidth: 380,
        canvasHeight: 270
    };
    freezeSidenavClose = false;
    penColors = [{ id: 'black' }, { id: 'orange' }, { id: '#FF0000' }];

    @Input() mainDocument: any = {};

    @ViewChild('pdfpage') elPdfContainer: ElementRef;
    @ViewChild('snav') snav: MatSidenav;
    @ViewChild('canvas') canvas: ElementRef;
    @ViewChild('canvasWrapper') canvasWrapper: ElementRef;
    @ViewChild(SignaturePad) signaturePad: SignaturePad;

    public context: CanvasRenderingContext2D;

    constructor(private route: ActivatedRoute, public http: HttpClient,
        public snackBar: MatSnackBar, public signaturesService: SignaturesContentService,
        private sanitization: DomSanitizer, public dialog: MatDialog, private bottomSheet: MatBottomSheet) {
        this.draggable = false;
        PDFJS.GlobalWorkerOptions.workerSrc = './node_modules/pdfjs-dist/build/pdf.worker.js';
    }

    // TO DO REMOVE DEFINE EXEMPLE
    ngOnInit(): void {
        this.route.params.subscribe(params => {
            if (typeof params['id'] !== 'undefined') {
                this.http.get('../rest/documents/' + params['id'])
                    .subscribe((data: any) => {
                        this.mainDocument = data.document;
                        this.docList.push({ 'id': this.mainDocument.id, 'encodedDocument': this.mainDocument.encodedDocument, 'title': this.mainDocument.subject });
                        this.mainDocument.attachments.forEach((attach: any, index: any) => {
                            this.docList.push({ 'id': attach.id, 'encodedDocument': '', 'title': '' });
                        });
                        this.pdfRender(this.docList[this.currentDoc]);
                        this.context = (<HTMLCanvasElement>this.canvas.nativeElement).getContext('2d');
                        this.loadNextDoc();
                    }, () => {
                        console.log('error !');
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
            this.annotationPadOptions.canvasWidth = viewport.width;
            this.annotationPadOptions.canvasHeight = viewport.height / 2;
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            this.canvas.nativeElement.style.width = 'auto';
            this.canvas.nativeElement.style.height = '100vh';
            const renderTask = page.render(renderContext);
            renderTask.then(() => {
                this.signaturesService.signWidth = viewport.width / 5;
                this.loadingDoc = false;
            });
        });
    }

    prevPage() {
        this.pageNum--;
        if (this.pageNum === 0) {
            this.pageNum = 1;
        } else {
            this.renderPage(this.pageNum, this.canvas, 1);
        }

        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }
    }

    nextPage() {
        if (this.pageNum >= this.totalPages) {
            this.pageNum = this.totalPages;
        } else {
            this.pageNum++;
            this.renderPage(this.pageNum, this.canvas, 1);
        }

        // only for main document
        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }
    }

    nextDoc() {
        this.loadingDoc = true;
        this.signaturesService.isTaggable = false;
        this.pageNum = 1;
        this.currentDoc++;
        this.pdfRender(this.docList[this.currentDoc]);
        this.loadNextDoc();
    }

    prevDoc() {
        this.loadingDoc = true;
        this.pageNum = 1;
        this.currentDoc--;
        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = 1;
            this.signaturesService.isTaggable = true;
        }
        this.pdfRender(this.docList[this.currentDoc]);
    }

    addAnnotation(e: any) {
        if (!this.signaturesService.lockNote && this.currentDoc === 0) {
            this.scale = 1;
            this.signaturesService.annotationMode = true;
            this.renderPage(this.pageNum, this.canvas.nativeElement, this.scale);
            this.signaturePadPosX = e.layerX - 40;
            this.signaturePadPosY = e.layerY - 40;
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
        this.scale = 1;
        this.signaturesService.annotationMode = false;
        this.signaturesService.lockNote = false;
        this.renderPage(this.pageNum, this.canvas.nativeElement, this.scale);
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
                setTimeout(() => {
                    this.bottomSheet.dismiss();
                }, 2000);
            } else if (result === 'annotation') {
                this.signaturesService.annotationMode = true;
                this.signaturesService.lockNote = true;
            }
        });
    }

    confirmDialog(): void {
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
                setTimeout(() => {
                    this.bottomSheet.dismiss();
                }, 2000);
            }
        });
    }

    openDrawer(): void {
        if (this.signaturesService.signaturesList.length === 0) {
            this.http.get('../rest/users/' + '1' + '/signatures')
            .subscribe((data: any) => {
                this.signaturesService.signaturesList = data.signatures;
            });
        }
        this.signaturesService.showSign = true;
        const config: MatBottomSheetConfig = {
            disableClose: false,
            direction: 'ltr'
        };
        this.bottomSheet.open(SignaturesComponent, config);
    }

    removeTags() {
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
                    this.snackBar.open('Pièce jointe chargé', null,
                    {
                        duration: 3000,
                        panelClass: 'center-snackbar',
                        verticalPosition: 'top'
                    }
                );
                }, () => {
                    console.log('error !');
                });
        }
    }
}

@Component({
    templateUrl: '../modal/warn-modal.component.html',
    styleUrls: ['../modal/warn-modal.component.styl']
})
export class WarnModalComponent {
    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<WarnModalComponent>) { }
}

@Component({
    templateUrl: '../modal/confirm-modal.component.html',
    styleUrls: ['../modal/confirm-modal.component.styl']
})
export class ConfirmModalComponent {
    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<ConfirmModalComponent>) { }
}

@Component({
    templateUrl: '../modal/success-info-valid.html',
    styleUrls: ['../modal/success-info-valid.styl']
})
export class SuccessInfoValidBottomSheetComponent {
    constructor(private bottomSheetRef: MatBottomSheetRef<SuccessInfoValidBottomSheetComponent>) { }
}

@Component({
    templateUrl: '../modal/reject-info.html',
    styleUrls: ['../modal/reject-info.styl']
})
export class RejectInfoBottomSheetComponent {
    constructor(private bottomSheetRef: MatBottomSheetRef<RejectInfoBottomSheetComponent>) { }
}
