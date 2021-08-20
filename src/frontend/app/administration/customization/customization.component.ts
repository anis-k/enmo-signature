import { Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { tap, catchError } from 'rxjs/operators';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../../service/auth.service';
import { of } from 'rxjs';
import { NgForm } from '@angular/forms';
import { FunctionsService } from '../../service/functions.service';

declare let tinymce: any;

@Component({
    templateUrl: 'customization.component.html',
    styleUrls: ['../administration.scss', 'customization.component.scss'],
})

export class CustomizationComponent implements OnInit, OnDestroy {

    @ViewChild('customizationForm', { static: false }) customizationForm: NgForm;

    loading: boolean = true;
    loginMessage: string = '';
    applicationUrl: string = '';

    watermark = {
        enabled: false,
        text: 'Document n°[id]',
        align: 'right',
        posX: 30,
        posY: 10
    };

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        public notificationService: NotificationService,
        public authService: AuthService,
        private functions: FunctionsService
    ) { }

    async ngOnInit(){
        await this.getCustomizationInfos();
        // await this.getWatermark();
        this.loading = false;
    }

    ngOnDestroy(): void {
        tinymce.remove();
    }

    getCustomizationInfos() {
        return new Promise((resolve) => {
            this.http.get('../rest/authenticationInformations').pipe(
                tap((data: any) => {
                    this.applicationUrl = data.applicationUrl;
                    this.loginMessage = data.loginMessage;
                    setTimeout(() => {
                        this.initMce();
                    }, 100);
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getWatermark() {
        return new Promise((resolve) => {
            this.http.get('../rest/customization/watermark').pipe(
                tap((data: any) => {
                    this.watermark = data.watermark;
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
        this.loginMessage = tinymce.get('login_message').getContent();
        this.http.put('../rest/customization', {id: this.authService.user.id , loginMessage: this.loginMessage, applicationUrl: this.applicationUrl, watermark: this.watermark}).pipe(
            tap(() => {
                this.notificationService.success(this.translate.instant('lang.modificationSaved'));
            }),
            catchError((err: any) => {
                this.notificationService.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initMce() {
        const param = {
            selector: '#login_message',
            base_url: this.functions.getBaseUrl() + '/tinymce/',
            height: '200',
            suffix: '.min',
            extended_valid_elements : 'tag,class',
            content_css: this.functions.getBaseUrl() + '/assets/custom_tinymce.css',
            language: this.translate.instant('lang.langISO').replace('-', '_'),
            language_url: `../node_modules/tinymce-i18n/langs/${this.translate.instant('lang.langISO').replace('-', '_')}.js`,
            menubar: false,
            statusbar: false,
            readonly: false,
            plugins: [
                'autolink', 'table', 'code', 'noneditable', 'link'
            ],
            noneditable_noneditable_class: 'mceNonEditable',
            table_toolbar: '',
            table_sizing_mode: 'relative',
            table_resize_bars: false,
            toolbar_sticky: true,
            toolbar_drawer: 'floating',
            table_style_by_css: true,
            content_style: 'table td { padding: 1px; vertical-align: top; }',
            forced_root_block : false,
            toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline strikethrough forecolor backcolor | table maarch_b64image | \
        alignleft aligncenter alignright alignjustify \
        bullist numlist outdent indent | removeformat | code link'
        };
        tinymce.init(param);
    }
}
