import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { MatDialog } from '@angular/material';
import { map, tap, finalize } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { ConfirmComponent } from '../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../../service/auth.service';


export interface User {
    id: string;
    firstname: string;
    lastname: string;
    login: string;
    email: string;
    picture: string;
    isRest: boolean;
}

@Component({
    selector: 'app-administration-user',
    templateUrl: 'user.component.html',
    styleUrls: ['../administration.scss', 'user.component.scss'],
})

export class UserComponent implements OnInit {

    creationMode: boolean = true;
    loading: boolean = true;
    user: User;
    userClone: User;
    title: string = '';
    hideCurrentPassword: Boolean = true;
    hideNewPassword: Boolean = true;
    hideNewPasswordConfirm: Boolean = true;

    // HANDLE PASSWORD
    passwordRules: any = {
        minLength: { enabled: false, value: 0 },
        complexityUpper: { enabled: false, value: 0 },
        complexityNumber: { enabled: false, value: 0 },
        complexitySpecial: { enabled: false, value: 0 },
        renewal: { enabled: false, value: 0 },
        historyLastUse: { enabled: false, value: 0 },
    };
    passwordRest: any = {
        newPassword: '',
        passwordConfirmation: ''
    };

    ruleText = '';
    otherRuleText = '';

    showPassword = false;
    handlePassword: any = {
        error: false,
        errorMsg: ''
    };

    constructor(public http: HttpClient, private translate: TranslateService, private route: ActivatedRoute, private router: Router, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog, public authService: AuthService) {
    }

