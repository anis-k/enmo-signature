import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ModalController } from '@ionic/angular';
import { SignatureMethodModalComponent } from './signature-method-modal.component';
import { ActionsService } from '../actions.service';

@Injectable({
  providedIn: 'root'
})
export class SignatureMethodService {

  constructor(
    public http: HttpClient,
    public modalController: ModalController,
    public actionsService: ActionsService,
  ) { }

  async checkAuthenticationAndLaunchAction(userWorkflow: any) {
    console.log(userWorkflow);
    if (userWorkflow.signatureMode === 'rgs_2stars') {
      const res = await this.openRgsAuth();
      return res;
    } else {
      const res = await this.launchDefaultMode();
      return res;
    }
  }

  async launchDefaultMode() {
    return new Promise(async (resolve) => {
      const res = await this.actionsService.sendDocument();
      resolve(res);
    });
  }

  async openRgsAuth() {
    return new Promise(async (resolve) => {
      const modal = await this.modalController.create({
        component: SignatureMethodModalComponent,
        componentProps: {}
      });
      await modal.present();
      const { data } = await modal.onWillDismiss();
      resolve(data);
    });
  }
}
