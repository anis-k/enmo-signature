import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';
import { LatinisePipe } from 'ngx-pipes';
import { AlertController } from '@ionic/angular';
import { NotificationService } from '../../../service/notification.service';
import { catchError, exhaustMap, finalize, tap } from 'rxjs/operators';
import { of } from 'rxjs';


export interface User {
    id: string;
    firstname: string;
    lastname: string;
    login: string;
    email: string;
    subtitute: boolean;
}

@Component({
    selector: 'app-check-connection',
    templateUrl: 'check-connection.component.html',
    styleUrls: ['check-connection.component.scss'],
})

export class CheckConnectionComponent implements OnInit {

    @Input() ldapTest: any;
    @Input() ldap: any;
    @Input() canValidate: boolean;

    loadingTest: boolean = false;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        private latinisePipe: LatinisePipe,
        public dialog: MatDialog,
        public notificationService: NotificationService,
        public alertController: AlertController
    ) {

    }

    ngOnInit(): void {
    }


    testLdap() {
        this.loadingTest = true;
        this.ldapTest.result = '';
        if (this.canValidate) {
            this.http.patch('../rest/configurations/' + this.ldap.id, this.ldap).pipe(
                tap(() => {
                    this.notificationService.success('lang.ldapUpdated');
                }),
                exhaustMap(() => this.http.get('../rest/configurations/' + this.ldap.id + '/connection', {
                    params: {
                        login: this.ldapTest.login,
                        password: this.ldapTest.password
                    }
                })),
                tap((data: any) => {
                    this.ldapTest.result = data.informations;
                    if (data.connection) {
                        this.notificationService.success('lang.ldapConnectionSucceeded');
                    }
                }),
                finalize(() => this.loadingTest = false),
                catchError((err: any) => {
                    return of(false);
                })
            ).subscribe();
        } else {
            this.http.get('../rest/configurations/' + this.ldap.id + '/connection', {
                params: {
                    login: this.ldapTest.login,
                    password: this.ldapTest.password
                }
            }).pipe(
                tap((data: any) => {
                    this.ldapTest.result = data.informations;
                    if (data.connection) {
                        this.notificationService.success('lang.ldapConnectionSucceeded');
                    }
                }),
                finalize(() => this.loadingTest = false),
                catchError((err: any) => {
                    return of(false);
                })
            ).subscribe();
        }
    }
}
