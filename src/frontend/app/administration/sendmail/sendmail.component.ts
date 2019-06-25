import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material';
import { map, finalize } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';


export interface Sendmail {
    auth: boolean,
    from: string,
    host: string,
    port: number,
    type: string,
    user: string,
    secure: string,
    charset: string,
    password: string,
    passwordAlreadyExists: boolean
}

@Component({
    selector: 'app-administration-sendmail',
    templateUrl: 'sendmail.component.html',
    styleUrls: ['../administration.scss', 'sendmail.component.scss'],
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
            id : '',
            label : 'lang.none'
        },
        {
            id : 'ssl',
            label : 'ssl'
        },
        {
            id : 'tls',
            label : 'tls'
        }
    ];

    constructor(public http: HttpClient, private translate: TranslateService, private route: ActivatedRoute, private router: Router, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog) {
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
        if (JSON.stringify(this.sendmail) === JSON.stringify(this.sendmailClone)) {
            return false;
        } else {
            return true;
        }
    }

    onSubmit() {
        this.loading = true;
        this.http.patch('../rest/configurations/1', {'value': this.sendmail, 'label': this.sendmailLabel})
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    this.router.navigate(['/administration']);
                    this.notificationService.success('lang.emailConfigurationUpdated');
                },
            });
    }

    cancel() {
        this.router.navigate(['/administration']);
    }
}
