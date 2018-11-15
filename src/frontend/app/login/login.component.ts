import { Component, AfterViewInit, OnInit, ViewChild, ElementRef } from '@angular/core';
import { trigger, transition, style, animate } from '@angular/animations';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry, MatSnackBar } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { CookieService } from 'ngx-cookie-service';

@Component({
    templateUrl: 'login.component.html',
    styleUrls: ['login.component.styl'],
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

    loadingForm = true;
    newLogin = {
        mail: '',
        password: ''
    };
    labelButton = 'Se connecter';
    loadingConnexion = false;

    constructor(public http: HttpClient, private cookieService: CookieService, private router: Router, iconReg: MatIconRegistry, sanitizer: DomSanitizer, public snackBar: MatSnackBar, public signaturesService: SignaturesContentService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/assets/logo_white.svg'));
    }

    ngOnInit(): void {
        this.signaturesService.userLogged = {};
        this.cookieService.deleteAll();
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
                const cookieInfo = JSON.parse(atob(this.cookieService.get('maarchParapheurAuth')));
                this.signaturesService.userLogged = cookieInfo;
                this.router.navigate(['/document/']);
            }, (err: any) => {
                this.labelButton = 'Se connecter';
                this.loadingConnexion = false;
                this.snackBar.open(err.error.errors, null,
                    {
                        duration: 3000,
                        panelClass: 'center-snackbar',
                        verticalPosition: 'top'
                    }
                );
            });
    }
}
