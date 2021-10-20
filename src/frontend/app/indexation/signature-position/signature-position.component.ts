import { Component, Input, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { NgxExtendedPdfViewerService } from 'ngx-extended-pdf-viewer';
import { LoadingController, ModalController } from '@ionic/angular';
import { SignaturesContentService } from '../../service/signatures.service';

@Component({
    templateUrl: 'signature-position.component.html',
    styleUrls: ['signature-position.component.scss'],
})
export class SignaturePositionComponent implements OnInit {

    @Input() workflow: any = [];
    @Input() resource: any = [];
    @Input() pdfContent: any = null;

    loading: boolean = false;
    dragging: boolean = false;

    pages: number[] = [];

    currentUser: number = 0;
    currentPage: number = 1;
    currentSignature: any = {
        positionX: 0,
        positionY: 0
    };

    workingAreaWidth: number = 0;
    workingAreaHeight: number = 0;
    signList: any[] = [];

    imgContent: any = null;
    load: HTMLIonLoadingElement = null;

    pagesLoaded: boolean = false;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        private pdfViewerService: NgxExtendedPdfViewerService,
        public modalController: ModalController,
        public loadingController: LoadingController,
    ) { }

    ngOnInit(): void {
        this.loadingController.create({
            message: this.translate.instant('lang.processing'),
            spinner: 'dots'
        }).then((load: HTMLIonLoadingElement) => {
            this.load = load;
            this.load.present();
        });
        if (this.resource.signPos) {
            this.initSignPos();
        }
    }

    initSignPos() {
        this.workflow.forEach((user: any, index: number) => {
            if (user.signaturePositions?.length > 0) {
                this.signList = this.signList.concat(user.signaturePositions.filter((pos: any) => pos.mainDocument === this.resource.mainDocument).map((pos: any) => ({
                    ...pos,
                    sequence : index
                })));
            }
        });
    }

    onSubmit() {
        this.modalController.dismiss(this.formatData());
    }

    async onPagesLoaded(ev: any) {
        this.pages = Array.from({ length: ev.pagesCount }).map((_, i) => i + 1);
        this.changePage(1);
    }

    public async exportAsImage(): Promise<void> {
        const scale = { width: 1000 };
        const data = await this.pdfViewerService.getPageAsImage(this.currentPage, scale);
        this.getImageDimensions(data);
        this.imgContent = data;
        this.pagesLoaded = true;
    }

    getImageDimensions(imgContent: any): void {
        const img = new Image();
        img.onload = (data: any) => {
            this.workingAreaWidth = data.target.naturalWidth;
            this.workingAreaHeight = data.target.naturalHeight;
        };
        img.src = imgContent;
    }

    changePage(page: number) {
        this.currentPage = page;
        this.exportAsImage();
    }

    moveSign(event: any, i: number) {
        const percentx = (event.x * 100) / this.workingAreaWidth;
        const percenty = (event.y * 100) / this.workingAreaHeight;
        this.signList.filter((item: any) => item.sequence === this.currentUser && item.page === this.currentPage)[0].position.positionX = percentx;
        this.signList.filter((item: any) => item.sequence === this.currentUser && item.page === this.currentPage)[0].position.positionY = percenty;
        this.dragging = false;
    }

    emptySign() {
        return this.signList.filter((item: any) => item.sequence === this.currentUser && item.page === this.currentPage).length === 0;
    }

    initSign() {
        this.signList.push(
            {
                sequence: this.currentUser,
                page: this.currentPage,
                position: {
                    positionX: 75,
                    positionY: 90
                }
            }
        );
        let indexOfElement: number = 0;
        const element: any[] = Array.from(document.getElementsByClassName('drag-scroll-content'));
        element.forEach((item: any, index: number) => {
            if (item.offsetParent !== null) {
                indexOfElement = index;
                return true;
            }
        });
        document.getElementsByClassName('drag-scroll-content')[indexOfElement].scrollTop = this.workingAreaHeight;
    }

    getUserSignPosPage(workflowIndex: number) {
        return this.signList.filter((item: any) => item.sequence === workflowIndex);
    }

    selectUser(workflowIndex: string) {
        this.currentUser = +workflowIndex;
    }

    getUserName(workflowIndex: number) {
        return this.workflow[workflowIndex].userDisplay;
    }

    goToSignUserPage(workflowIndex: number, page: number) {
        this.currentUser = workflowIndex;
        this.currentPage = page;
        this.exportAsImage();
    }

    deleteSign(index: number) {
        this.signList.splice(index, 1);
    }

    formatData() {
        let objToSend: any[] = [];
        this.workflow.forEach((element: any, index: number) => {
            if (this.signList.filter((item: any) => item.sequence === index).length > 0) {
                objToSend = objToSend.concat(this.signList.filter((item: any) => item.sequence === index));
            }
        });
        return objToSend;
    }

    imageLoaded(ev: any) {
        this.load.dismiss();
    }
}
