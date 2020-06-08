import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {TranslateService} from '@ngx-translate/core';
import {NotificationService} from '../../service/notification.service';
import {SignaturesContentService} from '../../service/signatures.service';

@Component({
    templateUrl: 'securities-administration.component.html',
    styleUrls: ['../administration.scss']
})
export class SecuritiesAdministrationComponent implements OnInit {

    loading: boolean = false;

    passwordRules: any = {
        minLength: { enabled: false, value: 0 },
        complexityUpper: { enabled: false, value: 0 },
        complexityNumber: { enabled: false, value: 0 },
        complexitySpecial: { enabled: false, value: 0 },
        renewal: { enabled: false, value: 0 },
        historyLastUse: { enabled: false, value: 0 },
        lockTime: { enabled: false, value: 0 },
        lockAttempts: { enabled: false, value: 0 },
    };
    passwordRulesClone: any = {};

    passwordRulesList: any[] = [];


    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        private notify: NotificationService,
        public signaturesService: SignaturesContentService
    ) { }

    ngOnInit(): void {
        this.loading = true;

        this.http.get('../rest/passwordRules')
            .subscribe((data: any) => {
                this.passwordRulesList = data.rules;

                data.rules.forEach((rule: any) => {
                    this.passwordRules[rule.label].enabled = rule.enabled;
                    this.passwordRules[rule.label].value = rule.value;
                    this.passwordRules[rule.label].label = this.translate.instant('lang.password_' + rule.label + 'Required');
                    this.passwordRules[rule.label].id = rule.label;

                    this.loading = false;
                });

                this.passwordRulesClone = JSON.parse(JSON.stringify(this.passwordRules));

            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    cancelModification() {
        this.passwordRules = JSON.parse(JSON.stringify(this.passwordRulesClone));
        this.passwordRulesList.forEach((rule: any) => {
            rule.enabled = this.passwordRules[rule.label].enabled;
            rule.value = this.passwordRules[rule.label].value;
        });
    }

    checkModif() {
        return JSON.stringify(this.passwordRules) === JSON.stringify(this.passwordRulesClone);
    }

    disabledForm() {
        return !this.passwordRules['lockTime'].enabled && !this.passwordRules['minLength'].enabled && !this.passwordRules['lockAttempts'].enabled && !this.passwordRules['renewal'].enabled && !this.passwordRules['historyLastUse'].enabled;
    }

    toggleRule(rule: any) {
        rule.enabled = !rule.enabled;
        this.passwordRulesList.forEach((rule2: any) => {
            if (rule.id === 'lockAttempts' && (rule2.label === 'lockTime' || rule2.label === 'lockAttempts')) {
                rule2.enabled = rule.enabled;
                this.passwordRules['lockTime'].enabled = rule.enabled;
            } else if (rule.id === rule2.label) {
                rule2.enabled = rule.enabled;
            }
        });
    }

    onSubmit() {
        this.passwordRulesList.forEach((rule: any) => {
            rule.enabled = this.passwordRules[rule.label].enabled;
            rule.value = this.passwordRules[rule.label].value;
        });
        this.http.put('../rest/passwordRules', { rules: this.passwordRulesList })
            .subscribe(() => {
                this.passwordRulesClone = JSON.parse(JSON.stringify(this.passwordRules));
                this.notify.success('lang.passwordRulesUpdated');
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }
}
