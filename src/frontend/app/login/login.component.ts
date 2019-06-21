import { Component, AfterViewInit, OnInit } from '@angular/core';
import { trigger, transition, style, animate } from '@angular/animations';
import { DomSanitizer } from '@angular/platform-browser';
import { MatDialog } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { environment } from '../../core/environments/environment';
import { Validators, FormControl } from '@angular/forms';
import { AuthService } from '../service/auth.service';

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

    newLogin: any = {
        login: '',
        password: ''
    };
    labelButton: string = 'lang.connect';
    appVersion: string = '';
    appAuthor: string = '';

    idMail = new FormControl('', [Validators.required]);
    password = new FormControl('', [Validators.required]);

    constructor(public http: HttpClient, private router: Router, sanitizer: DomSanitizer, public authService: AuthService, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog) {
        if (this.authService.isAuth) {
            this.router.navigate(['/documents']);
        }
    }

    ngOnInit(): void {
        this.authService.loadingForm = true;
        this.appVersion = environment.VERSION;
        this.appAuthor = environment.AUTHOR;
        this.signaturesService.reset();
    }

    ngAfterViewInit(): void {
        setTimeout(() => {
            $('.maarchLogo').css({ 'transform': 'translateY(-200px)' });
        }, 200);
        setTimeout(() => {
            this.authService.loadingForm = false;
            this.fixAutoFill();
        }, 500);
    }

    fixAutoFill() {
        setTimeout(() => {
            this.newLogin.login = $('#login').val();
            this.newLogin.password = $('#password').val();
        }, 100);
    }
}
