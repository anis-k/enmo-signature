import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { tap, catchError } from 'rxjs/operators';
import { SignaturesContentService } from './signatures.service';
import { NotificationService } from '../service/notification.service';
import { of } from 'rxjs';
import { FunctionsService } from './functions.service';
import { resolve } from 'path';
import { AlertController } from '@ionic/angular';

@Injectable({
    providedIn: 'root'
})
export class ActionsService {

    constructor(
        public http: HttpClient,
        public translate: TranslateService,
        public notificationService: NotificationService,
        public signaturesService: SignaturesContentService,
        private functionsService: FunctionsService,
        public alertController: AlertController,
    ) { }

    sendDocument(note: string, eSignature: any = null, signatureLength: any = null, tmpUniqueId: string = null, imgDocElements: any[] = null) {
        return new Promise(async (resolve) => {
            let data: any = {};

            if (this.signaturesService.currentAction > 0) {
                if (imgDocElements === null) {
                    data.signatures = await this.getElementsFromDoc();
                } else {
                    data.signatures = imgDocElements;
                }

                if (eSignature !== null) {
                    data = { ...data, ...eSignature };
                    data.step = 'hashCertificate';
                }

                if (note !== null) {
                    data['note'] = note;
                }

                if (signatureLength !== null) {
                    data.signatureLength = signatureLength;
                }

                if (!this.functionsService.empty(tmpUniqueId)) {
                    data.tmpUniqueId = tmpUniqueId;
                }

                this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, data)
                    .pipe(
                        tap((res: any) => {
                            if (eSignature !== null) {
                                const objSignData = {
                                    hashDocument: res.dataToSign,
                                    signatureContentLength: res.signatureContentLength,
                                    signatureFieldName: res.signatureFieldName,
                                    tmpUniqueId: res.tmpUniqueId
                                };
                                resolve(objSignData);
                            } else {
                                resolve(true);
                            }
                        }),
                        catchError((err: any) => {
                            this.notificationService.handleErrors(err);
                            if (err.status === 403) {
                                resolve(null);
                            } else {
                                resolve(false);
                            }
                            return of(false);
                        })
                    ).subscribe();
            } else {
                resolve(false);
            }
        });
    }

    async getElementsFromDoc(): Promise<any[]> {
        return new Promise(async (resolve) => {
            const signatures: any[] = [];
            for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                if (this.signaturesService.datesContent[index]) {
                    for (let indexSvg = 0; indexSvg < this.signaturesService.datesContent[index].length; indexSvg++) {
                        const date = this.signaturesService.datesContent[index][indexSvg];
                        const svgContent: any = await this.getSvgContent(indexSvg);
                        signatures.push(
                            {
                                'encodedImage': svgContent.replace('data:image/svg+xml;base64,', ''),
                                'width': date.width,
                                'height': date.height,
                                'positionX': date.positionX,
                                'positionY': date.positionY,
                                'type': 'SVG',
                                'page': index,
                            }
                        );
                        console.log('push date', signatures);
                    }
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
                    this.signaturesService.notesContent[index].forEach((noteItem: any) => {
                        signatures.push(
                            {
                                'encodedImage': noteItem.fullPath.replace('data:image/png;base64,', ''),
                                'width': noteItem.width,
                                'positionX': noteItem.positionX,
                                'positionY': noteItem.positionY,
                                'type': 'PNG',
                                'page': index,
                            }
                        );
                    });
                }
            }
            resolve(signatures);
        });
    }

    getSvgContent(index: number) {
        return new Promise((resolve) => {
            const svg = document.getElementById('testSVG_' + index);
            const data = new XMLSerializer().serializeToString(svg);
            const blob = new Blob([data], { type: 'image/svg+xml' });
            const reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onloadend = () => {
                resolve(reader.result);
            };
        });
    }

    checkGroupMail(mainDocument: any) {
        return new Promise(async (resolve) => {
            if (this.functionsService.empty(mainDocument.groupMailId)) {
                resolve(false);
            } else {
                const alert = await this.alertController.create({
                    header: this.translate.instant('lang.mailing'),
                    message: this.translate.instant('lang.makeActionOnDocInMailGroup'),
                    buttons: [
                        {
                            text: this.translate.instant('lang.yes'),
                            handler: () => {
                                resolve(true);
                            }
                        },
                        {
                            role: 'cancel',
                            text: this.translate.instant('lang.no'),
                            cssClass: 'secondary',
                            handler: () => {
                                resolve(false);
                            }
                        }
                    ]
                });
                await alert.present();
            }
        });
    }
}
