import { Component, OnInit, ViewChild, ElementRef, Input, HostListener } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { DomSanitizer } from '@angular/platform-browser';
import { MatDialog, MatBottomSheet, MatBottomSheetConfig, MatSidenav } from '@angular/material';
import { SignaturesComponent } from '../signatures/signatures.component';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../service/notification.service';
import { trigger, transition, style, animate } from '@angular/animations';
import { CookieService } from 'ngx-cookie-service';
import { DocumentNotePadComponent } from '../documentNotePad/document-note-pad.component';
import { WarnModalComponent } from '../modal/warn-modal.component';
import { RejectInfoBottomSheetComponent } from '../modal/reject-info.component';
import { ConfirmModalComponent } from '../modal/confirm-modal.component';
import { SuccessInfoValidBottomSheetComponent } from '../modal/success-info-valid.component';
import { SimplePdfViewerComponent } from 'simple-pdf-viewer';


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

    enterApp            : boolean   = true;
    loadingPage         : boolean   = true;
    pageNum             : number    = 1;
    signaturesContent   : any       = [];
    totalPages          : number;
    draggable           : boolean;
    loadingDoc          : boolean   = true;
    currentDoc          : number    = 0;
    docList             : any       = [];
    actionsList         : any       = [];
    pdfDataArr          : any;
    freezeSidenavClose  : boolean   = false;
    disableState        : boolean   = true;
    startX = 0;
    startY = 0;


    @Input() mainDocument: any = {};

    @ViewChild('snav') snav: MatSidenav;
    @ViewChild('snavRight') snavRight: MatSidenav;
    @ViewChild('canvas') canvas: ElementRef;
    @ViewChild('canvasWrapper') canvasWrapper: ElementRef;
    @ViewChild('appDocumentNotePad') appDocumentNotePad: DocumentNotePadComponent;
    @ViewChild(SimplePdfViewerComponent) private pdfViewer: SimplePdfViewerComponent;

    @HostListener('mousedown', ['$event']) protected onPMouseDown(event: any) {
        event.preventDefault();
    }


    constructor(private router: Router, private route: ActivatedRoute, public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        private cookieService: CookieService,
        private sanitizer: DomSanitizer, public dialog: MatDialog, private bottomSheet: MatBottomSheet) {
        this.draggable = false;
        PDFJS.workerSrc = './../node_modules/simple-pdf-viewer/node_modules/pdfjs-dist/build/pdf.worker.min.js';

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
                        this.mainDocument = data.document;
                        this.signaturesService.mainDocumentId = this.mainDocument.id;
                        this.initDoc();
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
                this.loadingDoc = false;
            }
        });
    }

    initDoc() {
        this.docList = [];
        this.signaturesService.signaturesContent = [];
        this.signaturesService.notesContent = [];

        let notesContent = localStorage.getItem(this.mainDocument.id.toString());
        if (notesContent) {
            let storageContent = JSON.parse(notesContent);
            this.signaturesService.notesContent = storageContent['note'];
            this.signaturesService.signaturesContent = storageContent['sign'];
        }

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
        this.pdfViewer.openDocument(this.pdfDataArr);
    }

    pdfRendered() {
        console.log('pdf rendered');
        this.pdfViewer.setZoom(this.signaturesService.scale);
        this.signaturesService.workingAreaHeight = $('.page').height();
        this.signaturesService.workingAreaWidth = $('.page').width();

        this.totalPages = this.pdfViewer.getNumberOfPages();
        this.signaturesService.totalPage = this.totalPages;
        this.disableState = false;
    }

    pdfError(e: any) {
        console.log(e);
    }

    zoomPlus() {
        this.pdfViewer.setZoom(2);
        this.signaturesService.scale = this.pdfViewer.getZoom();
    }

    zoomMinus() {
        this.pdfViewer.setZoom(1);
        this.signaturesService.scale = this.pdfViewer.getZoom();
    }

    prevPage() {
        this.disableState = true;
        this.pageNum--;
        this.pdfViewer.prevPage();
        if (this.pageNum === 0) {
            this.pageNum = 1;
        } else {
        }

        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }

        // fix issue render pdf load is quick click
        setTimeout(() => {
            this.disableState = false;
        }, 500);
    }

    nextPage() {
        this.disableState = true;
        if (this.pageNum >= this.totalPages) {
            this.pageNum = this.totalPages;
        } else {
            this.pageNum++;
        }
        this.pdfViewer.nextPage();
        // only for main document
        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }

        // fix issue render pdf load is quick click
        setTimeout(() => {
            this.disableState = false;
        }, 500);
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

    addAnnotation(e: any) {
        console.log(e.srcEvent.layerY);
        this.signaturesService.x = -e.srcEvent.layerX;
        this.signaturesService.y = -e.srcEvent.layerY;
        if (!this.signaturesService.annotationMode && this.currentDoc === 0) {
            this.signaturesService.annotationMode = true;
            this.zoomPlus();
            this.appDocumentNotePad.initPad();
        }
    }

    refuseDocument(): void {
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
                localStorage.removeItem(this.mainDocument.id.toString());
            } else if (result === 'annotation') {
                this.signaturesService.annotationMode = true;
            }
        });
    }

    validateDocument(mode: any): void {
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
                localStorage.removeItem(this.mainDocument.id.toString());
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
                localStorage.removeItem(this.mainDocument.id.toString());
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

    undoTag() {
        if (this.signaturesService.notesContent[this.pageNum]) {
            this.signaturesService.notesContent[this.pageNum].pop();
            localStorage.setItem(this.mainDocument.id.toString(), JSON.stringify({"sign" : this.signaturesService.signaturesContent, "note" : this.signaturesService.notesContent}));
        }
    }

    checkEmptyNote() {
        if (!this.signaturesService.notesContent[this.pageNum]) {
            return true;
        } else if (this.signaturesService.notesContent[this.pageNum] === 'undefined') {
            return true;
        } else if (this.signaturesService.notesContent[this.pageNum].length === 0) {
            return true;
        } else {
            return false;
        }
    }

    checkEmptiness() {
        let state = true;
        for (let pageNum = 1; pageNum <= this.signaturesService.totalPage; pageNum++) {
            if (this.signaturesService.notesContent[pageNum]) {
                if (this.signaturesService.notesContent[pageNum].length > 0) {
                    state = false;
                    break;
                }
            }
            if (this.signaturesService.signaturesContent[pageNum]) {
                if (this.signaturesService.signaturesContent[pageNum].length > 0) {
                    state = false;
                    break;
                }
            }
        }
        return state;
    }

    onPanStart(event: any): void {
        this.startX = this.signaturesService.x;
        this.startY = this.signaturesService.y;
    }

    onPan(event: any): void {
        event.preventDefault();
        if (!this.signaturesService.annotationMode && !this.signaturesService.documentFreeze) {
            this.signaturesService.x = this.startX + event.deltaX;
            this.signaturesService.y = this.startY + event.deltaY;
        }
    }
}
