import { ChangeDetectorRef, Component, OnInit, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { FormBuilder, FormGroup, Validators, ValidationErrors, AbstractControl, ValidatorFn } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialog } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "password-modification.component.html",
    providers: [NotificationService]
})
export class PasswordModificationComponent implements OnInit {

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;
    dialogRef                       : MatDialogRef<any>;
    config                          : any       = {};

    coreUrl         : string;
    lang            : any       = LANG;
    loading         : boolean   = false;

    user            : any       = {};
    ruleText        : string    = '';
    otherRuleText   : string;
    hidePassword    : boolean   = true;
    passLength      : any       = false;
    arrValidator    : any[]     = [];
    validPassword   : boolean   = false;
    matchPassword   : boolean   = false;
    isLinear        : boolean   = false;
    firstFormGroup  : FormGroup;

    passwordRules   : any = {
        minLength           : { enabled: false, value: 0 },
        complexityUpper     : { enabled: false, value: 0 },
        complexityNumber    : { enabled: false, value: 0 },
        complexitySpecial   : { enabled: false, value: 0 },
        renewal             : { enabled: false, value: 0 },
        historyLastUse      : { enabled: false, value: 0 },
    };

    passwordModel   : any = {
        currentPassword : "",
        newPassword     : "",
        reNewPassword   : "",
    };


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, private _formBuilder: FormBuilder, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
        this.user = angularGlobals.user;
    }

    prepare() {
        $j("link[href='merged_css.php']").remove();
        $j('#footer').remove();
        $j('#inner_content').remove();
        $j('#inner_content_contact').parent('div').remove();
        $j('#inner_content_contact').remove();
        $j('#menunav').hide();
        $j('#divList').remove();
        $j('#magicContactsTable').remove();
        $j('#manageBasketsOrderTable').remove();
        $j('#controlParamTechnicTable').remove();
        $j('#container').width("99%");
        if ($j('#content h1')[0] && $j('#content h1')[0] != $j('my-app h1')[0]) {
            $j('#content h1')[0].remove();
        }
    }

    ngOnInit(): void {
        this.prepare();
        setTimeout(() => {
            this.config = {data:{user:this.user,state:'BEGIN'},disableClose: true};
            this.dialogRef = this.dialog.open(InfoChangePasswordModalComponent, this.config);
        }, 0);

        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + 'rest/passwordRules')
            .subscribe((data: any) => {
                let valArr : ValidatorFn[] = [];
                let ruleTextArr: String[] = [];
                let otherRuleTextArr: String[] = [];

                valArr.push(Validators.required);
                
                data.rules.forEach((rule: any) => {
                    if (rule.label == 'minLength') {
                        this.passwordRules.minLength.enabled = rule.enabled;
                        this.passwordRules.minLength.value = rule.value;
                        if (rule.enabled) {
                            valArr.push(Validators.minLength(this.passwordRules.minLength.value));
                            ruleTextArr.push(rule.value + ' ' + this.lang['password' + rule.label]);
                        }
                    } else if (rule.label == 'complexityUpper') {
                        this.passwordRules.complexityUpper.enabled = rule.enabled;
                        this.passwordRules.complexityUpper.value = rule.value;
                        if (rule.enabled) {
                            valArr.push(this.regexValidator(new RegExp('[A-Z]'), { 'complexityUpper': '' }));
                            ruleTextArr.push(this.lang['password' + rule.label]);
                        }
                    } else if (rule.label == 'complexityNumber') {
                        this.passwordRules.complexityNumber.enabled = rule.enabled;
                        this.passwordRules.complexityNumber.value = rule.value;
                        if (rule.enabled) {
                            valArr.push(this.regexValidator(new RegExp('[0-9]'), { 'complexityNumber': '' }));
                            ruleTextArr.push(this.lang['password' + rule.label]);
                        }
                    } else if (rule.label == 'complexitySpecial') {
                        this.passwordRules.complexitySpecial.enabled = rule.enabled;
                        this.passwordRules.complexitySpecial.value = rule.value;
                        if (rule.enabled) {
                            valArr.push(this.regexValidator(new RegExp('[^A-Za-z0-9]'), { 'complexitySpecial': '' }));
                            ruleTextArr.push(this.lang['password' + rule.label]);
                        }
                    } else if (rule.label == 'renewal') {
                        this.passwordRules.renewal.enabled = rule.enabled;
                        this.passwordRules.renewal.value = rule.value;
                        if (rule.enabled) {
                            otherRuleTextArr.push(this.lang['password' + rule.label] + ' <b>' + rule.value + ' ' + this.lang.days + '</b>. ' + this.lang['password2' + rule.label]+'.');
                        }
                    } else if (rule.label == 'historyLastUse') {
                        this.passwordRules.historyLastUse.enabled = rule.enabled;
                        this.passwordRules.historyLastUse.value = rule.value
                        if (rule.enabled) {
                            otherRuleTextArr.push(this.lang['passwordhistoryLastUseDesc'] + ' <b>' + rule.value + '</b> ' + this.lang['passwordhistoryLastUseDesc2']+'.');
                        }
                    }
                });
                this.ruleText = ruleTextArr.join(', ');
                this.otherRuleText = otherRuleTextArr.join('<br/>');
                this.firstFormGroup.controls["newPasswordCtrl"].setValidators(valArr);
            }, (err) => {
                this.notify.error(err.error.errors);
            });

        this.firstFormGroup = this._formBuilder.group({
            newPasswordCtrl: [
                ''
            ],
            retypePasswordCtrl: [
                '',
                Validators.compose([Validators.required])
            ],
            currentPasswordCtrl: [
                '',
                Validators.compose([Validators.required])
            ]
        }, {
            validator: this.matchValidator
        });
    }

    regexValidator(regex: RegExp, error: ValidationErrors): ValidatorFn {
        return (control: AbstractControl): { [key: string]: any } => {
            if (!control.value) {
                return null;
            }
            const valid = regex.test(control.value);
            return valid ? null : error;
        };
    }

    matchValidator(group: FormGroup) {
        if (group.controls['newPasswordCtrl'].value == group.controls['retypePasswordCtrl'].value) {
            return false;
        } else {
            group.controls['retypePasswordCtrl'].setErrors({'mismatch': true});
            return {'mismatch': true};
        }
    }

    getErrorMessage() {
        if (this.firstFormGroup.controls['newPasswordCtrl'].value != this.firstFormGroup.controls['retypePasswordCtrl'].value) {
            this.firstFormGroup.controls['retypePasswordCtrl'].setErrors({'mismatch': true});
        } else {
            this.firstFormGroup.controls['retypePasswordCtrl'].setErrors(null);
        }
        if (this.firstFormGroup.controls['newPasswordCtrl'].hasError('required')) {
            return this.lang.requiredField + ' !';
        } else if (this.firstFormGroup.controls['newPasswordCtrl'].hasError('minlength') && this.passwordRules.minLength.enabled) {
            return this.passwordRules.minLength.value + ' ' + this.lang.passwordminLength + ' !';
        } else if (this.firstFormGroup.controls['newPasswordCtrl'].errors != null && this.firstFormGroup.controls['newPasswordCtrl'].errors.complexityUpper !== undefined && this.passwordRules.complexityUpper.enabled) {
            return this.lang.passwordcomplexityUpper + ' !';
        } else if (this.firstFormGroup.controls['newPasswordCtrl'].errors != null && this.firstFormGroup.controls['newPasswordCtrl'].errors.complexityNumber !== undefined && this.passwordRules.complexityNumber.enabled) {
            return this.lang.passwordcomplexityNumber + ' !';
        } else if (this.firstFormGroup.controls['newPasswordCtrl'].errors != null && this.firstFormGroup.controls['newPasswordCtrl'].errors.complexitySpecial !== undefined && this.passwordRules.complexitySpecial.enabled) {
            return this.lang.passwordcomplexitySpecial + ' !';
        } else {
            this.firstFormGroup.controls['newPasswordCtrl'].setErrors(null);
            this.validPassword = true;
            return '';
        }
    }

    onSubmit() {
        this.passwordModel.currentPassword = this.firstFormGroup.controls['currentPasswordCtrl'].value;
        this.passwordModel.newPassword = this.firstFormGroup.controls['newPasswordCtrl'].value;
        this.passwordModel.reNewPassword = this.firstFormGroup.controls['retypePasswordCtrl'].value;
        this.http.put(this.coreUrl + "rest/currentUser/password", this.passwordModel)
            .subscribe(() => {
                this.config = {data:{state:'END'},disableClose: true};
                this.dialogRef = this.dialog.open(InfoChangePasswordModalComponent, this.config);
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }

    logout() {
        location.href = "index.php?display=true&page=logout&logout=true";
    }
}

@Component({
    templateUrl: "info-change-password-modal.component.html"
})
export class InfoChangePasswordModalComponent {

    lang    : any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<InfoChangePasswordModalComponent>) {
    }

    redirect() {
        location.href = "index.php";
    }
}
