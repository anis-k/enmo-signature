import { Component, Input, OnInit } from '@angular/core';
import { ModalController } from '@ionic/angular';
import {catchError, tap} from 'rxjs/operators';
import {of} from 'rxjs';
import {HttpClient} from '@angular/common/http';
import {NotificationService} from '../notification.service';

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

    constructor(
        public modalController: ModalController,
        public http: HttpClient,
        public notificationService: NotificationService,
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
        console.log(certData);

        const provider = await certData.detail.server.getCrypto(certData.detail.providerId);

        const cert = await provider.certStorage.getItem(certData.detail.certificateId);
        const certPem = await provider.certStorage.exportCert('pem', cert);
        const privateKey = await provider.keyStorage.getItem(certData.detail.privateKeyId);

        console.log('cert = ');
        console.log(cert);
        console.log('certPem = ');
        console.log(certPem);
        console.log('privateKey = ');
        console.log(privateKey);

        this.http.post('../rest/testFortify?action=start', {certificate: certPem}).pipe(
            tap(async (dataToSign: any) => {
                const message = this.fromHex(dataToSign.dataToSign);
                const alg = {
                    name: privateKey.algorithm.name,
                    hash: 'SHA-256',
                };
                const signature = await provider.subtle.sign(alg, privateKey, message);

                return this.http.post('../rest/testFortify?action=complete', {signature: signature});
            }),
            tap(() => {
                console.log('signature ok');
                this.modalController.dismiss(true);
            }),
            catchError(err => {
                this.notificationService.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    cancelSign(data: any) {
        console.log(data);
        this.modalController.dismiss(false);
    }

    fromHex(hexString) {
        const res = new Uint8Array(hexString.length / 2);
        for (let i = 0; i < hexString.length; i = i + 2) {
            const c = hexString.slice(i, i + 2);
            res[i / 2] = parseInt(c, 16);
        }
        return res.buffer;
    }
}
