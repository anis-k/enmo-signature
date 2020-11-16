import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { Router } from '@angular/router';
import { AlertController, LoadingController, MenuController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { AuthService } from '../service/auth.service';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'search.component.html',
    styleUrls: ['search.component.scss'],
})
export class SearchComponent implements OnInit {

    loading: boolean = false;
    filesToUpload: any[] = [];
    errors: any[] = [];

    ressources = Array.from({ length: 5000 }).map((_, i) => {
        return {
          resId: i
        };
      });

    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        public router: Router,
        private menu: MenuController,
        public signaturesService: SignaturesContentService,
        public viewContainerRef: ViewContainerRef,
        public notificationService: NotificationService,
        public authService: AuthService,
        public loadingController: LoadingController,
        public alertController: AlertController,
    ) { }

    ngOnInit(): void {
        this.menu.enable(true, 'left-menu');
        this.menu.enable(true, 'right-menu');
    }

    ionViewWillEnter() {
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }
}