    ngOnInit(): void {
        this.route.params.subscribe((params: any) => {
            if (params['id'] === undefined) {
                this.creationMode = true;
                this.title = this.translate.instant('lang.userCreation');
                this.user = {
                    id: '',
                    firstname: '',
                    lastname: '',
                    login: '',
                    email: '',
                    picture: '',
                    isRest: false
                };
                this.loading = false;
            } else {
                this.creationMode = false;
                this.http.get('../rest/users/' + params['id'])
                    .pipe(
                        map((data: any) => data.user),
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: data => {
                            this.user = data;
                            this.userClone = JSON.parse(JSON.stringify(this.user));
                            this.title = this.user.firstname + ' ' + this.user.lastname;
                            if (this.user.isRest) {
                                this.getPassRules({ checked: true });
                            }
                        },
                    });
            }
        });
    }

    canValidate() {
        if (this.user.isRest && this.passwordRest.newPassword !== '' && (this.handlePassword.error || this.passwordRest.passwordConfirmation !== this.passwordRest.newPassword)) {
            return false;
        } else if (JSON.stringify(this.user) === JSON.stringify(this.userClone) && this.passwordRest.newPassword === '') {
            return false;
        } else {
            return true;
        }
    }

    onSubmit() {
        if (this.creationMode) {
            this.createUser();
        } else {
            this.modifyUser();
        }
    }

    modifyUser() {
        this.loading = true;
        this.http.put('../rest/users/' + this.user.id, this.user)
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    if (this.authService.user.id === this.user.id) {
                        this.authService.updateUserInfoWithTokenRefresh();
                    }
                    if (this.passwordRest.newPassword !== '') {
                        this.updateRestUser();
                    }
                    this.router.navigate(['/administration/users']);
                    this.notificationService.success('lang.userUpdated');
                },
            });
    }

    updateRestUser() {
        const headers = new HttpHeaders({
            'Authorization': 'Bearer ' + this.authService.getToken()
        });
        this.http.put('../rest/users/' + this.user.id + '/password', this.passwordRest, { headers: headers })
            .subscribe(() => {
                this.passwordRest.newPassword = '';
                this.passwordRest.passwordConfirmation = '';
            }, (err) => {
                this.notificationService.handleErrors(err);
            });
    }

    createUser() {
        this.loading = true;
        this.http.post('../rest/users', this.user)
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: (data: any) => {
                    if (this.user.isRest) {
                        this.user.id = data.id;
                        this.updateRestUser();
                    }
                    this.router.navigate(['/administration/users']);
                    this.notificationService.success('lang.userAdded');
                },
            });
    }

    delete() {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: '', title: 'lang.confirmMsg', msg: '' } });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
                this.http.delete('../rest/users/' + this.user.id)
                    .pipe(
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: () => {
                            this.router.navigate(['/administration/users']);
                            this.notificationService.success('lang.userDeleted');
                        },
                    });
            }
        });
    }

    cancel() {
        this.router.navigate(['/administration/users']);
    }

    getPassRules(ev: any) {
        if (ev.checked) {
            this.handlePassword.error = false;
            this.handlePassword.errorMsg = '';

            this.http.get('../rest/passwordRules')
                .subscribe((data: any) => {
                    const ruleTextArr: String[] = [];
                    const otherRuleTextArr: String[] = [];

                    data.rules.forEach((rule: any) => {
                        if (rule.label === 'minLength') {
                            this.passwordRules.minLength.enabled = rule.enabled;
                            this.passwordRules.minLength.value = rule.value;
                            if (rule.enabled) {
                                this.translate.get('lang.minLengthChar', { charLength: rule.value }).subscribe((res: string) => {
                                    ruleTextArr.push(res);
                                });
                            }

                        } else if (rule.label === 'complexityUpper') {
                            this.passwordRules.complexityUpper.enabled = rule.enabled;
                            this.passwordRules.complexityUpper.value = rule.value;
                            if (rule.enabled) {
                                ruleTextArr.push('lang.upperRequired');
                            }

                        } else if (rule.label === 'complexityNumber') {
                            this.passwordRules.complexityNumber.enabled = rule.enabled;
                            this.passwordRules.complexityNumber.value = rule.value;
                            if (rule.enabled) {
                                ruleTextArr.push('lang.numberRequired');
                            }

                        } else if (rule.label === 'complexitySpecial') {
                            this.passwordRules.complexitySpecial.enabled = rule.enabled;
                            this.passwordRules.complexitySpecial.value = rule.value;
                            if (rule.enabled) {
                                ruleTextArr.push('lang.specialCharRequired');
                            }
                        } else if (rule.label === 'renewal') {
                            this.passwordRules.renewal.enabled = rule.enabled;
                            this.passwordRules.renewal.value = rule.value;
                            if (rule.enabled) {
                                this.translate.get('lang.renewalInfo', { time: rule.value }).subscribe((res: string) => {
                                    otherRuleTextArr.push(res);
                                });
                            }
                        } else if (rule.label === 'historyLastUse') {
                            this.passwordRules.historyLastUse.enabled = rule.enabled;
                            this.passwordRules.historyLastUse.value = rule.value;
                            if (rule.enabled) {
                                this.translate.get('lang.historyUseInfo', { countPwd: rule.value }).subscribe((res: string) => {
                                    otherRuleTextArr.push(res);
                                });
                            }
                        }

                    });
                    this.ruleText = ruleTextArr.join(', ');
                    this.otherRuleText = otherRuleTextArr.join('<br/>');
                }, (err) => {
                    this.notificationService.handleErrors(err);
                });
        }
    }

    checkPasswordValidity(password: string) {
        this.handlePassword.error = true;

        if (!password.match(/[A-Z]/g) && this.passwordRules.complexityUpper.enabled) {
            this.handlePassword.errorMsg = 'lang.upperRequired';
        } else if (!password.match(/[0-9]/g) && this.passwordRules.complexityNumber.enabled) {
            this.handlePassword.errorMsg = 'lang.numberRequired';
        } else if (!password.match(/[^A-Za-z0-9]/g) && this.passwordRules.complexitySpecial.enabled) {
            this.handlePassword.errorMsg = 'lang.specialCharRequired';
        } else if (password.length < this.passwordRules.minLength.value && this.passwordRules.minLength.enabled) {
            this.translate.get('lang.minLengthChar', { charLength: this.passwordRules.minLength.value }).subscribe((res: string) => {
                this.handlePassword.errorMsg = res;
            });
        } else {
            this.handlePassword.error = false;
            this.handlePassword.errorMsg = '';
        }
    }
}
