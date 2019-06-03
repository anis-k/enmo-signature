import { Component, OnInit, Input, ViewChild } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry, MatSidenav, MatExpansionPanel, MatTabGroup } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { CookieService } from 'ngx-cookie-service';
import * as EXIF from 'exif-js';
import { TranslateService } from '@ngx-translate/core';

@Component({
    selector: 'app-my-profile',
    templateUrl: 'profile.component.html',
    styleUrls: ['profile.component.scss'],
})

export class ProfileComponent implements OnInit {

    @Input('snavRightComponent') snavRightComponent: MatSidenav;
    @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

    @ViewChild('passwordContent') passwordContent: MatExpansionPanel;

    profileInfo: any = {
        substitute: null,
        preferences: []
    };
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
    password: any = {
        currentPassword: '',
        newPassword: '',
        passwordConfirmation: ''
    };
    handlePassword: any = {
        error: false,
        errorMsg: ''
    };

    usersRest: any = [];
    currentUserRest: any = {};
    currentUserRestPassword = '';

    ruleText = '';
    otherRuleText = '';

    showPassword = false;

    disableState = false;
    msgButton = 'lang.validate';
    loading: boolean = false;

    constructor(private translate: TranslateService, public http: HttpClient, iconReg: MatIconRegistry, public sanitizer: DomSanitizer, public notificationService: NotificationService, public signaturesService: SignaturesContentService, private cookieService: CookieService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));
    }

    ngOnInit(): void {
        if (this.cookieService.check('maarchParapheurAuth')) {
            this.loading = true;
            const cookieInfo = JSON.parse(atob(this.cookieService.get('maarchParapheurAuth')));
            this.http.get('../rest/users/' + cookieInfo.id)
                .subscribe((data: any) => {
                    this.profileInfo = data.user;
                    this.loading = false;
                },
                    (err: any) => {
                        this.notificationService.handleErrors(err);
                    });
        }
    }

    closeProfile() {
        $('.avatarProfile').css({ 'transform': 'rotate(0deg)' });
        $('.avatarProfile').css({ 'content': '' });
        this.profileInfo = JSON.parse(JSON.stringify(this.signaturesService.userLogged));
        this.passwordContent.close();

        if (this.signaturesService.mobileMode) {
            this.snavLeftComponent.open();
            this.snavRightComponent.close();
        }
    }


    changePasswd() {
        this.showPassword = true;
        this.getPassRules();
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
                            this.translate.get('lang.minLengthChar', {charLength: rule.value}).subscribe((res: string) => {
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
                            this.translate.get('lang.renewalInfo', {time: rule.value}).subscribe((res: string) => {
                                otherRuleTextArr.push(res);
                            });
                        }
                    } else if (rule.label === 'historyLastUse') {
                        this.passwordRules.historyLastUse.enabled = rule.enabled;
                        this.passwordRules.historyLastUse.value = rule.value;
                        if (rule.enabled) {
                            this.translate.get('lang.historyUseInfo', {countPwd: rule.value}).subscribe((res: string) => {
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

    checkPasswordValidity(password: string) {
        this.handlePassword.error = true;

        if (!password.match(/[A-Z]/g) && this.passwordRules.complexityUpper.enabled) {
            this.handlePassword.errorMsg = 'lang.upperRequired';
        } else if (!password.match(/[0-9]/g) && this.passwordRules.complexityNumber.enabled) {
            this.handlePassword.errorMsg = 'lang.numberRequired';
        } else if (!password.match(/[^A-Za-z0-9]/g) && this.passwordRules.complexitySpecial.enabled) {
            this.handlePassword.errorMsg = 'lang.specialCharRequired';
        } else if (password.length < this.passwordRules.minLength.value && this.passwordRules.minLength.enabled) {
            this.translate.get('lang.minLengthChar', {charLength: this.passwordRules.minLength.value}).subscribe((res: string) => {
                this.handlePassword.errorMsg = res;
            });
        } else {
            this.handlePassword.error = false;
            this.handlePassword.errorMsg = '';
        }
    }

    allowValidate() {
        if (this.disableState) {
            return true;
        } else if (this.showPassword && (this.handlePassword.error || this.password.newPassword !== this.password.passwordConfirmation || this.password.currentPassword.length === 0 || this.password.newPassword.length === 0 || this.password.passwordConfirmation.length === 0)) {
            return true;
        } else {
            return false;
        }
    }

    submitProfile() {
        this.disableState = true;
        this.msgButton = 'lang.sending';
        let profileToSend = {
            'firstname': this.profileInfo.firstname,
            'lastname': this.profileInfo.lastname,
            'email': this.profileInfo.email,
            'picture': this.profileInfo.picture,
            'preferences': this.profileInfo.preferences,
            'substitute': this.profileInfo.substitute,
        };

        if (this.profileInfo.substitute !== null) {
            alert('Vous avez choisi une délégation, vous ne pourrez plus viser ou signer de courrier.');
        }

        if (this.profileInfo.picture === this.signaturesService.userLogged.picture) {
            profileToSend.picture = '';
        } else {
            const orientation = $('.avatarProfile').css('content');
            profileToSend['pictureOrientation'] = orientation.replace(/\"/g, '');
        }

        this.http.put('../rest/users/' + this.signaturesService.userLogged.id, profileToSend)
            .subscribe((data: any) => {
                this.signaturesService.userLogged.email = this.profileInfo.email;
                this.signaturesService.userLogged.firstname = this.profileInfo.firstname;
                this.signaturesService.userLogged.lastname = this.profileInfo.lastname;
                this.signaturesService.userLogged.picture = data.user.picture;
                this.signaturesService.userLogged.preferences = data.user.preferences;
                this.signaturesService.userLogged.substitute = data.user.substitute;
                this.profileInfo.picture = data.user.picture;
                this.setLang(this.signaturesService.userLogged.preferences.lang);
                this.cookieService.set( 'maarchParapheurLang', this.signaturesService.userLogged.preferences.lang );

                $('.avatarProfile').css({ 'transform': 'rotate(0deg)' });

                if (this.showPassword) {
                    this.http.put('../rest/users/' + this.signaturesService.userLogged.id + '/password', this.password)
                        .subscribe(() => {
                            this.password.newPassword = '';
                            this.password.passwordConfirmation = '';
                            this.password.currentPassword = '';
                            this.notificationService.success('lang.profileUpdated');
                            this.disableState = false;
                            this.msgButton = 'lang.validate';
                            this.closeProfile();
                        }, (err) => {
                            this.disableState = false;
                            this.msgButton = 'lang.validate';
                            if (err.status === 401) {
                                this.notificationService.error('lang.wrongPassword');
                            } else {
                                this.notificationService.handleErrors(err);
                            }
                        });
                }

                if (!this.showPassword) {
                    this.notificationService.success('lang.profileUpdated');
                    this.disableState = false;
                    this.msgButton = 'lang.validate';
                    this.closeProfile();
                }

                if (this.profileInfo.substitute !== null) {
                    this.http.patch('../rest/users/' + this.signaturesService.userLogged.id + '/signatures/substituted', {'signatures': this.signaturesService.signaturesList})
                        .subscribe(() => { }, (err) => {
                            this.notificationService.handleErrors(err);
                        });
                }

            }, (err) => {
                this.disableState = false;
                this.msgButton = 'lang.validate';
                this.notificationService.handleErrors(err);
                this.disableState = false;
            });
    }

    handleFileInput(files: FileList) {
        this.passwordContent.close();
        const fileToUpload = files.item(0);
        $('.avatarProfile').css({ 'content': '' });

        if (fileToUpload.size <= 5000000) {
            if (['image/png', 'image/svg+xml', 'image/jpg', 'image/jpeg', 'image/gif'].indexOf(fileToUpload.type) !== -1) {
                const myReader: FileReader = new FileReader();
                myReader.onloadend = (e) => {
                    this.profileInfo.picture = myReader.result;
                    const image = new Image();

                    image.src = myReader.result.toString();
                    image.onload = function () {
                        EXIF.getData((image as any), () => {
                            let deg = 0;
                            const orientation = EXIF.getTag(this, 'Orientation');
                            switch (orientation) {
                                case 3:
                                    deg = 180;
                                    break;
                                case 6:
                                    deg = 90;
                                    break;
                                case 8:
                                    deg = -90;
                                    break;
                            }
                            $('.avatarProfile').css({ 'background-size': 'cover' });
                            $('.avatarProfile').css({ 'background-position': 'center' });
                            $('.avatarProfile').css({ 'transform': 'rotate(' + deg + 'deg)' });
                            $('.avatarProfile').css({ 'content': '\'' + deg + '\'' });
                        });
                    };
                };
                myReader.readAsDataURL(fileToUpload);
            } else {
                this.notificationService.error('lang.notAnImage');
            }
        } else {
            this.notificationService.error('lang.imageTooBig');
        }
    }

    drawSample() {
        const c = document.getElementById('sampleNote');
        const ctx = (<HTMLCanvasElement>c).getContext('2d');
        ctx.clearRect(0, 0, 150, 150);
        ctx.beginPath();
        ctx.lineWidth = this.profileInfo.preferences.writingSize;
        ctx.moveTo(0, 0);
        ctx.lineTo(150, 150);
        ctx.moveTo(150, 0);
        ctx.lineTo(0, 150);
        ctx.stroke();
    }

    initProfileTab(e: any) {
        if (e.index === 1) {
            this.drawSample();
        }
        if (e.index === 2) {
            this.swithToAdmin();
        }
    }

    counter(i: number) {
        return new Array(i);
    }

    siwtchToleft(tab: MatTabGroup) {
        tab.selectedIndex++;

        if (tab.selectedIndex === 2) {
            this.swithToAdmin();
        }
    }

    siwtchToRight(tab: MatTabGroup) {
        if (tab.selectedIndex > 0) {
            tab.selectedIndex--;

            if (tab.selectedIndex === 2) {
                this.swithToAdmin();
            }
        }
    }

    swithToAdmin() {
        /*if (this.usersRest.length === 0) {
            this.getPassRules();
            this.http.get('../rest/users?mode=rest')
                .subscribe((data: any) => {
                    this.usersRest = data.users;
                    this.currentUserRest = data.users[0];

                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                });
        }*/

        if (this.usersRest.length === 0) {
            this.getPassRules();
            this.http.get('../rest/users')
                .subscribe((data: any) => {
                    this.usersRest = data.users;
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                });
        }

        if (this.signaturesService.signaturesList.length === 0) {
            this.http.get('../rest/users/' + this.profileInfo.id + '/signatures')
                .subscribe((data: any) => {
                    this.signaturesService.signaturesList = data.signatures;
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                });
        }
    }

    updateRestUser() {
        this.http.put('../rest/users/' + this.currentUserRest.id + '/password', { 'newPassword': this.currentUserRestPassword })
            .subscribe(() => {
                this.currentUserRestPassword = '';
                this.translate.get('lang.passwordOfUserUpdated', {user: this.currentUserRest.firstname + ' ' + this.currentUserRest.lastname}).subscribe((res: string) => {
                    this.notificationService.success(res);
                });
            }, (err) => {
                this.notificationService.handleErrors(err);
            });
    }

    setLang(lang: any) {
        this.translate.use(lang);
    }

    toggleSignature(i: number) {
        this.signaturesService.signaturesList[i].substituted = !this.signaturesService.signaturesList[i].substituted;
    }
}
