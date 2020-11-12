import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { MenuController } from '@ionic/angular';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'indexation.component.html',
    styleUrls: ['indexation.component.scss'],
})
export class IndexationComponent implements OnInit {

    loading: boolean = false;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

    constructor(
        private menu: MenuController,
        public signaturesService: SignaturesContentService,
        public viewContainerRef: ViewContainerRef,
    ) { }

    ngOnInit(): void {
        this.menu.enable(true, 'left-menu');
        this.menu.open('left-menu');
        this.menu.enable(true, 'right-menu');
        this.menu.open('right-menu');
    }

    ionViewWillEnter() {
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }
}
