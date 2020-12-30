import { Component, OnInit } from '@angular/core';
import { MenuController, ModalController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { SignaturesContentService } from '../service/signatures.service';
import { VersionInformationComponent } from '../versionInformation/version-information.component';

@Component({
    templateUrl: 'home.component.html',
    styleUrls: ['home.component.scss'],
})
export class HomeComponent implements OnInit {

    loading: boolean = false;

    constructor(
        public signaturesService: SignaturesContentService,
        public translate: TranslateService,
        public menu: MenuController,
        public modalController: ModalController,
    ) { }

    ngOnInit(): void {
        this.menu.enable(true, 'left-menu');
        this.menu.enable(false, 'right-menu');
        this.menu.open('left-menu');
    }

    async openInformationModal() {
        const modal = await this.modalController.create({
            component: VersionInformationComponent,
            cssClass: 'my-custom-class'
        });
        await modal.present();
    }
}
