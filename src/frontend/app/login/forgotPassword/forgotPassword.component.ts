import { Component, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { NotificationService } from '../../service/notification.service';

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
    labelButton: string = 'Envoyer';
    appVersion: string = '';
    appAuthor: string = '';

    constructor(private router: Router, public http: HttpClient, iconReg: MatIconRegistry, sanitizer: DomSanitizer, public notificationService: NotificationService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));
    }

    ngOnInit(): void { }

    generateLink() {
        this.labelButton = 'Génération ...';
        this.loading = true;

        this.http.post('../rest/password', { 'login': this.newLogin.login })
            .subscribe((data: any) => {
                this.loadingForm = true;
                this.notificationService.success('La demande vous a été envoyé par mail.');
                this.router.navigate(['/login']);
            }, (err: any) => {
                this.notificationService.handleErrors(err);

                this.labelButton = 'Envoyer';
                this.loading = false;
            });
    }
}
