import { Component, OnInit, Renderer2, ViewChild } from '@angular/core';
import { MatBottomSheet, MatBottomSheetConfig } from '@angular/material/bottom-sheet';
import { SignaturesContentService } from '../service/signatures.service';
import { DomSanitizer } from '@angular/platform-browser';
import * as $ from 'jquery';
import { HttpClient } from '@angular/common/http';
import { trigger, transition, style, animate, stagger, query } from '@angular/animations';
import { NotificationService } from '../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';
import { IonSlides, ModalController } from '@ionic/angular';
import { log } from 'console';
import { SignaturePadPageComponent } from '../pad/pad.component';

@Component({
    selector: 'app-signatures',
    templateUrl: 'signatures.component.html',
    styleUrls: ['signatures.component.scss'],
})
export class SignaturesComponent implements OnInit {
    loading: boolean = true;
    scrolling: boolean = false;
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

    selectSignature(signature: any, img: any) {

        signature.positionX = 60;
        signature.positionY = 80;

        const percentWidth = 25;

        signature.width = percentWidth;

        if (!this.signaturesService.signaturesContent[this.signaturesService.currentPage]) {
            this.signaturesService.signaturesContent[this.signaturesService.currentPage] = [];
        }
        this.signaturesService.signaturesContent[this.signaturesService.currentPage].push(JSON.parse(JSON.stringify(signature)));
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
        this.notificationService.success('lang.signatureInDocAdded');
        this.modalController.dismiss('success');
    }

    removeSignature(signature: any, i: any) {
        const r = confirm(this.translate.instant('lang.wantDeleteSignature'));

        if (r) {
            this.http.delete('../rest/users/' + this.authService.user.id + '/signatures/' + signature.id)
                .subscribe(() => {
                    this.signaturesService.signaturesList.splice(i, 1);
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
                this.selectSignature(signature, id);
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
}
