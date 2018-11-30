import { Component, AfterViewInit, OnInit } from '@angular/core';
import { trigger, transition, style, animate } from '@angular/animations';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { CookieService } from 'ngx-cookie-service';
import { NotificationService } from '../service/notification.service';
import { environment } from '../../core/environments/environment';

@Component({
    templateUrl: 'login.component.html',
    styleUrls: ['login.component.scss'],
    animations: [
        trigger(
            'myAnimation',
            [
                transition(
                    ':enter', [
                        style({ transform: 'translateX(100%)', opacity: 0 }),
                        animate('500ms', style({ transform: 'translateX(0)', 'opacity': 1 }))
                    ]
                ),
                transition(
                    ':leave', [
                        style({ transform: 'translateX(0)', 'opacity': 1 }),
                        animate('500ms', style({ transform: 'translateX(100%)', 'opacity': 0 })),
                    ]
                )]
        )
    ],
})
export class LoginComponent implements OnInit, AfterViewInit {

    loadingForm         : boolean   = true;
    loadingConnexion    : boolean   = false;
    newLogin            : any       = {
        mail: '',
        password: ''
    };
    labelButton         : string    = 'Se connecter';
    appVersion          : string    = '';
    appAuthor           : string    = '';

    constructor(public http: HttpClient, private cookieService: CookieService, private router: Router, iconReg: MatIconRegistry, sanitizer: DomSanitizer, public signaturesService: SignaturesContentService, public notificationService: NotificationService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));
        if (this.cookieService.check('maarchParapheurAuth')) {
            this.router.navigate(['/documents']);
        }
    }

    ngOnInit(): void {
        this.appVersion = environment.VERSION;
        this.appAuthor = environment.AUTHOR;
        this.signaturesService.userLogged = {};
    }

    ngAfterViewInit(): void {
        setTimeout(() => {
            $('.maarchLogo').css({ 'transform': 'translateY(-200px)' });
        }, 200);
        setTimeout(() => {
            this.loadingForm = false;
        }, 500);
    }

    login() {
        this.labelButton = 'Connexion ...';
        this.loadingConnexion = true;

        this.http.post('../rest/log', { 'email': this.newLogin.mail, 'password': this.newLogin.password })
            .subscribe((data: any) => {
                this.signaturesService.userLogged = data.user;
                this.loadingForm = true;
                $('.maarchLogo').css({ 'transform': 'translateY(0px)' });
                setTimeout(() => {
                    this.router.navigate(['/documents']);
                }, 700);
            }, (err: any) => {
                if (err.status === 401) {
                    this.notificationService.error('Mauvais courriel ou mauvais mot de passe');
                } else {
                    this.notificationService.handleErrors(err);
                }
                this.labelButton = 'Se connecter';
                this.loadingConnexion = false;
            });
    }
}
