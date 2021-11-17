import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { ToastController } from '@ionic/angular';

@Injectable()
export class NotificationService {

    constructor(
        private translate: TranslateService,
        private router: Router,
        public toastController: ToastController
    ) { }

    async success(message: string) {
        const msg = message.includes('lang.') ? this.translate.instant(message) : message;
        const toast = await this.toastController.create({
            cssClass: 'notif-success',
            duration: 3000,
            message: msg,
            position: 'top'
        });
        toast.present();
    }

    async error(message: string) {
        const msg = message.includes('lang.') ? this.translate.instant(message) : message;
        const toast = await this.toastController.create({
            cssClass: 'notif-error',
            duration: 3000,
            message: msg,
            position: 'top'
        });
        toast.present();
    }

    handleErrors(err: any) {
        console.log(err);
        if (err.status === 0 && err.statusText === 'Unknown Error') {
            this.error('lang.connectionServerFailed');
        } else {
            if (err.error.errors !== undefined) {
                if (err.error.lang !== undefined) {
                    this.error('lang.' + err.error.lang);
                } else {
                    this.error(err.error.errors);
                }
                if (err.status === 403 || err.status === 404) {
                    this.router.navigate(['/home']);
                }
            } else if (err.error.exception !== undefined) {
                this.error(err.error.exception[0].message);
            } else if (err.error.error !== undefined && err.error.error.message !== undefined) {
                this.error(err.error.error.message);
            } else if (err.error.error[0] !== undefined) {
                this.error(err.error.error[0].message);
            } else {
                this.error(err.message);
            }
        }
    }
}
