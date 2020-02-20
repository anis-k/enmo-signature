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
import { TranslateService } from '@ngx-translate/core';
import { CdkDragEnd, DragRef, CdkDrag } from '@angular/cdk/drag-drop';
import { DocumentListComponent } from './document-list/document-list.component';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';


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

    enterApp: boolean = true;
    pageNum: number = 1;
    signaturesContent: any = [];
    totalPages: number;
    draggable: boolean;
    currentDoc: number = 0;
    docList: any = [];
    actionsList: any = [
        {
            id: 2,
            label: 'lang.reject',
            color: '#e74c3c',
            logo: 'fas fa-backspace',
            event: 'refuseDocument'
        },
        {
            id: 3,
            label: 'lang.signatures',
            color: '#135F7F',
            logo: '',
            event: 'openDrawer'
        },
        {
            id: 1,
            label: 'lang.validate',
            color: '#2ecc71',
            logo: 'fas fa-check-circle',
            event: 'validateDocument'
        },
    ];
    pdfDataArr: any;
    freezeSidenavClose: boolean = false;
    startX: number = 0;
    startY: number = 0;
    widthDoc: string = '100%';
    resetDragPos: boolean = false;

    mainDocument: any = {
        id: 0,
        attachments: [],
        workflow: [],
    };

    loadingUI: any = false;

    img: any;

    expandedNote: boolean = true;

    @ViewChild('snav') snav: MatSidenav;
    @ViewChild('snavRight') snavRight: MatSidenav;
    @ViewChild('dragElem') dragElem: any;
    @ViewChild('appDocumentNotePad') appDocumentNotePad: DocumentNotePadComponent;
    @ViewChild('appDocumentList') appDocumentList: DocumentListComponent;


    @HostListener('window:resize', ['$event'])
    onResize(event: any) {
        this.signaturesService.annotationMode = false;
        this.widthDoc = '100%';
        this.resetDragPos = true;
        setTimeout(() => {
            this.resetDragPos = false;
        }, 200);
        setTimeout(() => {
            this.signaturesService.workingAreaHeight = $('#snapshotPdf').height();
            this.signaturesService.workingAreaWidth = $('#snapshotPdf').width();
            this.signaturesService.scale = 1;
        }, 400);
    }

    constructor(private translate: TranslateService,
        private router: Router,
        private route: ActivatedRoute,
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        private cookieService: CookieService,
        private sanitizer: DomSanitizer,
        public dialog: MatDialog,
        private bottomSheet: MatBottomSheet,
        public authService: AuthService,
        private localStorage: LocalStorageService) {
        this.draggable = false;
    }

    ngOnInit(): void {
        setTimeout(() => {
            this.enterApp = false;
        }, 500);
        this.route.params.subscribe(params => {
            if (typeof params['id'] !== 'undefined') {
                this.signaturesService.mainLoading = true;
                this.signaturesService.renderingDoc = true;
                this.http.get('../rest/documents/' + params['id'])
                    .subscribe((data: any) => {
                        this.mainDocument = data.document;
                        this.totalPages = this.mainDocument.pages;

                        this.signaturesService.mainDocumentId = this.mainDocument.id;
                        this.signaturesService.totalPage = this.mainDocument.pages;

                        this.initDoc();

                        const realUserWorkflow = this.mainDocument.workflow.filter((line: { current: boolean; }) => line.current === true)[0];

                        if (realUserWorkflow.userId !== this.authService.user.id) {
                            this.http.get('../rest/users/' + realUserWorkflow.userId + '/signatures')
                                .subscribe((dataSign: any) => {
                                    this.signaturesService.signaturesListSubstituted = dataSign.signatures;
                                    this.signaturesService.loadingSign = false;
                                });
                        } else {
                            this.signaturesService.signaturesListSubstituted = [];
                        }

                        this.docList.push({ 'id': this.mainDocument.id, 'title': this.mainDocument.title, 'pages': this.mainDocument.pages, 'imgContent': [], 'imgUrl': '../rest/documents/' + this.mainDocument.id + '/thumbnails' });
                        this.mainDocument.attachments.forEach((attach: any) => {
                            this.docList.push({ 'id': attach.id, 'title': attach.title, 'pages': attach.pages, 'imgContent': [], 'imgUrl': '../rest/attachments/' + attach.id + '/thumbnails' });
                        });
                        this.renderImage();

                    });
            } else {
                this.docList = [];
                this.signaturesService.signaturesContent = [];
                this.signaturesService.notesContent = [];
                this.snav.open();
                this.signaturesService.mainDocumentId = null;
                this.freezeSidenavClose = true;
                this.signaturesService.mainLoading = false;
            }
        });
    }

    renderImage() {
        if (this.docList[this.currentDoc].imgContent[this.pageNum] === undefined) {
            this.signaturesService.mainLoading = true;
            this.loadingUI = true;
            if (this.currentDoc === 0) {
                this.http.get('../rest/documents/' + this.docList[this.currentDoc].id + '/thumbnails/' + this.pageNum)
                    .subscribe((data: any) => {
                        this.docList[this.currentDoc].imgContent[this.pageNum] = data.fileContent;
                        this.signaturesService.mainLoading = false;
                        setTimeout(() => {
                            this.initWorkingArea();
                            this.loadingUI = false;
                        }, 400);
                    });
            } else {
                this.http.get('../rest/attachments/' + this.docList[this.currentDoc].id + '/thumbnails/' + this.pageNum)
                    .subscribe((data: any) => {
                        this.docList[this.currentDoc].imgContent[this.pageNum] = data.fileContent;
                        this.signaturesService.mainLoading = false;
                        setTimeout(() => {
                            this.loadingUI = false;
                        }, 400);
                    });
            }
        }
    }

    initDoc() {
        this.docList = [];
        this.signaturesService.signaturesContent = [];
        this.signaturesService.notesContent = [];

        const notesContent = this.localStorage.get(this.mainDocument.id.toString());

        if (notesContent) {
            const storageContent = JSON.parse(notesContent);
            this.signaturesService.notesContent = storageContent['note'];
            this.signaturesService.signaturesContent = storageContent['sign'];
        }

        this.signaturesService.currentAction = 0;
        this.signaturesService.currentPage = 1;
        this.pageNum = 1;
        this.signaturesContent.currentDoc = 1;
        this.currentDoc = 0;
    }

    testDrag(event: any) {
        const element = event.source.getRootElement();
        const boundingClientRect = element.getBoundingClientRect();
        const parentPosition = this.getPosition(element);

        this.signaturesService.y = (boundingClientRect.y - parentPosition.top);
        this.signaturesService.x = (boundingClientRect.x - parentPosition.left);
    }

    getPosition(el: any) {
        let x = 0;
        let y = 0;
        while (el && !isNaN(el.offsetLeft) && !isNaN(el.offsetTop)) {
            x += el.offsetLeft - el.scrollLeft;
            y += el.offsetTop - el.scrollTop;
            el = el.offsetParent;
        }
        return { top: y, left: x };
    }

    zoomForNotes() {
        this.widthDoc = '200%';
        this.signaturesService.scale = 2;
        $('.example-box').css({ 'transform': 'translate3d(' + this.signaturesService.x * this.signaturesService.scale + 'px, ' + this.signaturesService.y * this.signaturesService.scale + 'px, 0px)' });

        this.signaturesService.workingAreaHeight *= this.signaturesService.scale;
        this.signaturesService.workingAreaWidth *= this.signaturesService.scale;

    }

    zoomForView() {
        this.signaturesService.mainLoading = true;
        // this.resetDragPosition();
        this.resetDragPos = true;
        this.widthDoc = '100%';
        this.signaturesService.workingAreaHeight = this.signaturesService.workingAreaHeight / 2;
        this.signaturesService.workingAreaWidth = this.signaturesService.workingAreaWidth / 2;
        setTimeout(() => {
            this.resetDragPos = false;
        }, 200);
        setTimeout(() => {
            this.signaturesService.mainLoading = false;
        }, 400);
        this.signaturesService.scale = 1;

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
        this.renderImage();
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
        this.renderImage();
    }

    initWorkingArea() {
        if ((typeof this.signaturesService.workingAreaHeight !== 'number' || this.signaturesService.workingAreaHeight === 0) && (typeof this.signaturesService.workingAreaWidth !== 'number' || this.signaturesService.workingAreaWidth === 0)) {
            this.img = document.querySelector('img.zoom');
            const rect = this.img.getBoundingClientRect();
            this.signaturesService.workingAreaHeight = rect.height;
            this.signaturesService.workingAreaWidth = rect.width;
        }
    }

    addAnnotation(e: any) {
        e.preventDefault();

        e = e.srcEvent;

        if (!this.signaturesService.annotationMode && this.currentDoc === 0) {

            this.backToDetails();

            this.img = document.querySelector('img.zoom');

            const rect = this.img.getBoundingClientRect();
            const offsetX = e.pageX - rect.left - window.pageXOffset;
            const offsetY = e.pageY - rect.top - window.pageYOffset;

            const posX = offsetX - this.signaturesService.x;
            const posY = offsetY - this.signaturesService.y;

            if (this.signaturesService.mobileMode) {
                this.signaturesService.x = -posX;
            } else {
                this.signaturesService.x = -posX + 350;
            }

            this.signaturesService.y = -posY;
            this.zoomForNotes();
            $('.example-box').css({ 'transform': 'translate3d(' + -(posX) + 'px, ' + -(posY) + 'px, 0px)' });

            this.signaturesService.annotationMode = true;
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
                this.localStorage.remove(this.mainDocument.id.toString());
            } else if (result === 'annotation') {
                this.signaturesService.annotationMode = true;
                this.appDocumentNotePad.initPad();
            }
        });
    }

    validateDocument(mode: any): void {
        const dialogRef = this.dialog.open(ConfirmModalComponent, {
            width: '350px',
            data: { msg: 'lang.areYouSure' }
        });

        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                const config: MatBottomSheetConfig = {
                    disableClose: true,
                    direction: 'ltr'
                };
                this.bottomSheet.open(SuccessInfoValidBottomSheetComponent, config);
                this.localStorage.remove(this.mainDocument.id.toString());
            }
        });
    }

    openDrawer(): void {
        if (this.currentDoc > 0) {
            this.currentDoc = 0;
            this.pageNum = 1;
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
            data: { msg: 'lang.deleteNoteAndSignature' }
        });

        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                this.signaturesService.signaturesContent = [];
                this.signaturesService.notesContent = [];
                this.localStorage.remove(this.mainDocument.id.toString());
                this.notificationService.success('lang.noteAndSignatureDeleted');
            }
        });
    }

    loadDoc(index: any) {
        this.signaturesService.renderingDoc = true;
        if (index > 0) {
            this.signaturesService.isTaggable = false;
        }
        this.pageNum = 1;
        this.currentDoc = index;
        this.totalPages = this.docList[index].pages;
    }

    launchEvent(action: any) {
        this.backToDetails();
        this.signaturesService.currentAction = action.id;
        this[action.event]();
    }

    undoTag() {
        if (this.signaturesService.notesContent[this.pageNum]) {
            this.signaturesService.notesContent[this.pageNum].pop();
            this.localStorage.remove(this.mainDocument.id.toString());
            this.localStorage.save(this.mainDocument.id.toString(), JSON.stringify({ 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
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

    openVisaWorkflow() {
        this.snavRight.open();
        this.signaturesService.sideNavRigtDatas = {
            mode: 'visaWorkflow',
            width: '450px',
            locked: false,
        };
    }

    openDocumentList() {
        this.snavRight.open();
        this.signaturesService.sideNavRigtDatas = {
            mode: 'documentList',
            width: '450px',
            locked: false,
        };
    }

    openMainDocumentDetail() {
        this.snavRight.open();
        this.signaturesService.sideNavRigtDatas = {
            mode: 'mainDocumentDetail',
            width: '450px',
            locked: false,
        };
    }

    backToDetails() {
        this.signaturesService.sideNavRigtDatas = {
            mode: 'mainDocumentDetail',
            width: '450px',
            locked: false,
        };
    }

    deleteSubstution() {
        const r = confirm(this.translate.instant('lang.deleteSubstitution') + ' ?');

        if (r) {
            this.http.put('../rest/users/' + this.authService.user.id + '/substitute', { substitute: null })
                .subscribe(() => {
                    this.authService.updateUserInfoWithTokenRefresh();
                    this.notificationService.success('lang.substitutionDeleted');
                });
        }
    }

    gotTo(documentId: number, i: any) {
        this.router.navigate(['/documents/' + documentId]);
        this.signaturesService.mainDocumentId = documentId;
        // this.signaturesService.indexDocumentsList = i;
        this.signaturesService.sideNavRigtDatas = {
            mode: 'mainDocumentDetail',
            width: '450px',
            locked: false,
        };
    }
}
