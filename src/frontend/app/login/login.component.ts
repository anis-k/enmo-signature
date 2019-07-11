import { Component, AfterViewInit, OnInit } from '@angular/core';
import { trigger, transition, style, animate } from '@angular/animations';
import { MatDialog } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { environment } from '../../core/environments/environment';
import { Validators, FormGroup, FormBuilder } from '@angular/forms';
import { AuthService } from '../service/auth.service';
import { tap, catchError } from 'rxjs/operators';
import {EMPTY} from 'rxjs';

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

    loginForm   : FormGroup;

    loading     : boolean = false;
    showForm    : boolean = false;
    environment : any;


    constructor(private http: HttpClient, private router: Router, public authService: AuthService, private signaturesService: SignaturesContentService, private notificationService: NotificationService, public dialog: MatDialog, private formBuilder: FormBuilder) {
    }

    ngOnInit(): void {
        if (this.authService.isAuth) {
            this.router.navigate(['/documents']);
        }

        this.loginForm = this.formBuilder.group({
            login:      [ null, Validators.required ],
            password:   [ null, Validators.required ]
        });

        this.environment = environment;
        this.signaturesService.reset();
    }

    ngAfterViewInit(): void {
        setTimeout(() => {
            $('.maarchLogo').css({ 'transform': 'translateY(-200px)' });
        }, 200);
        setTimeout(() => {
            this.showForm = true;
            this.fixAutoFill();
        }, 500);
    }

    fixAutoFill() {
        setTimeout(() => {
            this.loginForm.get('login').setValue($('#login').val());
            this.loginForm.get('password').setValue($('#password').val());
        }, 100);
    }

    onSubmit() {
        this.loading = true;
        this.http.post('../rest/authenticate', { 'login': this.loginForm.get('login').value, 'password': this.loginForm.get('password').value }, { observe: 'response' })
            .pipe(
                tap((data: any) => {
                    this.loading = false;
                    this.showForm = false;
                    this.authService.saveTokens(data.headers.get('Token'), data.headers.get('Refresh-Token'));
                    this.authService.setUser({});
                    $('.maarchLogo').css({ 'transform': 'translateY(0px)' });
                    setTimeout(() => {
                        this.router.navigate(['/documents']);
                    }, 700);
                }),
                catchError((err: any) => {
                    this.loading = false;
                    if (err.status === 401) {
                        this.notificationService.error('lang.wrongLoginPassword');
                    } else {
                        this.notificationService.handleErrors(err);
                    }

                    return EMPTY;
                })
            )
            .subscribe();
    }
}
