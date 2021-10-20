import { Component, Input, OnInit } from '@angular/core';
import { LoadingController, ModalController } from '@ionic/angular';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../notification.service';
import { TranslateService } from '@ngx-translate/core';
import { ActionsService } from '../actions.service';
import { SignaturesContentService } from '../signatures.service';
import { AuthService } from '../auth.service';
import { FunctionsService } from '../functions.service';

@Component({
    selector: 'signature-method-modal',
    templateUrl: 'signature-method-modal.component.html',
    styleUrls: ['./signature-method-modal.component.scss']
})
export class SignatureMethodModalComponent implements OnInit {

    @Input() note: string;
    @Input() signatureMode: string;
    @Input() idsToProcess: any[];

    filters: any = {
        //   onlySmartcards: false,
        expired: false,
        keyUsage: [],
        onlyWithPrivateKey: true
    };

    provider: any = null;
    cert: any = null;
    certPem: any = null;
    privateKey: any = null;

    signatures: any[] = [];

    signature: string;
    certificate: any;
    signatureLength: any = null;

    server: any = null;

    constructor(
        public modalController: ModalController,
        public http: HttpClient,
        public translate: TranslateService,
        public notificationService: NotificationService,
        public loadingController: LoadingController,
        public signaturesService: SignaturesContentService,
        public actionsService: ActionsService,
        private functionsService: FunctionsService,
        public authService: AuthService
    ) { }

    async ngOnInit(): Promise<void> {
        const signatureModeData = this.authService.signatureRoles.filter((mode: any) => mode.id === this.signatureMode)[0];
        if (!this.functionsService.empty(signatureModeData.issuer)) {
            this.filters.issuerDNMatch = new RegExp(signatureModeData.issuer, 'i');
        }
        if (!this.functionsService.empty(signatureModeData.subject)) {
            this.filters.subjectDNMatch = new RegExp(signatureModeData.subject, 'i');
        }
        if (!this.functionsService.empty(signatureModeData.keyUsage)) {
            this.filters.keyUsage.push(signatureModeData.keyUsage);
        }
    }

    async certificateChosen(certData: any) {
        this.loadingController.create({
            message: this.translate.instant('lang.processing'),
            spinner: 'dots'
        }).then(async (load: HTMLIonLoadingElement) => {
            load.present();

            try {
                this.server = certData.detail.server;
                this.checkWebsocketSession();
                this.provider = await certData.detail.server.getCrypto(certData.detail.providerId);
                this.checkWebsocketSession();
                this.cert = await this.provider.certStorage.getItem(certData.detail.certificateId);
                this.checkWebsocketSession();
                this.certPem = await this.provider.certStorage.exportCert('pem', this.cert);
                this.checkWebsocketSession();
                this.privateKey = await this.provider.keyStorage.getItem(certData.detail.privateKeyId);
            } catch (e) {
                this.notificationService.error(e);
                load.dismiss();
                this.modalController.dismiss(false);
                return;
            }

            this.certificate = {
                certificate: this.certPem
            };

            let result: any = false;

            for (let index = 0; index < this.idsToProcess.length; index++) {
                this.signatures = await this.actionsService.getElementsFromDoc();
                result = await this.sendAndSign(this.idsToProcess[index]);
            }
            load.dismiss();
            this.modalController.dismiss(result);
        });
    }

    async checkWebsocketSession() {
        // session closed?!
        while (this.server.client.state !== WebSocket.OPEN) {
            await this.server.connect();
            await new Promise(resolve => setTimeout(resolve, 150));
        }
    }

    async sendAndSign(idToProcess: number) {
        let allSignaturesComplete: boolean = false;
        let res: any = {};
        while (!allSignaturesComplete) {
            let signDocComplete: any = false;
            while (signDocComplete === false) {
                res = await this.fusionStampAndGenerateSignature(idToProcess, res.tmpUniqueId);
                if (res === null) {
                    return false;
                } else if (res !== false) {
                    signDocComplete = await this.signDocument(idToProcess, res.hashDocument, res.signatureContentLength, res.signatureFieldName, res.tmpUniqueId);
                    if (signDocComplete === true) {
                        this.signatures.shift();
                        allSignaturesComplete = this.signatures.length === 0;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return allSignaturesComplete;
    }

    async fusionStampAndGenerateSignature(idToProcess: number, tmpUniqueId: string = null) {
        let res: any = {};
        res = await this.actionsService.sendDocument(idToProcess, null, this.certificate, this.signatureLength, tmpUniqueId, this.signatures);
        return res;
    }

    signDocument(idToProcess: number, hashDocument: any, eSignatureLength: any, signatureFieldName: any, tmpUniqueId: string) {
        return new Promise(async (resolve) => {
            const alg = {
                name: this.privateKey.algorithm.name,
                hash: 'SHA-256',
            };
            const hashDocumentHex = this.fromHex(hashDocument);

            let hashSignature: any;

            try {
                this.checkWebsocketSession();
                hashSignature = await this.provider.subtle.sign(alg, this.privateKey, hashDocumentHex);
            } catch (e) {
                this.notificationService.error(e);
                resolve(false);
                return of(false);
            }

            const note = {
                note: this.note
            };

            const objEsign = {
                signatures : this.signatures,
                certificate: this.certPem,
                hashSignature: this.toHex(hashSignature),
                signatureContentLength: eSignatureLength,
                signatureFieldName: signatureFieldName,
                tmpUniqueId: tmpUniqueId,
            };

            const objToSend = {...note, ...objEsign };

            this.http.put('../rest/documents/' + idToProcess + '/actions/' + this.signaturesService.currentAction, objToSend)
                .pipe(
                    tap(() => {
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        if (err.error.newSignatureLength !== undefined) {
                            this.signatureLength = err.error.newSignatureLength;
                            resolve(false);
                        } else {
                            this.notificationService.handleErrors(err);
                            resolve('error');
                        }
                        return of(false);
                    })
                ).subscribe();

        });
    }

    cancelSign() {
        this.modalController.dismiss(false);
    }

    toHex(buffer: any) {
        const buf = new Uint8Array(buffer),
            splitter = '',
            res = [],
            len = buf.length;

        for (let i = 0; i < len; i++) {
            const char = buf[i].toString(16);
            res.push(char.length === 1 ? '0' + char : char);
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
