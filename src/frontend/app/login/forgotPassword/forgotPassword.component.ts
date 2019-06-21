import { Component, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { NotificationService } from '../../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { finalize } from 'rxjs/operators';

@Component({
    templateUrl: 'forgotPassword.component.html',
    styleUrls: ['forgotPassword.component.scss'],
})
export class ForgotPasswordComponent implements OnInit {

    loadingForm: boolean = false;
    loading: boolean = false;
    newLogin: any = {
        login: '',
        password: ''
    };
    labelButton: string = 'lang.send';
    appVersion: string = '';
    appAuthor: string = '';

    constructor(private translate: TranslateService, private router: Router, public http: HttpClient, iconReg: MatIconRegistry, sanitizer: DomSanitizer, public notificationService: NotificationService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));
    }

    ngOnInit(): void { }

    generateLink() {
        this.labelButton = 'lang.generation';
        this.loading = true;

        this.http.post('../rest/password', { 'login': this.newLogin.login })
            .pipe(
                finalize(() => {
                    this.labelButton = 'lang.send';
                    this.loading = false;
                })
            )
            .subscribe((data: any) => {
                this.loadingForm = true;
                this.notificationService.success('lang.requestSentByEmail');
                this.router.navigate(['/login']);
            }, (err: any) => {
                this.notificationService.handleErrors(err);
            });
    }
}
