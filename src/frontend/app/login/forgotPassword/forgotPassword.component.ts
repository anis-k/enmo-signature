import { Component, AfterViewInit, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { NotificationService } from '../../service/notification.service';
import { environment } from '../../../core/environments/environment';

@Component({
    templateUrl: 'forgotPassword.component.html',
    styleUrls: ['forgotPassword.component.scss'],
})
export class ForgotPasswordComponent implements OnInit, AfterViewInit {

    loadingForm: boolean = false;
    loadingConnexion: boolean = false;
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

    ngAfterViewInit(): void { }

    generateLink() {
        this.labelButton = 'Génération ...';
        this.loadingConnexion = true;

        this.http.post('../rest/password', { 'login': this.newLogin.login })
            .subscribe((data: any) => {
                this.loadingForm = true;
                this.notificationService.success('La demande vous a été envoyé par mail.');
                this.router.navigate(['/login']);
            }, (err: any) => {
                this.notificationService.handleErrors(err);

                this.labelButton = 'Envoyer';
                this.loadingConnexion = false;
            });
    }
}