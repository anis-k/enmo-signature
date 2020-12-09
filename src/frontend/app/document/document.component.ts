import { Component, OnInit, ViewChild, ElementRef, TemplateRef, ViewContainerRef, OnDestroy } from '@angular/core';
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
import { SuccessInfoValidBottomSheetComponent } from '../modal/success-info-valid.component';
import { TranslateService } from '@ngx-translate/core';
import { DocumentListComponent } from './document-list/document-list.component';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';
import { ActionSheetController, AlertController, LoadingController, MenuController, ModalController, NavController } from '@ionic/angular';
import { NgxExtendedPdfViewerService } from 'ngx-extended-pdf-viewer';
import {catchError, exhaustMap, tap} from 'rxjs/operators';
import { of } from 'rxjs';
import { SignatureMethodService } from '../service/signature-method/signature-method.service';
import {sign} from "crypto";

@Component({
    selector: 'app-document',
    templateUrl: 'document.component.html',
    styleUrls: ['document.component.scss'],
})

export class DocumentComponent implements OnInit {

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
    @ViewChild('snavRight', { static: true }) snavRight: MatSidenav;
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
        public navCtrl: NavController
    ) {
        this.draggable = false;
    }

    imageLoaded(ev: any) {
        this.getImageDimensions(true);
        // this.loadingDocument = false;
        this.load.dismiss();
        this.menu.enable(true, 'right-menu');
        this.loadingImage = false;
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

    async openAction() {
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

        buttons.push({
            text: this.translate.instant('lang.affixSignature'),
            icon: 'ribbon-outline',
            handler: () => {
                this.openSignatures();
            }
        });

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
                precentScrollTop: scrollPercentY,
                precentScrollLeft: -scrollPercentX,
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
                    this.signaturesService.mainLoading = true;
                    this.signaturesService.renderingDoc = true;
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
                                    'datePositions': [],
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

                            if (realUserWorkflow.length === 0) {
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
                                if (realUserWorkflow[0].userId !== this.authService.user.id) {
                                    this.http.get('../rest/users/' + realUserWorkflow[0].userId + '/signatures')
                                        .subscribe((dataSign: any) => {
                                            this.signaturesService.signaturesListSubstituted = dataSign.signatures;
                                            this.signaturesService.loadingSign = false;
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
        this.signaturesService.sideNavRigtDatas.mode = 'mainDocumentDetail';

        const notesContent = this.localStorage.get(this.mainDocument.id.toString());

        if (notesContent) {
            const storageContent = JSON.parse(notesContent);
            this.signaturesService.notesContent = storageContent['note'];
            this.signaturesService.signaturesContent = storageContent['sign'];
            this.signaturesService.datesContent = storageContent['date'];
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
        this.loadingController.create({
            message: this.translate.instant('lang.loadingDocument'),
            spinner: 'dots'
        }).then((load: HTMLIonLoadingElement) => {
            this.load = load;
            this.load.present();
        });
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
        this.loadingController.create({
            message: this.translate.instant('lang.loadingDocument'),
            spinner: 'dots'
        }).then((load: HTMLIonLoadingElement) => {
            this.load = load;
            this.load.present();
        });
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

    addAnnotation(e: any) {
        /*e.preventDefault();

        e = e.srcEvent;

        if (!this.signaturesService.annotationMode && this.currentDoc === 0 && this.mainDocument.status === 'READY') {

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
        }*/
    }

    async refuseDocument(): Promise<void> {
        const alert = await this.alertController.create({
            cssClass: 'custom-alert-danger',
            header: this.translate.instant('lang.reject'),
            message: this.translate.instant('lang.rejectDocumentWarning'),
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
                    handler: (data: any) => {
                        this.loadingController.create({
                            message: this.translate.instant('lang.processing') + ' ...',
                            spinner: 'dots'
                        }).then(async (load: HTMLIonLoadingElement) => {
                            load.present();
                            const res = await this.sendDocument({ 'note': data.paragraph });
                            if (res) {
                                const config: MatBottomSheetConfig = {
                                    disableClose: true,
                                    direction: 'ltr'
                                };
                                this.bottomSheet.open(RejectInfoBottomSheetComponent, config);
                                this.localStorage.remove(this.mainDocument.id.toString());
                            }
                            load.dismiss();
                        });
                    }
                }
            ]
        });
        await alert.present();
    }

    async validateDocument(mode: any): Promise<void> {
        const alert = await this.alertController.create({
            cssClass: 'custom-alert-success',
            header: this.translate.instant('lang.validate'),
            message: this.signaturesService.signaturesContent.length === 0 && this.signaturesService.notesContent.length === 0 ? this.translate.instant('lang.validateDocumentWithoutSignOrNote') : this.translate.instant('lang.areYouSure'),
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
                        const certificate = await this.signatureMethodService.checkAuthentication(currentUserWorkflow);
                        console.log('result auth', certificate);
                        if (certificate !== false) {
                            this.loadingController.create({
                                message: this.translate.instant('lang.processing') + ' ...',
                                spinner: 'dots'
                            }).then(async (load: HTMLIonLoadingElement) => {
                                load.present();
                                const res = await this.sendDocument({ 'note': data.paragraph, 'certificate': certificate });
                                if (res) {
                                    const config: MatBottomSheetConfig = {
                                        disableClose: true,
                                        direction: 'ltr'
                                    };
                                    this.bottomSheet.open(SuccessInfoValidBottomSheetComponent, config);
                                    this.localStorage.remove(this.mainDocument.id.toString());
                                }
                                load.dismiss();
                            });
                        }

                    }
                }
            ]
        });

        await alert.present();
    }

    sendDocument(data: any) {
        return new Promise((resolve) => {
            const signatures: any[] = [];
            if (this.signaturesService.currentAction > 0) {
                for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                    if (this.signaturesService.datesContent[index]) {
                        this.signaturesService.datesContent[index].forEach((date: any) => {
                            signatures.push(
                                {
                                    'encodedImage': date.content.replace('data:image/svg+xml;base64,', ''),
                                    'width': date.width,
                                    'positionX': date.positionX,
                                    'positionY': date.positionY,
                                    'type': 'SVG',
                                    'page': index,
                                }
                            );
                        });
                    }
                    if (this.signaturesService.signaturesContent[index]) {
                        this.signaturesService.signaturesContent[index].forEach((signature: any) => {
                            signatures.push(
                                {
                                    'encodedImage': signature.encodedSignature,
                                    'width': signature.width,
                                    'positionX': signature.positionX,
                                    'positionY': signature.positionY,
                                    'type': 'PNG',
                                    'page': index,
                                }
                            );
                        });
                    }
                    if (this.signaturesService.notesContent[index]) {
                        this.signaturesService.notesContent[index].forEach((note: any) => {
                            signatures.push(
                                {
                                    'encodedImage': note.fullPath.replace('data:image/png;base64,', ''),
                                    'width': note.width,
                                    'positionX': note.positionX,
                                    'positionY': note.positionY,
                                    'type': 'PNG',
                                    'page': index,
                                }
                            );
                        });
                    }
                }
                data.signatures = signatures;
                const privateKey = data.privatekey;
                data.privateKey = undefined;
                const signDocumentWith2Steps = data.certificate !== undefined && data.certificate !== false && data.certificate !== true;
                this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, data)
                    .pipe(
                        tap((signatureData) => {
                            if (signDocumentWith2Steps) {
                                data.signatures = undefined;

                                const message = this.fromHex(signatureData.dataToSign);
                                const alg = {
                                    name: privateKey.algorithm.name,
                                    hash: 'SHA-256',
                                };
                                signature = await provider.subtle.sign(alg, privateKey, message);

                                // TODO mettre les infos à envoyer en step 2 dans data !!!!
                            }
                        }),
                        exhaustMap(() => signDocumentWith2Steps ? this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, data) : null),
                        tap(() => {
                            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList] !== undefined) {
                                this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                                if (this.signaturesService.documentsListCount.current > 0) {
                                    this.signaturesService.documentsListCount.current--;
                                }
                            }
                            resolve(true);
                        }),
                        catchError((err: any) => {
                            this.notificationService.handleErrors(err);
                            resolve(false);
                            return of(false);
                        })
                    ).subscribe();
            } else {
                resolve(false);
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
        this.signaturesService.sideNavRigtDatas = {
            mode: 'visaWorkflow',
            width: '450px',
            locked: false,
        };
    }

    openDocumentList() {
        this.menu.open('right-menu');
        this.signaturesService.sideNavRigtDatas = {
            mode: 'documentList',
            width: '450px',
            locked: false,
        };
    }

    openAssociatedDocuments() {
        this.menu.open('right-menu');
        this.signaturesService.sideNavRigtDatas = {
            mode: 'associatedDocuments',
            width: '450px',
            locked: false,
        };
    }

    openMainDocumentDetail() {
        this.menu.open('right-menu');
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

    back() {
        this.navCtrl.back();
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }

    openSelect() {
        if (this.totalPages > 1) {
            this.pagesList.open();
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
