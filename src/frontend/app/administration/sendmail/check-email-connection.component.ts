import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';
import { AlertController } from '@ionic/angular';
import { NotificationService } from '../../service/notification.service';
import { finalize, tap } from 'rxjs/operators';

@Component({
    selector: 'app-check-email-connection',
    templateUrl: 'check-email-connection.component.html',
    styleUrls: ['check-email-connection.component.scss'],
})

export class CheckEmailConnectionComponent implements OnInit {

    @Input() profileInfo: any;
    @Input() sendmailFrom: any;
    @Input() recipientTest: any;

    emailSendLoading: boolean = false;
    emailSendResult = {
        icon: '',
        msg: '',
        debug: '',
        error: false
    };

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        public dialog: MatDialog,
        public notificationService: NotificationService,
        public alertController: AlertController
    ) { }

    ngOnInit(): void {
    }


    testEmailSend() {
        this.emailSendResult = {
            icon: 'paper-plane-outline',
            msg: 'lang.emailSendInProgress',
            debug: '',
            error: false
        };
        const email = {
            'sender': this.sendmailFrom,
            'recipients': [this.recipientTest],
            'subject': '[' + this.translate.instant('lang.doNotReply') + '] ' + this.translate.instant('lang.emailSendTest'),
            'status': 'EXPRESS',
            'body': this.translate.instant('lang.emailSendTest'),
            'isHtml': false
        };
        this.emailSendLoading = true;

        this.http.post('../rest/emails', email)
            .pipe(
                tap((data: any) => {
                    if (data.isSent) {
                        this.emailSendResult = {
                            icon: 'checkmark-outline',
                            msg: 'lang.emailSendSuccess',
                            debug: '',
                            error: false
                        };
                    } else {
                        this.emailSendResult = {
                            icon: 'close-outline',
                            msg: 'lang.emailSendFailed',
                            debug: data.informations,
                            error: true
                        };
                    }
                }),
                finalize(() => this.emailSendLoading = false)
            ).subscribe();
    }
}
