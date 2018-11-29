import { Component, OnInit, ViewChild, ElementRef, Input, Inject } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
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
    MatSidenav,
    MatMenuTrigger,
    MatMenu
} from '@angular/material';
import { SignaturesComponent } from '../signatures/signatures.component';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../service/notification.service';
import { trigger, transition, style, animate } from '@angular/animations';
import { PDFDocumentProxy } from 'ng2-pdf-viewer';
import { CookieService } from 'ngx-cookie-service';


@Component({
    selector: 'app-document',
    templateUrl: 'document.component.html',
    styleUrls: ['document.component.scss'],
    animations: [
        trigger(
            'enterApp',
            [
                transition(
                    ':leave', [
                        style({ transform: 'translateY(0)' }),
                        animate('500ms', style({ transform: 'translateY(-100%)' })),
                    ]
                )]
        ),
        trigger(
            'slideDown',
            [
                transition(
                    ':enter', [
                        style({ transform: 'translateY(-100%)', opacity: 0 }),
                        animate('500ms', style({ transform: 'translateY(0)', 'opacity': 1 }))
                    ]
                ),
                transition(
                    ':leave', [
                        style({ transform: 'translateY(0)', 'opacity': 1 }),
                        animate('500ms', style({ transform: 'translateY(-100%)', 'opacity': 0 })),
                    ]
                )]
        ),
        trigger(
            'slideUp',
            [
                transition(
                    ':enter', [
                        style({ transform: 'translateY(100%)', opacity: 0 }),
                        animate('500ms', style({ transform: 'translateY(0)', 'opacity': 1 }))
                    ]
                ),
                transition(
                    ':leave', [
                        style({ transform: 'translateY(0)', 'opacity': 1 }),
                        animate('500ms', style({ transform: 'translateY(100%)', 'opacity': 0 })),
                    ]
                )]
        )
    ],
})
export class DocumentComponent implements OnInit {
    enterApp = true;
    loadingPage = true;
    pageNum = 1;
    signaturesContent: any = [];
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
    lockSignaturePad = false;
    pdfDataArr: any;
    annotationPadOptions = {
        throttle: 0,
        minWidth: 1,
        maxWidth: 2.5,
        backgroundColor: 'rgba(255, 255, 255, 0)',
        canvasWidth: 768,
        canvasHeight: 270
    };
    freezeSidenavClose = false;
    penColors = [{ id: 'black' }, { id: '#1a75ff' }, { id: '#FF0000' }];

    @Input() mainDocument: any = {};

    @ViewChild('snav') snav: MatSidenav;
    @ViewChild('snavRight') snavRight: MatSidenav;
    @ViewChild('canvas') canvas: ElementRef;
    @ViewChild('canvasWrapper') canvasWrapper: ElementRef;
    @ViewChild('menuTrigger') menuSign: MatMenuTrigger;
    @ViewChild(SignaturePad) signaturePad: SignaturePad;


    constructor(private router: Router, private route: ActivatedRoute, public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        private cookieService: CookieService,
        private sanitization: DomSanitizer, public dialog: MatDialog, private bottomSheet: MatBottomSheet) {
        this.draggable = false;
        if (!this.cookieService.check('maarchParapheurAuth')) {
            this.router.navigate(['/login']);
        }
    }

    ngOnInit(): void {
        setTimeout(() => {
            this.enterApp = false;
        }, 500);
        this.route.params.subscribe(params => {
            if (typeof params['id'] !== 'undefined') {
                this.renderingDoc = true;
                this.loadingDoc = true;
                this.http.get('../rest/documents/' + params['id'])
                    .subscribe((data: any) => {
                        this.initDoc();
                        this.mainDocument = data.document;
                        this.signaturesService.mainDocumentId = this.mainDocument.id;
                        this.actionsList = data.document.actionsAllowed;
                        if (this.signaturesService.signaturesList.length === 0) {
                            this.http.get('../rest/users/' + this.signaturesService.userLogged.id + '/signatures')
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
                        setTimeout(() => {
                            this.loadingPage = false;
                            this.renderingDoc = false;
                        }, 500);
                        this.loadNextDoc();
                    }, (err: any) => {
                        this.notificationService.handleErrors(err);
                    });
            } else {
                this.snav.open();
                this.freezeSidenavClose = true;
            }
        });
    }

    initDoc() {
        this.docList = [];
        this.signaturesService.signaturesContent = [];
        this.signaturesService.notesContent = [];
        this.signaturesService.currentAction = 0;
        this.signaturesService.currentPage = 1;
        this.pageNum = 1;
        this.signaturesContent.currentDoc = 1;
        this.currentDoc = 0;
    }

    pdfRender(document: any) {
        const binary_string = window.atob(document.encodedDocument);
        const len = binary_string.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
        }
        this.pdfDataArr = bytes.buffer;
    }

    pdfRendered(pdf: PDFDocumentProxy) {
        this.totalPages = pdf.numPages;
        this.signaturesService.totalPage = this.totalPages;
    }

