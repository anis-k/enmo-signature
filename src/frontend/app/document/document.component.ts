import { Component, OnInit, ViewChild, TemplateRef, ViewContainerRef, Injectable } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { DomSanitizer } from '@angular/platform-browser';
import { MatBottomSheet, MatBottomSheetConfig } from '@angular/material/bottom-sheet';
import { MatDialog } from '@angular/material/dialog';
import { MatSidenav } from '@angular/material/sidenav';
import { SignaturesComponent } from '../signatures/signatures.component';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../service/notification.service';
import { CookieService } from 'ngx-cookie-service';
import { DocumentNotePadComponent } from '../documentNotePad/document-note-pad.component';
import { RejectInfoBottomSheetComponent } from '../modal/reject-info.component';
import { TranslateService } from '@ngx-translate/core';
import { DocumentListComponent } from './document-list/document-list.component';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';
import { ActionSheetController, AlertController, LoadingController, MenuController, ModalController, NavController } from '@ionic/angular';
import { NgxExtendedPdfViewerService } from 'ngx-extended-pdf-viewer';
import { catchError, exhaustMap, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { SignatureMethodService } from '../service/signature-method/signature-method.service';
import { FunctionsService } from '../service/functions.service';
import { ActionsService } from '../service/actions.service';
import { SuccessInfoValidBottomSheetComponent } from '../modal/success-info-valid.component';

@Component({
    selector: 'app-document',
    templateUrl: 'document.component.html',
    styleUrls: ['document.component.scss'],
})


export class DocumentComponent implements OnInit {

    posX: number = 0;
    posY: number = 0;
    enterApp: boolean = true;
    detailMode: boolean = false;
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
            color: 'danger',
            logo: 'thumbs-down-outline',
            event: 'refuseDocument'
        },
        {
            id: 3,
            label: 'lang.signatures',
            color: '',
            logo: '',
            event: 'openSignatures'
        },
        {
            id: 1,
            label: 'lang.validate',
            color: 'success',
            logo: 'thumbs-up-outline',
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
        status,
        attachments: [],
        workflow: [],
    };

    loadingUI: any = false;

    expandedNote: boolean = true;
    hasWorkflowNotes: boolean = false;
    currentTool = 'info';
    load: HTMLIonLoadingElement = null;
    dragging: boolean = false;
    resizing: boolean = false;
    pdfname: string = null;
    loadingdocument: boolean = true;
    loadingpdf: boolean = false;
    loadingImage: boolean = true;
    @ViewChild('mainContent') mainContent: any;
    @ViewChild('img') img: any;
    @ViewChild('snav', { static: true }) snav: MatSidenav;
    @ViewChild('dragElem') dragElem: any;
    @ViewChild('appDocumentNotePad') appDocumentNotePad: DocumentNotePadComponent;
    @ViewChild('appDocumentList') appDocumentList: DocumentListComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;
    @ViewChild('pagesList') pagesList: any;

    constructor(private translate: TranslateService,
        private router: Router,
        private route: ActivatedRoute,
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        private cookieService: CookieService,
        public sanitizer: DomSanitizer,
        public dialog: MatDialog,
        private bottomSheet: MatBottomSheet,
        public authService: AuthService,
        private localStorage: LocalStorageService,
        private menu: MenuController,
        public actionSheetController: ActionSheetController,
        public loadingController: LoadingController,
        public viewContainerRef: ViewContainerRef,
        public modalController: ModalController,
        private pdfViewerService: NgxExtendedPdfViewerService,
        public alertController: AlertController,
        public signatureMethodService: SignatureMethodService,
        public navCtrl: NavController,
        public functionsService: FunctionsService,
        public actionsService: ActionsService,
    ) {
        this.draggable = false;
    }

    imageLoaded(ev: any) {
        this.getImageDimensions(true);
        // this.loadingDocument = false;
        this.load.dismiss();
        this.menu.enable(true, 'right-menu');
        this.loadingImage = false;
        document.getElementsByClassName('drag-scroll-content')[0].scrollTop = 0;
    }

    getImageDimensions(ajustSize: boolean = false): void {
        const img = new Image();
        img.onload = (data: any) => {

            const percent = (data.target.naturalWidth * 100) / this.signaturesService.workingAreaWidth;

            this.signaturesService.workingAreaWidth = data.target.naturalWidth;
            this.signaturesService.workingAreaHeight = data.target.naturalHeight;

            if (ajustSize) {
                this.getAreaDimension();
            }

            /*if (percent !== Infinity) {
                this.signatures.forEach(element => {
                    element.position.height = (percent * element.position.height) / 100;
                    element.position.width = (percent * element.position.width) / 100;
                    element.position.top = (percent * element.position.top) / 100;
                    element.position.left = (percent * element.position.left) / 100;
                });
            }*/
            // this.originalSize = true;
        };
        img.src = this.docList[this.currentDoc].imgContent[this.pageNum];
    }

    getAreaDimension() {
        const percent = (this.mainContent.el.offsetWidth * 100) / this.signaturesService.workingAreaWidth;

        this.signaturesService.workingAreaWidth = (percent * this.signaturesService.workingAreaWidth) / 100;
        this.signaturesService.workingAreaHeight = (percent * this.signaturesService.workingAreaHeight) / 100;

        /*this.signatures.forEach(element => {
          element.position.height = (percent * element.position.height) / 100;
          element.position.width = (percent * element.position.width) / 100;
          element.position.top = (percent * element.position.top) / 100;
          element.position.left = (percent * element.position.left) / 100;
        });*/
        // this.originalSize = false;
    }

    async openAction(event: any) {
        this.posX = event.clientX;
        this.posY = event.clientY; 
        let buttons = [];
        if (!this.checkEmptyNote()) {
            buttons.push({
                text: this.translate.instant('lang.cancelPreviousNote'),
                icon: 'arrow-undo-outline',
                handler: () => {
                    this.undoTag();
                }
            });
        }

        if (!this.signaturesService.stampLock) {
            buttons.push({
                text: this.translate.instant('lang.affixSignature'),
                icon: 'ribbon-outline',
                handler: () => {
                    this.openSignatures();
                }
            });
        }
        buttons.push({
            text: this.translate.instant('lang.annotateDocument'),
            icon: 'receipt-outline',
            handler: () => {
                this.openNoteEditor();
            }
        });
        /* if (this.originalSize) {
          buttons.push({
            text: 'Zoom taille écran',
            icon: 'contract-outline',
            handler: () => {
              this.getAreaDimension();
              console.log('Share clicked');
            }
          });
        } else {
          buttons.push({
            text: 'Zoom taille écran',
            icon: 'contract-outline',
            handler: () => {
              this.getImageDimensions();
              console.log('Share clicked');
            }
          });
        } */
        if (!this.checkEmptiness()) {
            buttons.push({
                text: this.translate.instant('lang.deleteAll'),
                icon: 'color-wand-outline',
                handler: () => {
                    this.removeTags();
                }
            });
        }

        const actionSheet = await this.actionSheetController.create({
            header: 'Actions',
            cssClass: 'my-custom-class',
            buttons: buttons,
        });
        await actionSheet.present();
    }

    async openSignatures() {
        const modal = await this.modalController.create({
            component: SignaturesComponent,
            cssClass: 'my-custom-class',
            componentProps: {
                currentWorflow: this.mainDocument.workflow.filter((line: { current: boolean; }) => line.current === true)[0],
            }
        });
        await modal.present();
        const { data } = await modal.onWillDismiss();
        this.dragging = false;
        if (data !== undefined) {
            if (data === 'success') {
                // this.scrollToElem();
            } else if (data.redirectPage !== undefined) {
                this.goTo(data.redirectPage);
            }
        }
        // console.log('dissmiss');
    }

    async openNoteEditor(ev: any = null) {
        let scrollPercentX = 0;
        let scrollPercentY = 0;
        if (ev !== null) {
            const offsetTop = -($('#myBounds')[0].getBoundingClientRect().top - 70);
            const realPosY = (ev.pageY - 75) + offsetTop;

            // console.log(offsetTop);

            scrollPercentX = ((ev.pageX - 350) / ($('#myBounds').width() - $(window).width())) * 100;
            scrollPercentX = scrollPercentX;
            scrollPercentY = (offsetTop / ($('#myBounds').height() - $(window).height())) * 100;
            // scrollPercentY = (realPosY / ($('#myBounds').height() - $(window).height())) * 100;

            // console.log('scrollPercentX', scrollPercentX);
            // console.log('scrollPercentY', scrollPercentY);
        }
        const modal = await this.modalController.create({
            component: DocumentNotePadComponent,
            cssClass: 'fullscreen',
            componentProps: {
                precentScrollTop:  this.posY,
                precentScrollLeft: this.posX,
                content: this.docList[this.currentDoc].imgContent[this.pageNum]
            }
        });
        await modal.present();
        const { data } = await modal.onWillDismiss();
        if (data === 'success') {

        }
        // console.log('dissmiss');
    }

    scrollToElem() {
        const pageY = this.signaturesService.signaturesContent[this.pageNum][this.signaturesService.signaturesContent[this.pageNum].length - 1].positionY;
        const offsetTop = -($('#myBounds')[0].getBoundingClientRect().top - 70);
        const realPosY = (pageY - 75) + offsetTop;

        const scrollY = (realPosY - $(window).height());

        document.getElementsByClassName('drag-scroll-content')[0].scrollTo(1000, -scrollY);
    }

    ionViewWillEnter() {
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
    }

    ngOnInit(): void {
        // console.log('oninit!');
        this.menu.enable(false, 'right-menu');
        this.menu.enable(true, 'left-menu');

        this.route.params.subscribe(params => {
            if (typeof params['id'] !== 'undefined') {
                this.loadingController.create({
                    message: this.translate.instant('lang.loadingDocument'),
                    spinner: 'dots'
                }).then((load: HTMLIonLoadingElement) => {
                    this.load = load;
                    this.load.present();
                    this.http.get('../rest/documents/' + params['id']).pipe(
                        tap((data: any) => {
                            this.mainDocument = data.document;
                            this.mainDocument.workflow = this.mainDocument.workflow.map((item: any) => {
                                if (item.note) {
                                    this.hasWorkflowNotes = true;
                                }
                                item.userSignatureModes.unshift('visa');
                                return {
                                    ...item,
                                    'role': item.mode === 'visa' ? 'visa' : item.signatureMode,
                                    'modes': item.userSignatureModes
                                };
                            });

                            this.totalPages = this.mainDocument.pages;

                            this.signaturesService.mainDocumentId = this.mainDocument.id;
                            this.signaturesService.totalPage = this.mainDocument.pages;
                            this.menu.enable(true, 'right-menu');
                            this.initDoc();

                            const realUserWorkflow = this.mainDocument.workflow.filter((line: { current: boolean; }) => line.current === true);

                            this.mainDocument.isCertified = this.mainDocument.workflow.filter((line: any) => line.mode === 'sign' && line.signatureMode !== 'stamp' && line.processDate !== null).length > 0;

                            if (realUserWorkflow.length === 0 || this.mainDocument.readOnly) {
                                this.actionsList = [
                                    {
                                        id: 4,
                                        label: 'lang.back',
                                        color: 'medium',
                                        logo: 'chevron-back-outline',
                                        event: 'back'
                                    },
                                ];
                                this.detailMode = true;
                            } else {
                                this.signaturesService.stampLock = this.mainDocument.isCertified && ((realUserWorkflow[0].signatureMode === 'stamp' && realUserWorkflow[0].mode === 'sign') || (realUserWorkflow[0].mode === 'visa'));
                                if (realUserWorkflow[0].userId !== this.authService.user.id) {
                                    this.http.get('../rest/users/' + realUserWorkflow[0].userId + '/signatures')
                                        .subscribe((dataSign: any) => {
                                            this.signaturesService.signaturesListSubstituted = dataSign.signatures;
                                        });
                                } else {
                                    this.signaturesService.signaturesListSubstituted = [];
                                }
                            }

                            this.docList.push({ 'id': this.mainDocument.id, 'title': this.mainDocument.title, 'pages': this.mainDocument.pages, 'imgContent': [], 'imgUrl': '../rest/documents/' + this.mainDocument.id + '/thumbnails' });
                            this.mainDocument.attachments.forEach((attach: any) => {
                                this.docList.push({ 'id': attach.id, 'title': attach.title, 'pages': attach.pages, 'imgContent': [], 'imgUrl': '../rest/attachments/' + attach.id + '/thumbnails' });
                            });
                            this.menu.enable(true, 'right-menu');
                            // this.renderPdf();
                            this.renderImage();
                            this.loadingdocument = false;
                        }),
                        catchError((err: any) => {
                            console.log('error', err);
                            setTimeout(() => {
                                this.load.dismiss();
                            }, 200);
                            this.notificationService.handleErrors(err);
                            this.router.navigate(['/home']);
                            return of(false);
                        })
                    ).subscribe();
                });
            }
        });
    }

    renderPdf() {
        // console.log('renderPdf');

        this.http.get('../rest/documents/' + this.docList[this.currentDoc].id + '/content')
            .subscribe((data: any) => {
                this.pdfname = 'data:application/pdf;base64,' + data.encodedDocument;
                this.loadingpdf = true;
            });
    }

    async onPagesLoaded(ev: any) {
        this.totalPages = ev.pagesCount;
        this.exportAsImage();
    }

    public async exportAsImage(): Promise<void> {
        const scale = { width: 1000 };
        // or: scale = {height: this.height};
        // or: scale = {scale: this.scale};
        const data = await this.pdfViewerService.getPageAsImage(this.pageNum, scale);
        this.docList[this.currentDoc].imgContent[this.pageNum] = data;
        this.loadingpdf = false;
        this.load.dismiss();
    }

    renderImage() {
        if (this.docList[this.currentDoc].imgContent[this.pageNum] === undefined) {
            if (this.currentDoc === 0) {
                this.http.get('../rest/documents/' + this.docList[this.currentDoc].id + '/thumbnails/' + this.pageNum).pipe(
                    tap((data: any) => {
                        this.docList[this.currentDoc].imgContent[this.pageNum] = 'data:image/png;base64,' + data.fileContent;
                    }),
                    catchError((err: any) => {
                        this.load.dismiss();
                        this.notificationService.handleErrors(err);
                        this.router.navigate(['/home']);
                        return of(false);
                    })
                ).subscribe();
            } else {
                this.http.get('../rest/attachments/' + this.docList[this.currentDoc].id + '/thumbnails/' + this.pageNum).pipe(
                    tap((data: any) => {
                        this.docList[this.currentDoc].imgContent[this.pageNum] = 'data:image/png;base64,' + data.fileContent;
                    }),
                    catchError((err: any) => {
                        this.load.dismiss();
                        this.notificationService.handleErrors(err);
                        this.router.navigate(['/home']);
                        return of(false);
                    })
                ).subscribe();
            }
        }
    }

    initDoc() {
        this.docList = [];
        this.signaturesService.signaturesContent = [];
        this.signaturesService.notesContent = [];
        this.signaturesService.datesContent = [];
        this.signaturesService.currentToobal = 'mainDocumentDetail';

        const notesContent = this.localStorage.get(this.mainDocument.id.toString());

        if (notesContent) {
            const storageContent = JSON.parse(notesContent);
            this.signaturesService.notesContent = storageContent['note']  !== undefined ? storageContent['note'] : [];
            this.signaturesService.signaturesContent = storageContent['sign']  !== undefined ? storageContent['sign'] : [];
            this.signaturesService.datesContent = storageContent['date'] !== undefined ? storageContent['date'] : [];
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
        // this.resetDragPosition();
        this.resetDragPos = true;
        this.widthDoc = '100%';
        this.signaturesService.workingAreaHeight = this.signaturesService.workingAreaHeight / 2;
        this.signaturesService.workingAreaWidth = this.signaturesService.workingAreaWidth / 2;
        setTimeout(() => {
            this.resetDragPos = false;
        }, 200);
        this.signaturesService.scale = 1;

    }

    prevPage() {
        this.loadingImage = true;
        this.pageNum--;

        if (this.pageNum === 0) {
            this.pageNum = 1;
        } else {
        }

        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }
        // this.exportAsImage();
        this.renderImage();
    }

    nextPage() {
        this.loadingImage = true;
        if (this.pageNum >= this.totalPages) {
            this.pageNum = this.totalPages;
        } else {
            this.pageNum++;
        }

        // only for main document
        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }
        // this.exportAsImage();
        this.renderImage();
    }

    goTo(page: number) {        
        this.loadingController.create({
            message: this.translate.instant('lang.loadingDocument'),
            spinner: 'dots',
        }).then((load: HTMLIonLoadingElement) => {
            this.load = load;
            this.load.present();
        });
        this.loadingImage = true;
        this.pageNum = page;
        // only for main document
        if (this.currentDoc === 0) {
            this.signaturesService.currentPage = this.pageNum;
        }
        // this.exportAsImage();
        this.renderImage();
    }

    pagesArray(n: number): number[] {
        return Array(n);
    }

    initWorkingArea() {
        /*if ((typeof this.signaturesService.workingAreaHeight !== 'number' || this.signaturesService.workingAreaHeight === 0) && (typeof this.signaturesService.workingAreaWidth !== 'number' || this.signaturesService.workingAreaWidth === 0)) {
            this.img = document.querySelector('img.zoom');
            const rect = this.img.getBoundingClientRect();
            this.signaturesService.workingAreaHeight = rect.height;
            this.signaturesService.workingAreaWidth = rect.width;
        }*/
    }

    async refuseDocument(): Promise<void> {
        let msg = this.translate.instant('lang.rejectDocumentWarning');

        if (this.signaturesService.notesContent.length === 0) {
            msg = this.translate.instant('lang.refuseDocumentWithoutNote');
        }

        const alert = await this.alertController.create({
            cssClass: 'custom-alert-danger',
            header: this.translate.instant('lang.reject'),
            message: msg,
            inputs: [
                {
                    name: 'paragraph',
                    id: 'paragraph',
                    type: 'textarea',
                    placeholder: this.translate.instant('lang.addReason')
                },
            ],
            buttons: [
                {
                    text: this.translate.instant('lang.reject'),
                    handler: async (data: any) => {
                        const res = await this.actionsService.sendDocument(data.paragraph);
                        if (!this.functionsService.empty(res)) {
                            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList] !== undefined) {
                                this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                                if (this.signaturesService.documentsListCount.current > 0) {
                                    this.signaturesService.documentsListCount.current--;
                                }
                            }
                            const config: MatBottomSheetConfig = {
                                disableClose: true,
                                direction: 'ltr'
                            };
                            this.bottomSheet.open(RejectInfoBottomSheetComponent, config);
                            this.localStorage.remove(this.mainDocument.id.toString());
                        }
                    }
                }
            ]
        });
        await alert.present();
    }

    async validateDocument(mode: any): Promise<void> {
        let msg = this.translate.instant('lang.validateDocumentWarning');

        if (this.signaturesService.signaturesContent.length === 0 && this.signaturesService.notesContent.length === 0) {
            msg = this.translate.instant('lang.validateDocumentWithoutSignOrNote');
        }
        if (this.signaturesService.stampLock) {
            msg = this.translate.instant('lang.certifiedDocumentMsg2');
        }
        const alert = await this.alertController.create({
            cssClass: 'custom-alert-success',
            header: this.translate.instant('lang.validate'),
            message: msg,
            inputs: [
                {
                    name: 'paragraph',
                    id: 'paragraph',
                    type: 'textarea',
                    placeholder: this.translate.instant('lang.addReason')
                },
            ],
            buttons: [
                {
                    text: this.translate.instant('lang.validate'),
                    handler: async (data: any) => {
                        const currentUserWorkflow = this.mainDocument.workflow.filter((line: { current: boolean; }) => line.current === true)[0]; 
                        this.loadingController.create({
                            message: this.translate.instant('lang.loadingValidation'),
                            spinner: 'dots',
                        }).then((load: HTMLIonLoadingElement) => {
                            this.load = load;
                            this.load.present();
                            if ((currentUserWorkflow.signatureMode === 'rgs_2stars') || (currentUserWorkflow.signatureMode === 'inca_card') || (currentUserWorkflow.signatureMode === 'rgs_2stars_timestamped' ) || (currentUserWorkflow.signatureMode === 'inca_card_eidas')) {
                                this.load.dismiss();
                            }
                        });
                        const res = await this.signatureMethodService.checkAuthenticationAndLaunchAction(currentUserWorkflow, data.paragraph);
                        if (!this.functionsService.empty(res)) {
                            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList] !== undefined) {
                                this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                                if (this.signaturesService.documentsListCount.current > 0) {
                                    this.signaturesService.documentsListCount.current--;
                                }
                            }
                                const config: MatBottomSheetConfig = {
                                    disableClose: true,
                                    direction: 'ltr'
                                };
                                this.bottomSheet.open(SuccessInfoValidBottomSheetComponent, config);
                                this.localStorage.remove(this.mainDocument.id.toString());
                        }
                        this.load.dismiss();
                    }
                }
            ]
        });

        await alert.present();
    }
    async removeTags() {
        this.signaturesService.currentAction = 0;

        const alert = await this.alertController.create({
            header: this.translate.instant('lang.deleteNoteAndSignature'),
            buttons: [
                {
                    text: this.translate.instant('lang.validate'),
                    handler: () => {
                        this.signaturesService.signaturesContent = [];
                        this.signaturesService.notesContent = [];
                        this.signaturesService.datesContent = [];
                        this.localStorage.remove(this.mainDocument.id.toString());
                        this.notificationService.success('lang.noteAndSignatureDeleted');
                    }
                }
            ]
        });

        await alert.present();
    }

    loadDoc(index: any) {
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
            if (this.signaturesService.datesContent[pageNum]) {
                if (this.signaturesService.datesContent[pageNum].length > 0) {
                    state = false;
                    break;
                }
            }
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
        this.menu.open('right-menu');
        this.signaturesService.currentToobal = 'visaWorkflow';
    }

    openDocumentList() {
        this.menu.open('right-menu');
        this.signaturesService.currentToobal = 'documentList';
    }

    openAssociatedDocuments() {
        this.menu.open('right-menu');
        this.signaturesService.currentToobal = 'associatedDocuments';
    }

    openMainDocumentDetail() {
        this.menu.open('right-menu');
        this.signaturesService.currentToobal = 'mainDocumentDetail';
    }

    backToDetails() {
        this.signaturesService.currentToobal = 'mainDocumentDetail';
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

    back() {
        this.navCtrl.back();
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }

    openSelect(event: any) {
        if (this.totalPages > 1) {
            this.pagesList.interface = 'popover'; 
            this.pagesList.open(event);
        }
    }

    fromHex(hexString: any) {
        const res = new Uint8Array(hexString.length / 2);
        for (let i = 0; i < hexString.length; i = i + 2) {
            const c = hexString.slice(i, i + 2);
            res[i / 2] = parseInt(c, 16);
        }
        return res.buffer;
    }
}
