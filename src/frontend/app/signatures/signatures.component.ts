import { Component, Input, OnInit, Renderer2, ViewChild } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { SignaturesContentService } from '../service/signatures.service';
import { DomSanitizer } from '@angular/platform-browser';
import * as $ from 'jquery';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';
import { IonSlides, ModalController } from '@ionic/angular';
import { SignaturePadPageComponent } from '../pad/pad.component';

@Component({
    selector: 'app-signatures',
    templateUrl: 'signatures.component.html',
    styleUrls: ['signatures.component.scss'],
})
export class SignaturesComponent implements OnInit {

    @Input() currentWorflow: any;
    @Input() signLock: any;

    loading: boolean = true;
    scrolling: boolean = false;
    signPosMode: boolean = false;
    datePosMode: boolean = false;
    title: string = 'lang.signatures';
    slideOpts = {
        initialSlide: 0,
        speed: 400,
        direction: 'vertical'
    };
    signaturesList: any[] = [];

    @ViewChild('slides', { static: false }) slides: IonSlides;
    inAllPage = false;
    count = 0;

    constructor(private translate: TranslateService,
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        private bottomSheetRef: MatBottomSheet,
        private sanitization: DomSanitizer,
        public notificationService: NotificationService,
        public authService: AuthService,
        private localStorage: LocalStorageService,
        private renderer: Renderer2,
        public modalController: ModalController
    ) {
    }

    dismissModal() {
        this.modalController.dismiss('cancel');
    }

    scroll(ev: any) {
        if (!this.scrolling) {
            this.scrolling = true;
            if (ev.deltaY < 0) {
                this.slides.slidePrev();
            } else {
                this.slides.slideNext();
            }
            setTimeout(() => {
                this.scrolling = false;
            }, 500);
        }
    }

    ngOnInit() {
        this.initSignatures();
        this.signPosMode = this.currentWorflow.signaturePositions.length > 0 && this.emptySigns();
    }

    initSignatures() {
        this.signaturesList = [];
        let obj: any[] = [];
        let index = 0;
        const mergedSign = this.signaturesService.signaturesListSubstituted.concat(this.signaturesService.signaturesList);
        mergedSign.forEach((element: any) => {
            if (index === 6) {
                this.signaturesList.push(obj);
                obj = [element];
                index = 0;
            } else {
                obj.push(element);
                index++;
            }
        });
        if (obj.length > 0) {
            this.signaturesList.push(obj);
        }
    }

    ionViewDidEnter() {
        this.loading = false;
    }

    async openSignatures() {
        const modal = await this.modalController.create({
            component: SignaturePadPageComponent,
            cssClass: 'my-custom-class'
        });
        await modal.present();
        const { data } = await modal.onWillDismiss();
        if (data === 'reload') {
            this.initSignatures();
        }
    }

    closeSignatures() {
        this.signaturesService.showSign = false;
    }

    openPad() {
        this.signaturesService.showPad = true;
        this.closeSignatures();
    }

    selectSignature(signature: any) {

        const percentWidth = 25;
        signature.width = percentWidth;

        const signPosCurrentPage = this.currentWorflow.signaturePositions.filter((item: any) => item.page === this.signaturesService.currentPage);
        const signPosOtherPage = this.currentWorflow.signaturePositions.filter((item: any) => item.page !== this.signaturesService.currentPage);

        if (!this.signPosMode || (signPosCurrentPage.length === 0 && signPosOtherPage.length === 0)) {
            signature.positionX = 60;
            signature.positionY = 80;
            this.storeSignature(signature, this.signaturesService.currentPage);
            this.notificationService.success('lang.signatureInDocAdded');
            this.modalController.dismiss('success');
        } else {
            if (signPosCurrentPage.length > 0) {
                signature.positionX = signPosCurrentPage[0].positionX;
                signature.positionY = signPosCurrentPage[0].positionY;
                this.storeSignature(signature, this.signaturesService.currentPage);
            }

            signPosOtherPage.forEach((postion: any) => {
                signature.positionX = postion.positionX;
                signature.positionY = postion.positionY;
                this.storeSignature(signature, postion.page);
            });

            if (this.currentWorflow.signaturePositions.length === 1) {
                this.notificationService.success('lang.signatureInDocAddedAlt');
            } else {
                this.translate.get('lang.signaturesInDocAdded', { 0: this.currentWorflow.signaturePositions.map((item: any) => item.page) }).subscribe((res: string) => {
                    this.notificationService.success(res);
                });
            }

            if (signPosCurrentPage.length === 0 && signPosOtherPage.length > 0) {
                this.modalController.dismiss({ redirectPage: signPosOtherPage[0].page });
            } else {
                this.modalController.dismiss('success');
            }
        }
    }