    pageRendered(e: any) {

        this.signaturesService.workingAreaWidth = e.target.clientWidth;
        this.signaturesService.workingAreaHeight = e.target.clientHeight;

        this.canvasWrapper.nativeElement.style.width = this.signaturesService.workingAreaWidth + 'px';
        this.canvasWrapper.nativeElement.style.height = this.signaturesService.workingAreaHeight + 'px';

        this.annotationPadOptions.canvasWidth = this.signaturesService.workingAreaWidth;
        this.annotationPadOptions.canvasHeight = this.signaturesService.workingAreaHeight;

        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        this.annotationPadOptions.canvasHeight = this.annotationPadOptions.canvasHeight * ratio;
        this.annotationPadOptions.canvasWidth = this.annotationPadOptions.canvasWidth * ratio;

        if (this.signaturePad !== undefined) {
            this.signaturePad.set('canvasWidth', this.annotationPadOptions.canvasWidth);
            this.signaturePad.set('canvasHeight', this.annotationPadOptions.canvasHeight);
        }

        this.signaturesService.signWidth = this.signaturesService.workingAreaWidth / 4.5;

        this.renderingDoc = false;
    }

    prevPage() {
        this.pageNum--;
        if (this.pageNum === 0) {
            this.pageNum = 1;
        } else {
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
    }

    addAnnotation() {
        if (!this.signaturesService.lockNote && this.currentDoc === 0) {
            this.signaturesService.annotationMode = true;
            this.signaturePadPosX = 0;
            this.signaturePadPosY = 0;
            this.signaturesService.lockNote = true;
        }
    }

    moveSign(event: any, i: number) {
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionX = event.x;
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionY = event.y;
    }

    cloneSign(i: number) {

        const r = confirm('Voulez-vous apposer la signature sur les autres pages ?');

        if (r) {
            this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].inAllPage = true;
            this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].token = Math.random().toString(36).substr(2, 9);

            for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                if (!this.signaturesService.signaturesContent[index]) {
                  this.signaturesService.signaturesContent[index] = [];
                }
                if (index !== this.signaturesService.currentPage) {
                    this.signaturesService.signaturesContent[index].push(JSON.parse(JSON.stringify(this.signaturesService.signaturesContent[this.signaturesService.currentPage][i])));
                }
              }
        }
        this.menuSign.closeMenu();
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

    deleteSignature(i: number) {

        if (this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].inAllPage === true) {
            const token = this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].token;
            const r = confirm('Voulez-vous supprimer la signature sur les autres pages ?');

            if (r) {

                for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                    if (!this.signaturesService.signaturesContent[index]) {
                        this.signaturesService.signaturesContent[index] = [];
                    }
                    for (let index2 = 0; index2 <= this.signaturesService.signaturesContent[index].length; index2++) {
                        if (this.signaturesService.signaturesContent[index][index2]) {
                            if (token === this.signaturesService.signaturesContent[index][index2].token) {
                                this.signaturesService.signaturesContent[index].splice(index2, 1);
                            }
                        }
                    }
                }
            } else {
                this.signaturesService.signaturesContent[this.signaturesService.currentPage].splice(i, 1);
            }
        } else {
            this.signaturesService.signaturesContent[this.signaturesService.currentPage].splice(i, 1);
        }
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
                'fullPath': this.signaturePad.toDataURL('image/svg+xml'),
                'positionX': (this.signaturePadPosX * 100) / this.annotationPadOptions.canvasWidth,
                'positionY': (this.signaturePadPosY * 100) / this.annotationPadOptions.canvasHeight,
                'height': this.annotationPadOptions.canvasHeight,
                'width': 768,
            }
        );
        this.signaturePad.clear();
        if (this.scale > 1) {
            this.renderingDoc = true;
        }
        this.scale = 1;
        this.signaturesService.annotationMode = false;
        this.signaturesService.lockNote = false;
        this.notificationService.success('Annotation ajoutée');
    }

    cancelAnnotation() {
        setTimeout(() => {
            this.signaturesService.annotationMode = false;
            this.signaturePad.clear();
            this.scale = 1;
            this.signaturesService.lockNote = false;
        }, 200);
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
        this.signaturesService.showPad = false;
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
            if (result) {
                this.signaturesService.signaturesContent = [];
                this.signaturesService.notesContent = [];
                this.notificationService.success('Annotations / signatures supprimées du document');
            }
        });
    }

    undoTag() {
        if (this.signaturesService.notesContent[this.pageNum]) {
            this.signaturesService.notesContent[this.pageNum].pop();
        }
    }

    loadNextDoc () {
        if (this.docList[this.currentDoc + 1] && this.docList[this.currentDoc + 1].id && this.docList[this.currentDoc + 1].encodedDocument === '') {
            this.http.get('../rest/attachments/' + this.docList[this.currentDoc + 1].id)
                .subscribe((dataPj: any) => {
                    this.docList[this.currentDoc + 1] = dataPj.attachment;
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
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
        this.renderingDoc = true;
        this.lockSignaturePad = true;
        this.scale = 2;
    }

    zoomMinus() {
        this.renderingDoc = true;
        this.signaturePad.clear();
        this.lockSignaturePad = true;
        this.scale = 1;
    }

    undo() {
        const data = this.signaturePad.toData();
        if (data) {
            data.pop(); // remove the last dot or line
            this.signaturePad.fromData(data);
        }
    }

    // USE TO PREVENT ISSUE IN MOBILE
    openMenu(menu: MatMenuTrigger) {
        menu.openMenu();
    }
}

@Component({
    templateUrl: '../modal/warn-modal.component.html',
    styleUrls: ['../modal/warn-modal.component.scss']
})
export class WarnModalComponent {
    disableState = false;

    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public http: HttpClient, public dialogRef: MatDialogRef<WarnModalComponent>, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    confirmDoc () {
        const signatures: any[] = [];
        if (this.signaturesService.currentAction > 0) {
            for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                if (this.signaturesService.signaturesContent[index]) {
                    this.signaturesService.signaturesContent[index].forEach((signature: any) => {
                        signatures.push(
                            {
                                'encodedImage'  : signature.encodedSignature,
                                'width'         : (this.signaturesService.signWidth * 100) / signature.pdfAreaX,
                                'positionX'     : (signature.positionX * 100) / signature.pdfAreaX,
                                'positionY'     : (signature.positionY * 100) / signature.pdfAreaY,
                                'type'          : 'PNG',
                                'page'          : index
                            }
                        );
                    });
                }
                if (this.signaturesService.notesContent[index]) {
                    this.signaturesService.notesContent[index].forEach((note: any) => {
                        signatures.push(
                            {
                                'encodedImage'  : note.fullPath,
                                'width'         : note.width,
                                'positionX'     : note.positionX,
                                'positionY'     : note.positionY,
                                'type'          : 'SVG',
                                'page'          : index
                            }
                        );
                    });
                }
            }
            this.disableState = true;
            this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, {'signatures': signatures})
                .subscribe(() => {
                    this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                    if (this.signaturesService.documentsListCount > 0) {
                        this.signaturesService.documentsListCount--;
                    }
                    this.disableState = false;
                    this.dialogRef.close('sucess');
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                    this.disableState = false;
                });
        } else {
            this.dialogRef.close('sucess');
        }
    }
}

