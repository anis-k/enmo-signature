import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { LatinisePipe } from 'ngx-pipes';
import { tap, catchError } from 'rxjs/operators';
import { SignaturesContentService } from './signatures.service';
import { NotificationService } from '../service/notification.service';
import { of } from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class ActionsService {

    constructor(
        public http: HttpClient,
        public translate: TranslateService,
        private latinisePipe: LatinisePipe,
        public notificationService: NotificationService,
        public signaturesService: SignaturesContentService,
    ) { }

    sendDocument(note: string, eSignature: any = null) {
        return new Promise(async (resolve) => {
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
                let data: any = {};

                data.signatures = signatures;

                if (eSignature !== null) {
                    data = {...data, ...eSignature };
                    data.step = 'hashCertificate';
                }

                if (note !== null) {
                    data['note'] = note;
                }

                this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, data)
                    .pipe(
                        tap((res: any) => {
                            if (eSignature !== null) {
                                const objSignData = {
                                    hashDocument : res.dataToSign,
                                    signatureContentLength : res.signatureContentLength
                                };
                                resolve(objSignData);
                            } else {
                                resolve(true);
                            }
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
}
