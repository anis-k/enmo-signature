import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { map, finalize } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../../service/auth.service';
import { CheckEmailConnectionComponent } from './check-email-connection.component';
import { ModalController } from '@ionic/angular';


export interface Sendmail {

    auth: boolean;
    from: string;
    host: string;
    port: number;
    type: string;
    user: string;
    secure: string;
    charset: string;
    password: string;
    passwordAlreadyExists: boolean;
}

@Component({
    selector: 'app-administration-sendmail',
    templateUrl: 'sendmail.component.html',
    styleUrls: ['sendmail.component.scss'],
})

export class SendmailComponent implements OnInit {

    loading: boolean = true;
    sendmail: Sendmail;
    sendmailClone: Sendmail;
    title: string = '';
    passwordLanguage: string = '';
    hidePassword: boolean = true;
    sendmailLabel: string;

    smtpTypeList = [
        {
            id: 'smtp',
            label: 'lang.smtpclient'
        },
        {
            id: 'sendmail',
            label: 'lang.smtprelay'
        },
        {
            id: 'qmail',
            label: 'lang.qmail'
        },
        {
            id: 'mail',
            label: 'lang.phpmail'
        }
    ];
    smtpSecList = [
        {
            id: '',
            label: 'lang.none'
        },
        {
            id: 'ssl',
            label: 'ssl'
        },
        {
            id: 'tls',
            label: 'tls'
        }
    ];

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        private route: ActivatedRoute,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public dialog: MatDialog,
        public authService: AuthService,
        public modalController: ModalController
    ) {
    }

    ngOnInit(): void {
        this.route.params.subscribe(() => {
            this.http.get('../rest/configurations/1')
                .pipe(
                    map((data: any) => data.configuration),
                    finalize(() => this.loading = false)
                )
                .subscribe({
                    next: data => {
                        this.sendmail = data.value;
                        this.sendmailLabel = data.label;
                        this.sendmailClone = JSON.parse(JSON.stringify(this.sendmail));
                        this.title = this.translate.instant('lang.manage_email_configuration');
                        if (this.sendmail.passwordAlreadyExists) {
                            this.passwordLanguage = this.translate.instant('lang.passwordModification');
                        } else {
                            this.passwordLanguage = this.translate.instant('lang.password');
                        }
                    },
                });
        });
    }

    canValidate() {
        return JSON.stringify(this.sendmail) !== JSON.stringify(this.sendmailClone);
    }

    onSubmit() {
        this.loading = true;
        this.http.patch('../rest/configurations/1', { 'value': this.sendmail, 'label': this.sendmailLabel })
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    this.sendmailClone = JSON.parse(JSON.stringify(this.sendmail));
                    this.notificationService.success('lang.emailConfigurationUpdated');
                },
            });
    }

    cleanAuthInfo() {
        this.sendmail.passwordAlreadyExists = false;
        this.sendmail.user = '';
        this.sendmail.password = '';
    }

    async checkConnection(ev: any) {
        const profileInfo = JSON.parse(JSON.stringify(this.authService.user));
        if (JSON.stringify(this.sendmailClone) !== JSON.stringify(this.sendmail)) {
            this.onSubmit();
        }
        const modal = await this.modalController.create({
            component: CheckEmailConnectionComponent,
            componentProps: {
                'sendmailFrom': this.sendmail.from,
                'recipientTest': profileInfo.email
            }
        });
        await modal.present();
    }
}
