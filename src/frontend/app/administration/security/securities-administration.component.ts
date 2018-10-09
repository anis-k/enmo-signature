import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "securities-administration.component.html",
    providers: [NotificationService]
})
export class SecuritiesAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl     : string;
    lang        : any = LANG;
    loading     : boolean = false;

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
    passwordRulesClone : any = {};

    passwordRulesList : any[] = [];


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.securitiesAdministration);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;

        this.http.get(this.coreUrl + 'rest/passwordRules')
            .subscribe((data: any) => {
                this.passwordRulesList = data.rules;

                data.rules.forEach((rule: any) => {
                    this.passwordRules[rule.label].enabled = rule.enabled;
                    this.passwordRules[rule.label].value = rule.value;
                    this.passwordRules[rule.label].label = this.lang['password'+rule.label+'Required'];
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
        if (JSON.stringify(this.passwordRules) === JSON.stringify(this.passwordRulesClone)) {
            return true 
        } else {
           return false;
        }
    }

    disabledForm() {
        if (!this.passwordRules['lockTime'].enabled && !this.passwordRules['minLength'].enabled && !this.passwordRules['lockAttempts'].enabled && !this.passwordRules['renewal'].enabled && !this.passwordRules['historyLastUse'].enabled) {
            return true;
        } else {
            false;
        }
    }

    toggleRule(rule:any) {
        rule.enabled = !rule.enabled;
        this.passwordRulesList.forEach((rule2: any) => {
            if (rule.id == 'lockAttempts' && (rule2.label == 'lockTime' || rule2.label == 'lockAttempts')) {
                rule2.enabled = rule.enabled
                this.passwordRules['lockTime'].enabled = rule.enabled;
            } else if (rule.id == rule2.label) {
                rule2.enabled = rule.enabled
            }
        });
    }

    onSubmit() { 
        this.passwordRulesList.forEach((rule: any) => {
            rule.enabled = this.passwordRules[rule.label].enabled;
            rule.value = this.passwordRules[rule.label].value;
        });
        this.http.put(this.coreUrl + "rest/passwordRules", {rules:this.passwordRulesList})
            .subscribe((data: any) => {
                this.passwordRulesClone = JSON.parse(JSON.stringify(this.passwordRules));
                this.notify.success(this.lang.passwordRulesUpdated);
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }
}