    addNewDate() {
        const dateBlock: any = {
            width: (130 * 100) / this.signaturesService.workingAreaWidth,
            height: (30 * 100) / this.signaturesService.workingAreaHeight,
            positionX: 0,
            positionY: 0
        };
        const datePosCurrentPage = this.currentWorflow.datePositions.filter((item: any) => item.page === this.signaturesService.currentPage);
        const datePosOtherPage = this.currentWorflow.datePositions.filter((item: any) => item.page !== this.signaturesService.currentPage);

        if (!this.datePosMode || (datePosCurrentPage.length === 0 && datePosOtherPage.length === 0)) {
            dateBlock.positionX = 130;
            dateBlock.positionY = 30;
            this.storeDate(dateBlock, this.signaturesService.currentPage);
            this.notificationService.success('lang.dateInDocAdded');
            setTimeout(() => {
                const svg = document.getElementById('testSVG_' + (this.signaturesService.datesContent[this.signaturesService.currentPage].length - 1));
                console.log(svg);
            }, 200);
            this.modalController.dismiss('success');
        } else {
            if (datePosCurrentPage.length > 0) {
                dateBlock.positionX = datePosCurrentPage[0].positionX;
                dateBlock.positionY = datePosCurrentPage[0].positionY;
                this.storeDate(dateBlock, this.signaturesService.currentPage);
            }

            datePosOtherPage.forEach((postion: any) => {
                dateBlock.positionX = postion.positionX;
                dateBlock.positionY = postion.positionY;
                this.storeDate(dateBlock, postion.page);
            });

            if (this.currentWorflow.signaturePositions.length === 1) {
                this.notificationService.success('lang.dateInDocAddedAlt');
            } else {
                this.translate.get('lang.dateInDocAdded', { 0: this.currentWorflow.signaturePositions.map((item: any) => item.page) }).subscribe((res: string) => {
                    this.notificationService.success(res);
                });
            }

            if (datePosCurrentPage.length === 0 && datePosOtherPage.length > 0) {
                this.modalController.dismiss({ redirectPage: datePosOtherPage[0].page });
            } else {
                this.modalController.dismiss('success');
            }
        }
    }

    getDateContent() {
        const svg = document.getElementById('testSVG_' + (this.signaturesService.datesContent[this.signaturesService.currentPage].length - 1));
        const data = new XMLSerializer().serializeToString(svg);
        const blob = new Blob([data], { type: 'image/svg+xml' });

        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function () {
            var base64data = reader.result;
            console.log(base64data);
        }
    }

    storeSignature(signature: any, page: number) {
        if (!this.signaturesService.signaturesContent[page]) {
            this.signaturesService.signaturesContent[page] = [];
        }
        this.signaturesService.signaturesContent[page].push(JSON.parse(JSON.stringify(signature)));
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'date': this.signaturesService.datesContent, 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
    }

    storeDate(date: any, page: number) {
        if (!this.signaturesService.datesContent[page]) {
            this.signaturesService.datesContent[page] = [];
        }
        this.signaturesService.datesContent[page].push(JSON.parse(JSON.stringify(date)));
        setTimeout(() => {
            const svg = document.getElementById('testSVG_' + (this.signaturesService.datesContent[this.signaturesService.currentPage].length - 1));
            const data = new XMLSerializer().serializeToString(svg);
            const blob = new Blob([data], { type: 'image/svg+xml' });

            const reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onloadend = () => {
                this.signaturesService.datesContent[page][this.signaturesService.datesContent[page].length - 1].content = reader.result;
                this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'date': this.signaturesService.datesContent, 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
            };
        }, 200);
    }

    removeSignature(signature: any) {
        const r = confirm(this.translate.instant('lang.wantDeleteSignature'));

        if (r) {
            this.http.delete('../rest/users/' + this.authService.user.id + '/signatures/' + signature.id)
                .subscribe(() => {
                    this.signaturesService.signaturesList = this.signaturesService.signaturesList.filter((element) => element.id !== signature.id);
                    this.notificationService.success('lang.signatureDeleted');
                    this.initSignatures();
                }, (err: any) => {
                    this.notificationService.error(err.error.errors);
                });
        }
    }

    toggleAllPage() {
        this.inAllPage = !this.inAllPage;
    }

    tapEvent(signature: any, i: any, mode: string) {
        this.count++;

        setTimeout(() => {
            if (this.count === 1) {
                this.count = 0;
            } else if (this.count > 1) {
                this.count = 0;
                const id = mode === 'substitute' ? ('imgSignSub_' + i) : ('imgSign_' + i);
                this.selectSignature(signature);
            }
        }, 250);
    }

    handleFileInput(files: FileList) {
        const fileToUpload = files.item(0);

        if (fileToUpload.size <= 1000000) {
            if (['image/png', 'image/jpg', 'image/jpeg', 'image/gif'].indexOf(fileToUpload.type) !== -1) {
                const myReader: FileReader = new FileReader();
                myReader.onloadend = (e) => {

                    const newEncodedSign = myReader.result.toString().replace('data:' + fileToUpload.type + ';base64,', '');
                    this.localStorage.save('signature', JSON.stringify(newEncodedSign));

                    // Save signature in BDD
                    const newSign = {
                        'id': 0,
                        'encodedSignature': newEncodedSign,
                        'format': 'png'
                    };
                    this.http.post('../rest/users/' + this.authService.user.id + '/signatures', newSign)
                        .subscribe((data: any) => {
                            newSign.id = data.signatureId;
                            this.signaturesService.signaturesList.unshift(
                                {
                                    id: newSign.id,
                                    encodedSignature: newSign.encodedSignature
                                }
                            );
                            this.initSignatures();
                            this.notificationService.success('lang.signatureRegistered');
                        });
                };
                myReader.readAsDataURL(fileToUpload);
            } else {
                this.notificationService.error('lang.notAnImage');
            }
        } else {
            this.notificationService.error('lang.maxFileSizeReached');
        }
    }

    emptySigns() {
        let state = true;
        for (let pageNum = 1; pageNum <= this.signaturesService.totalPage; pageNum++) {
            if (this.signaturesService.signaturesContent[pageNum]) {
                if (this.signaturesService.signaturesContent[pageNum].length > 0) {
                    state = false;
                    break;
                }
            }
        }
        return state;
    }
}
