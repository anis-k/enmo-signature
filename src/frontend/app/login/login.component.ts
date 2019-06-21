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
import { finalize } from 'rxjs/operators';

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

    constructor(public http: HttpClient, private router: Router, sanitizer: DomSanitizer, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog) { 
        const myItem = localStorage.getItem('MaarchParapheurToken');
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

        this.http.post('../rest/authenticate', { 'login': this.newLogin.login, 'password': this.newLogin.password }, { observe: 'response' })
            .pipe(
                finalize(() => {
                    this.labelButton = 'lang.connect';
                    this.loadingConnexion = false;
                })
            )
            .subscribe({
                next: (data: any) => {
                    localStorage.setItem('MaarchParapheurToken', data.headers.get('Token'));
                    localStorage.setItem('MaarchParapheurRefreshToken', data.headers.get('Refresh-Token'));

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
