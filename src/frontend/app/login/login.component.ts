import { Component, AfterViewInit, OnInit } from '@angular/core';
import { trigger, transition, style, animate } from '@angular/animations';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry, MatDialog } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { CookieService } from 'ngx-cookie-service';
import { NotificationService } from '../service/notification.service';
import { environment } from '../../core/environments/environment';
import { TranslateService } from '@ngx-translate/core';
import { Validators, FormControl } from '@angular/forms';
import { finalize } from 'rxjs/operators';
import { AlertComponent } from '../plugins/alert.component';

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

    loadingForm: boolean = true;
    loadingConnexion: boolean = false;
    newLogin: any = {
        login: '',
        password: ''
    };
    labelButton: string = 'lang.connect';
    appVersion: string = '';
    appAuthor: string = '';

    idMail = new FormControl('', [Validators.required]);
    password = new FormControl('', [Validators.required]);

    constructor(private translate: TranslateService, public http: HttpClient, private cookieService: CookieService, private router: Router, iconReg: MatIconRegistry, sanitizer: DomSanitizer, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));
        const myItem = localStorage.getItem('MaarchParapheur');
        if (myItem !== null) {
            this.router.navigate(['/documents']);
        }
    }

    ngOnInit(): void {
        this.appVersion = environment.VERSION;
        this.appAuthor = environment.AUTHOR;
        this.signaturesService.reset();
    }

    ngAfterViewInit(): void {
        setTimeout(() => {
            $('.maarchLogo').css({ 'transform': 'translateY(-200px)' });
        }, 200);
        setTimeout(() => {
            this.loadingForm = false;
            this.fixAutoFill();
        }, 500);
    }

    login() {
        this.labelButton = 'lang.connexion';
        this.loadingConnexion = true;

        this.http.post('../rest/log', { 'login': this.newLogin.login, 'password': this.newLogin.password }, { observe: 'response' })
            .pipe(
                finalize(() => {
                    this.labelButton = 'lang.connect';
                    this.loadingConnexion = false;
                })
            )
            .subscribe({
                next: (data: any) => {
                    localStorage.setItem('MaarchParapheur', data.headers.get('Token'));
                    this.signaturesService.userLogged = data.body.user;
                    this.http.get('../rest/users/' + this.signaturesService.userLogged.id + '/signatures')
                        .subscribe((dataSign: any) => {
                            this.signaturesService.signaturesList = dataSign.signatures;
                        });
                    this.translate.use(this.signaturesService.userLogged.preferences.lang);
                    this.cookieService.set('maarchParapheurLang', this.signaturesService.userLogged.preferences.lang);

                    this.loadingForm = true;
                    $('.maarchLogo').css({ 'transform': 'translateY(0px)' });
                    setTimeout(() => {
                        this.router.navigate(['/documents']);
                    }, 700);
                },
                error: err => {
                    if (err.status === 401) {
                        this.notificationService.error('lang.wrongLoginPassword');
                    } else {
                        this.notificationService.handleErrors(err);
                    }
                }
            });
    }

    fixAutoFill() {
        setTimeout(() => {
            this.newLogin.login = $('#login').val();
            this.newLogin.password = $('#password').val();
        }, 100);
    }
}
