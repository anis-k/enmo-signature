import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../../service/signatures.service';
import { NotificationService } from '../../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog, MatSidenav } from '@angular/material';
import { map, finalize } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { ConfirmComponent } from '../../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../../../service/auth.service';


export interface Ldap {
    id: number;
    label: string;
    identifier: string;
    value: {
        uri: string,
        ssl: boolean,
        prefix: string,
        suffix: string,
        baseDN: string,
    };
}

@Component({
    selector: 'app-administration-ldap',
    templateUrl: 'ldap.component.html',
    styleUrls: ['../../administration.scss', 'ldap.component.scss'],
})

export class LdapComponent implements OnInit {

    creationMode: boolean = true;
    loading: boolean = true;
    loadingTest: boolean = false;
    ldapTest: any = {
        login: '',
        password: '',
        result: ''
    };
    ldap: Ldap;
    ldapClone: Ldap;
    title: string = '';

    // tslint:disable-next-line:no-input-rename
    @ViewChild('snavRight') snavRight: MatSidenav;

    constructor(public http: HttpClient, private translate: TranslateService, private route: ActivatedRoute, private router: Router, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog, public authService: AuthService) {
    }

    ngOnInit(): void {
        this.ldapTest.login = this.authService.user.login;

        this.route.params.subscribe((params: any) => {
            if (params['id'] === undefined) {
                this.creationMode = true;
                this.title = this.translate.instant('lang.ldapCreation');
                this.ldap = {
                    id: 0,
                    label: '',
                    identifier: 'ldapServer',
                    value: {
                        uri: '',
                        ssl: false,
                        prefix: '',
                        suffix: '',
                        baseDN: '',
                    }
                };
                this.loading = false;
            } else {
                this.creationMode = false;
                this.http.get('../rest/configurations/' + params['id'])
                    .pipe(
                        map((data: any) => data.configuration),
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: (data: any) => {
                            this.ldap = data;
                            this.ldapClone = JSON.parse(JSON.stringify(this.ldap));
                            this.title = this.ldap.label;
                        },
                    });
            }
        });
    }

    canValidate() {
        if (JSON.stringify(this.ldap) === JSON.stringify(this.ldapClone)) {
            return false;
        } else {
            return true;
        }
    }

    onSubmit() {
        if (this.creationMode) {
            this.createLdap();
        } else {
            this.modifyLdap();
        }
    }

    modifyLdap() {
        this.loading = true;
        this.http.patch('../rest/configurations/' + this.ldap.id, this.ldap)
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    this.router.navigate(['/administration/connections/ldaps']);
                    this.notificationService.success('lang.ldapUpdated');
                },
            });
    }

    createLdap() {
        this.loading = true;
        this.http.post('../rest/configurations', this.ldap)
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    this.router.navigate(['/administration/connections/ldaps']);
                    this.notificationService.success('lang.ldapAdded');
                },
            });
    }

    delete() {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: '', title: 'lang.confirmMsg', msg: '' } });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
                this.http.delete('../rest/configurations/' + this.ldap.id)
                    .pipe(
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: () => {
                            this.router.navigate(['/administration/connections/ldaps']);
                            this.notificationService.success('lang.ldapDeleted');

                        },
                    });
            }
        });
    }

    cancel() {
        this.router.navigate(['/administration/connections/ldaps']);
    }

    testLdap() {
        this.loadingTest = true;
        this.ldapTest.result = '';
        if (this.canValidate()) {
            this.http.patch('../rest/configurations/' + this.ldap.id, this.ldap)
            .subscribe({
                next: () => {
                    this.ldapClone = JSON.parse(JSON.stringify(this.ldap));
                    this.notificationService.success('lang.ldapUpdated');
                    this.http.get('../rest/configurations/' + this.ldap.id + '/connection', {
                        params: {
                            login: this.ldapTest.login,
                            password: this.ldapTest.password
                        }
                    })
                        .pipe(
                            finalize(() => this.loadingTest = false)
                        )
                        .subscribe({
                            next: (data: any) => {
                                this.ldapTest.result = data.informations;
                                if (data.connection) {
                                    this.notificationService.success('lang.ldapConnectionSucceeded');
                                }
                            },
                        });
                },
            });
        } else {
            this.http.get('../rest/configurations/' + this.ldap.id + '/connection', {
                params: {
                    login: this.ldapTest.login,
                    password: this.ldapTest.password
                }
            })
                .pipe(
                    finalize(() => this.loadingTest = false)
                )
                .subscribe({
                    next: (data: any) => {
                        this.ldapTest.result = data.informations;
                        if (data.connection) {
                            this.notificationService.success('lang.ldapConnectionSucceeded');
                        }
                    },
                });
        }
    }
}
