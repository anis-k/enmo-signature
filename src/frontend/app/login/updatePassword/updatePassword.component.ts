import { Component, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { NotificationService } from '../../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { finalize } from 'rxjs/operators';

@Component({
    templateUrl: 'updatePassword.component.html',
    styleUrls: ['updatePassword.component.scss'],
})
export class UpdatePasswordComponent implements OnInit {

    loadingForm: boolean = false;
    loading: boolean = false;

    token: string = '';

    password: any = {
        newPassword: '',
        passwordConfirmation: ''
    };
    labelButton: string = 'lang.update';

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

    handlePassword: any = {
        error: false,
        errorMsg: ''
    };

    ruleText = '';
    otherRuleText = '';


    constructor(private translate: TranslateService, private router: Router, private route: ActivatedRoute, public http: HttpClient, iconReg: MatIconRegistry, sanitizer: DomSanitizer, public notificationService: NotificationService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));
        this.route.queryParams
            .subscribe(params => {
                this.token = params.token;
            });
    }

    ngOnInit(): void { }

    updatePassword() {
        this.labelButton = 'lang.sending';
        this.loading = true;

        this.http.put('../rest/password', { 'token': this.token, 'password': this.password.newPassword })
            .pipe(
                finalize(() => {
                    this.labelButton = 'lang.update';
                    this.loading = false;
                })
            )
            .subscribe((data: any) => {
                this.loadingForm = true;
                this.notificationService.success('lang.passwordChanged');
                this.router.navigate(['/login']);
            }, (err: any) => {
                this.notificationService.handleErrors(err);
            });
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

    getPassRules() {
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

    allowValidate() {
        if ((this.handlePassword.error || this.password.newPassword !== this.password.passwordConfirmation || this.password.newPassword.length === 0 || this.password.passwordConfirmation.length === 0)) {
            return true;
        } else {
            return false;
        }
    }
}
