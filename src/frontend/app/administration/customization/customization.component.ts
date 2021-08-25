import { Component, OnInit, ViewChild } from '@angular/core';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { tap, catchError } from 'rxjs/operators';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../../service/auth.service';
import { of } from 'rxjs';
import { NgForm } from '@angular/forms';

@Component({
    templateUrl: 'customization.component.html',
    styleUrls: ['../administration.scss', 'customization.component.scss'],
})

export class CustomizationComponent implements OnInit {

    @ViewChild('customizationForm', { static: false }) customizationForm: NgForm;

    loading: boolean = true;
    loginMessage: string = '';
    applicationUrl: string = '';

    watermark = {
        enabled: false,
        text: 'Document n°[id]',
        align: 'R',
        posY: 10
    };

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        public notificationService: NotificationService,
        public authService: AuthService
    ) { }

    async ngOnInit() {
        await this.getWatermark();
        this.loading = false;
    }

    getWatermark() {
        return new Promise((resolve) => {
            this.http.get('../rest/customization/watermark').pipe(
                tap((data: any) => {
                    if (!this.functions.empty(data.configuration)) {
                        this.watermark = data.configuration;
                    }
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    onSubmit() {
        this.http.put('../rest/customization', { id: this.authService.user.id, watermark: this.watermark }).pipe(
            tap(() => {
                this.notificationService.success(this.translate.instant('lang.modificationSaved'));
            }),
            catchError((err: any) => {
                this.notificationService.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
