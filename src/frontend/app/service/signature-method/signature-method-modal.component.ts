import { Component, Input, OnInit } from '@angular/core';
import { LoadingController, ModalController } from '@ionic/angular';
import { catchError, exhaustMap, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../notification.service';
import { TranslateService } from '@ngx-translate/core';
import { ActionsService } from '../actions.service';
import { SignaturesContentService } from '../signatures.service';

@Component({
    selector: 'signature-method-modal',
    templateUrl: 'signature-method-modal.component.html',
    styleUrls: ['./signature-method-modal.component.scss']
})
export class SignatureMethodModalComponent implements OnInit {

    status = 'WAITING';
    attempt = 1;
    nbTry = 10;
    interval: any;

    filters: any = {
        //   onlySmartcards: false,
        expired: false,
        //   subjectDNMatch: 'apple',
        //   subjectDNMatch: new RegExp(/apple/),
        //   issuerDNMatch: 'demo',
        //   issuerDNMatch: new RegExp(/demo/),
        //   keyUsage: ['digitalSignature'],
        onlyWithPrivateKey: true
    };

    provider: any = null;
    cert: any = null;
    certPem: any = null;
    privateKey: any = null;

    signature: string;

    constructor(
        public modalController: ModalController,
        public http: HttpClient,
        public translate: TranslateService,
        public notificationService: NotificationService,
        public loadingController: LoadingController,
        public signaturesService: SignaturesContentService,
        public actionsService: ActionsService,
    ) { }

    ngOnInit(): void {
        // this.interval = setInterval(async () => {
        //     console.log('attempt', this.attempt);
        //     const res = await this.checkCertificate();
        //     if (res) {
        //         this.status = 'SUCCESS';
        //         clearInterval(this.interval);
        //         setTimeout(() => {
        //             this.modalController.dismiss(true);
        //         }, 1000);
        //     } else if (this.attempt === this.nbTry) {
        //         this.status = 'ERROR';
        //         clearInterval(this.interval);
        //         setTimeout(() => {
        //             this.modalController.dismiss(false);
        //         }, 1000);
        //     }
        //     this.attempt++;
        // }, 1000);
    }

    checkCertificate() {
        // return new Promise((resolve) => {
        //     setTimeout(() => {
        //         console.log('end request');
        //         resolve(false);
        //     }, 800);
        // });
    }

    async continueSignature(certData: any) {
        this.loadingController.create({
            message: this.translate.instant('lang.processing'),
            spinner: 'dots'
        }).then(async (load: HTMLIonLoadingElement) => {
            load.present();

            this.provider = await certData.detail.server.getCrypto(certData.detail.providerId);
            this.cert = await this.provider.certStorage.getItem(certData.detail.certificateId);
            this.certPem = await this.provider.certStorage.exportCert('pem', this.cert);
            this.privateKey = await this.provider.keyStorage.getItem(certData.detail.privateKeyId);

            const certificate = {
                certificate: this.certPem
            }

            const res: any = await this.actionsService.sendDocument(certificate);
            console.log('sendDocument', res);
            
            if (res !== false) {
                await this.signDocument(res.hashDocument, res.signatureContentLength);
            }
            load.dismiss();
        });

        // this.http.post('../rest/testFortify?action=start', {certificate: certPem}).pipe(
        //     tap(async (dataToSign: any) => {
        //         const message = this.fromHex(dataToSign.dataToSign);
        //         const alg = {
        //             name: privateKey.algorithm.name,
        //             hash: 'SHA-256',
        //         };
        //         this.signature = await provider.subtle.sign(alg, privateKey, message);
        //     }),
        //     exhaustMap(() => this.http.post('../rest/testFortify?action=complete', {signature: this.signature})),
        //     tap(() => {
        //         console.log('signature ok');
        //         this.modalController.dismiss(true);
        //     }),
        //     catchError(err => {
        //         this.notificationService.handleErrors(err);
        //         return of(false);
        //     })
        // ).subscribe();
    }

    signDocument(hashDocument: any, eSignatureLength: any) {
        console.log(hashDocument);
        console.log(eSignatureLength);
        
        return new Promise(async (resolve) => {
            const alg = {
                name: this.privateKey.algorithm.name,
                hash: 'SHA-256',
            };
            const hashDocumentHex = this.fromHex(hashDocument);

            console.log('hashDocumentHex', hashDocumentHex);
            

            const hashSignature = await this.provider.subtle.sign(alg, this.privateKey, hashDocumentHex);

            console.log('hashSignature', hashSignature);
            

            const objEsign = {
                certificate: this.certPem,
                hashSignature: this.toHex(hashSignature),
                signatureContentLength: eSignatureLength
            }
            this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, objEsign)
                .pipe(
                    tap((res: any) => {
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notificationService.handleErrors(err);
                        resolve(false);
                        return of(false);
                    })
                ).subscribe();

        });
    }

    cancelSign(data: any) {
        console.log(data);
        this.modalController.dismiss(false);
    }

    toHex(buffer: any) {
        let buf = new Uint8Array(buffer),
            splitter = "",
            res = [],
            len = buf.length;

        for (let i = 0; i < len; i++) {
            let char = buf[i].toString(16);
            res.push(char.length === 1 ? "0" + char : char);
        }
        return res.join(splitter);
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
