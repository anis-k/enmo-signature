import { Component, Input, OnInit } from '@angular/core';
import { ModalController } from '@ionic/angular';

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

    constructor(
        public modalController: ModalController
    ) { }

    ngOnInit(): void {
        this.interval = setInterval(async () => {
            console.log('attempt', this.attempt);
            const res = await this.checkCertificate();
            if (res) {
                this.status = 'SUCCESS';
                clearInterval(this.interval);
                setTimeout(() => {
                    this.modalController.dismiss(true);
                }, 1000);
            } else if (this.attempt === this.nbTry) {
                this.status = 'ERROR';
                clearInterval(this.interval);
                setTimeout(() => {
                    this.modalController.dismiss(false);
                }, 1000);
            }
            this.attempt++;
        }, 1000);
    }

    checkCertificate() {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('end request');
                resolve(false);
            }, 800);
        });
    }

}
