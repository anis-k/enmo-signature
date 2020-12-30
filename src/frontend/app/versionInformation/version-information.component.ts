import { Component, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { HttpClient } from '@angular/common/http';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { FiltersService } from '../service/filters.service';
import { tap, catchError } from 'rxjs/operators';
import { AuthService } from '../service/auth.service';
import { of } from 'rxjs';
import { ModalController } from '@ionic/angular';
import {environment} from '../../core/environments/environment';

@Component({
    selector: 'app-version-information',
    templateUrl: 'version-information.component.html',
})

export class VersionInformationComponent implements OnInit {

    commitHash: any = null;
    applicationVersion: any;

    constructor(private translate: TranslateService,
        public http: HttpClient,
        public sanitizer: DomSanitizer,
        public notificationService: NotificationService,
        public signaturesService: SignaturesContentService,
        public authService: AuthService,
        public filtersService: FiltersService,
        public modalController: ModalController
    ) { }

    async ngOnInit() {

        this.applicationVersion = environment.VERSION;

        await this.loadCommitInformation();
    }

    dismissModal() {
        this.modalController.dismiss('cancel');
    }

    loadCommitInformation() {
        return new Promise((resolve) => {
            this.http.get('../rest/commitInformation').pipe(
                tap((data: any) => {
                    this.commitHash = data.hash !== null ? data.hash : this.translate.instant('lang.undefined');
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }
}
