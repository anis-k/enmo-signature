import { Component, AfterViewInit, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { environment } from '../../core/environments/environment';
import { Validators, FormGroup, FormBuilder } from '@angular/forms';
import { AuthService } from '../service/auth.service';
import { tap, catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { LoadingController, MenuController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';

@Component({
    templateUrl: 'login.component.html',
    styleUrls: ['login.component.scss'],
})
export class LoginComponent implements OnInit, AfterViewInit {

    loginForm: FormGroup;

    loading: boolean = false;
    showForm: boolean = false;
    environment: any;


    constructor(
        private http: HttpClient,
        private router: Router,
        public authService: AuthService,
        private signaturesService: SignaturesContentService,
        private notificationService: NotificationService,
        public dialog: MatDialog,
        private formBuilder: FormBuilder,
        public loadingController: LoadingController,
        private translate: TranslateService,
        private menu: MenuController,
    ) { }

    ngOnInit(): void {

        this.loginForm = this.formBuilder.group({
            login: [null, Validators.required],
            password: [null, Validators.required]
        });

        this.environment = environment;
        this.signaturesService.reset();
    }

    ionViewWillEnter() {
        this.menu.enable(false, 'left-menu');
        this.menu.enable(false, 'right-menu');
    }

    ngAfterViewInit(): void {
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

    async onSubmit() {
        const loading = await this.loadingController.create({
            cssClass: 'my-custom-class',
            message: this.translate.instant('lang.connexion'),
          });
        await loading.present();
        this.http.post('../rest/authenticate', { 'login': this.loginForm.get('login').value, 'password': this.loginForm.get('password').value }, { observe: 'response' })
            .pipe(
                tap((data: any) => {
                    this.loading = false;
                    this.showForm = false;
                    this.authService.saveTokens(data.headers.get('Token'), data.headers.get('Refresh-Token'));
                    this.authService.setUser({});

                    this.router.navigate(['/home']);
                    loading.dismiss();
                }),
                catchError((err: any) => {
                    this.loading = false;
                    if (err.status === 401) {
                        this.notificationService.error('lang.wrongLoginPassword');
                        loading.dismiss();
                    } else {
                        this.notificationService.handleErrors(err);

                    }

                    return of(false);
                })
            )
            .subscribe();
    }
}
