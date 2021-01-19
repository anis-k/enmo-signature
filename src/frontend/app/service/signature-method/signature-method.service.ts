import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LoadingController, ModalController } from '@ionic/angular';
import { SignatureMethodModalComponent } from './signature-method-modal.component';
import { ActionsService } from '../actions.service';
import { TranslateService } from '@ngx-translate/core';

@Injectable({
    providedIn: 'root'
})
export class SignatureMethodService {

    constructor(
        public http: HttpClient,
        public modalController: ModalController,
        public actionsService: ActionsService,
        public loadingController: LoadingController,
        private translate: TranslateService,
    ) { }

    async checkAuthenticationAndLaunchAction(userWorkflow: any, note: any = null, idsToProcess: any[]) {
        if (['rgs_2stars', 'rgs_2stars_timestamped', 'inca_card', 'inca_card_eidas'].indexOf(userWorkflow.signatureMode) > -1) {
            const res = await this.openRgsAuth(note, userWorkflow.signatureMode, idsToProcess);
            return res;
        } else {
            const res = await this.launchDefaultMode(note, idsToProcess);
            return res;
        }
    }

    async launchDefaultMode(note: string, idsToProcess: any[]) {
        return new Promise(async (resolve) => {
            const loading = await this.loadingController.create({
                message: this.translate.instant('lang.loadingValidation'),
                spinner: 'dots',
            });
            loading.present();
            let res: any = true;
            for (let index = 0; index < idsToProcess.length; index++) {
                res = await this.actionsService.sendDocument(idsToProcess[index], note);
            }
            loading.dismiss();
            resolve(res);
        });
    }

    async openRgsAuth(note: string, signatureMode: string, idsToProcess: any[]) {
        return new Promise(async (resolve) => {
            const modal = await this.modalController.create({
                component: SignatureMethodModalComponent,
                componentProps: {
                    note: note,
                    signatureMode: signatureMode,
                    idsToProcess: idsToProcess
                }
            });
            await modal.present();
            const { data } = await modal.onWillDismiss();
            resolve(data);
        });
    }
}