@Component({
    templateUrl: '../modal/confirm-modal.component.html',
    styleUrls: ['../modal/confirm-modal.component.scss']
})
export class ConfirmModalComponent {
    disableState = false;

    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public http: HttpClient, public dialogRef: MatDialogRef<ConfirmModalComponent>, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    confirmDoc () {
        const signatures: any[] = [];
        if (this.signaturesService.currentAction > 0) {
            for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                if (this.signaturesService.signaturesContent[index]) {
                    this.signaturesService.signaturesContent[index].forEach((signature: any) => {
                        signatures.push(
                            {
                                'encodedImage'  : signature.encodedSignature,
                                'width'         : (this.signaturesService.signWidth * 100) / signature.pdfAreaX,
                                'positionX'     : (signature.positionX * 100) / signature.pdfAreaX,
                                'positionY'     : (signature.positionY * 100) / signature.pdfAreaY,
                                'type'          : 'PNG',
                                'page'          : index,
                            }
                        );
                    });
                }
                if (this.signaturesService.notesContent[index]) {
                    this.signaturesService.notesContent[index].forEach((note: any) => {
                        signatures.push(
                            {
                                'encodedImage'  : note.fullPath,
                                'width'         : note.width,
                                'positionX'     : note.positionX,
                                'positionY'     : note.positionY,
                                'type'          : 'SVG',
                                'page'          : index,
                            }
                        );
                    });
                }
            }
            this.disableState = true;
            this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, {'signatures': signatures})
                .subscribe(() => {
                    this.disableState = false;
                    this.dialogRef.close('sucess');
                    this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                    if (this.signaturesService.documentsListCount > 0) {
                        this.signaturesService.documentsListCount--;
                    }
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                    this.disableState = false;
                });
        } else {
            this.dialogRef.close('sucess');
        }
    }
}

@Component({
    templateUrl: '../modal/success-info-valid.html',
    styleUrls: ['../modal/success-info-valid.scss']
})
export class SuccessInfoValidBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(private router: Router, public signaturesService: SignaturesContentService, private bottomSheetRef: MatBottomSheetRef<SuccessInfoValidBottomSheetComponent>) { }
     ngOnInit(): void {
        setTimeout(() => {
            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList]) {
                this.router.navigate(['/documents/' + this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].id]);
            } else {
                this.router.navigate(['/documents']);
            }
            this.bottomSheetRef.dismiss();
        }, 2000);
     }
}

@Component({
    templateUrl: '../modal/reject-info.html',
    styleUrls: ['../modal/reject-info.scss']
})
export class RejectInfoBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(private router: Router, public signaturesService: SignaturesContentService, private bottomSheetRef: MatBottomSheetRef<RejectInfoBottomSheetComponent>) { }
    ngOnInit(): void {
        setTimeout(() => {
            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList]) {
                this.router.navigate(['/documents/' + this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].id]);
            } else {
                this.router.navigate(['/documents']);
            }
            this.bottomSheetRef.dismiss();
        }, 2000);
     }
}
