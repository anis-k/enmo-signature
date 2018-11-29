import { Component, OnInit, ViewChild, ElementRef, Input, Inject } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { DomSanitizer } from '@angular/platform-browser';
import { MatDialog, MatBottomSheet, MatBottomSheetConfig, MatSidenav } from '@angular/material';
import { SignaturesComponent } from '../signatures/signatures.component';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../service/notification.service';
import { trigger, transition, style, animate } from '@angular/animations';
import { PDFDocumentProxy } from 'ng2-pdf-viewer';
import { CookieService } from 'ngx-cookie-service';
import { DocumentNotePadComponent } from '../documentNotePad/document-note-pad.component';
import { WarnModalComponent } from '../modal/warn-modal.component';
import { RejectInfoBottomSheetComponent } from '../modal/reject-info.component';
import { ConfirmModalComponent } from '../modal/confirm-modal.component';
import { SuccessInfoValidBottomSheetComponent } from '../modal/success-info-valid.component';


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
    totalPages: number;
    draggable: boolean;
    loadingDoc = true;
    signaturePadPosX = 0;
    signaturePadPosY = 50;
    currentDoc = 0;
    docList: any = [];
    actionsList: any = [];
    pdfDataArr: any;
    freezeSidenavClose = false;

    @Input() mainDocument: any = {};

    @ViewChild('snav') snav: MatSidenav;
    @ViewChild('snavRight') snavRight: MatSidenav;
    @ViewChild('canvas') canvas: ElementRef;
    @ViewChild('canvasWrapper') canvasWrapper: ElementRef;

    @ViewChild('appDocumentNotePad') appDocumentNotePad: DocumentNotePadComponent;


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
                this.signaturesService.renderingDoc = true;
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
                            this.signaturesService.renderingDoc = false;
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

        if (this.appDocumentNotePad) {
            this.appDocumentNotePad.resizePad();
        }

        this.signaturesService.signWidth = this.signaturesService.workingAreaWidth / 4.5;

        this.signaturesService.renderingDoc = false;
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
        this.signaturesService.renderingDoc = true;
        this.signaturesService.isTaggable = false;
        this.pageNum = 1;
        this.currentDoc++;
        this.pdfRender(this.docList[this.currentDoc]);
        this.loadNextDoc();
    }

    prevDoc() {
        this.signaturesService.renderingDoc = true;
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
            this.signaturesService.lockNote = true;
        }
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
}
