import { Component, OnInit, Input } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry, MatSidenav } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { CookieService } from 'ngx-cookie-service';
import * as EXIF from 'exif-js';

@Component({
    selector: 'app-my-profile',
    templateUrl: 'profile.component.html',
    styleUrls: ['profile.component.scss'],
})

export class ProfileComponent implements OnInit {

    @Input('snavRightComponent') snavRightComponent: MatSidenav;
    @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

    profileInfo: any = {};
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
    ruleText = '';
    otherRuleText = '';

    showPassword = false;

    constructor(public http: HttpClient, iconReg: MatIconRegistry, public sanitizer: DomSanitizer, public notificationService: NotificationService, public signaturesService: SignaturesContentService, private cookieService: CookieService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));
    }

    ngOnInit(): void {
        if (this.cookieService.check('maarchParapheurAuth')) {
            const cookieInfo = JSON.parse(atob(this.cookieService.get('maarchParapheurAuth')));
            this.http.get('../rest/users/' + cookieInfo.id)
                .subscribe((data: any) => {
                    this.profileInfo = data.user;
                },
                (err: any) => {
                    this.notificationService.handleErrors(err);
                });
        }
    }

    closeProfile() {
        this.profileInfo = this.signaturesService.userLogged;
        this.snavLeftComponent.open();
        this.snavRightComponent.close();
    }

    changePasswd() {
        this.showPassword = true;
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
                            ruleTextArr.push(rule.value + ' Longueur minimale');
                        }

                    } else if (rule.label === 'complexityUpper') {
                        this.passwordRules.complexityUpper.enabled = rule.enabled;
                        this.passwordRules.complexityUpper.value = rule.value;
                        if (rule.enabled) {
                            ruleTextArr.push('Majuscule requise');
                        }

                    } else if (rule.label === 'complexityNumber') {
                        this.passwordRules.complexityNumber.enabled = rule.enabled;
                        this.passwordRules.complexityNumber.value = rule.value;
                        if (rule.enabled) {
                            ruleTextArr.push('Chiffre requis');
                        }

                    } else if (rule.label === 'complexitySpecial') {
                        this.passwordRules.complexitySpecial.enabled = rule.enabled;
                        this.passwordRules.complexitySpecial.value = rule.value;
                        if (rule.enabled) {
                            ruleTextArr.push('Caractère spécial requis');
                        }
                    } else if (rule.label === 'renewal') {
                        this.passwordRules.renewal.enabled = rule.enabled;
                        this.passwordRules.renewal.value = rule.value;
                        if (rule.enabled) {
                            otherRuleTextArr.push('Veuillez noter que ce nouveau mot de passe ne sera valide que' + ' <b>' + rule.value + ' jour(s)</b>. ' + 'Passé ce délai, vous devrez en choisir un nouveau.');
                        }
                    } else if (rule.label === 'historyLastUse') {
                        this.passwordRules.historyLastUse.enabled = rule.enabled;
                        this.passwordRules.historyLastUse.value = rule.value;
                        if (rule.enabled) {
                            otherRuleTextArr.push('Vous ne pouvez pas utiliser les <b>' + rule.value + '</b> dernier(s) mot(s) de passe.');
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
            this.handlePassword.errorMsg = '1 majuscule au minimum !';
        } else if (!password.match(/[0-9]/g) && this.passwordRules.complexityNumber.enabled) {
            this.handlePassword.errorMsg = '1 chiffre au minimum !';
        } else if (!password.match(/[^A-Za-z0-9]/g) && this.passwordRules.complexitySpecial.enabled) {
            this.handlePassword.errorMsg = 'Caractère spécial requis !';
        } else if (password.length < this.passwordRules.minLength.value && this.passwordRules.minLength.enabled) {
            this.handlePassword.errorMsg = this.passwordRules.minLength.value + ' caractère(s) au minimum !';
        } else {
            this.handlePassword.error = false;
            this.handlePassword.errorMsg = '';
        }
    }

    allowValidate() {
        if (this.showPassword && (this.handlePassword.error || this.password.newPassword !== this.password.passwordConfirmation || this.password.currentPassword.length === 0 || this.password.newPassword.length === 0 || this.password.passwordConfirmation.length === 0)) {
            return true;
        } else {
            return false;
        }
    }

    submitProfile() {
        const orientation = $('.avatarProfile').css('content');
        this.profileInfo.pictureOrientation = orientation.replace(/\"/g, '');
        this.http.put('../rest/users/' + this.signaturesService.userLogged.id, this.profileInfo)
            .subscribe((data: any) => {
                this.signaturesService.userLogged.email = this.profileInfo.email;
                this.signaturesService.userLogged.firstname = this.profileInfo.firstname;
                this.signaturesService.userLogged.lastname = this.profileInfo.lastname;
                this.signaturesService.userLogged.picture = data.user.picture;
                this.profileInfo.picture = data.user.picture;
                $('.avatarProfile').css({'transform': 'rotate(0deg)'});
                // MAJ COOKIE
                this.cookieService.delete('maarchParapheurAuth');
                this.cookieService.set(btoa(JSON.stringify(this.signaturesService.userLogged)), 'maarchParapheurAuth');

                if (this.showPassword) {
                    this.http.put('../rest/users/' + this.signaturesService.userLogged.id + '/password', this.password)
                    .subscribe(() => {
                        this.password.newPassword = '';
                        this.password.passwordConfirmation = '';
                        this.password.currentPassword = '';
                        this.notificationService.success('Profil modifié');
                        this.closeProfile();
                    }, (err) => {
                        if (err.status === 401) {
                            this.notificationService.error('Mauvais mot de passe');
                        } else {
                            this.notificationService.handleErrors(err);
                        }
                    });
                }

                if (!this.showPassword) {
                    this.notificationService.success('Profil modifié');
                    this.closeProfile();
                }
            }, (err) => {
                this.notificationService.handleErrors(err);
            });
    }

    handleFileInput(files: FileList) {
        const fileToUpload = files.item(0);
        $('.avatarProfile').css({'content': '' });

        if (fileToUpload.size <=  5000000) {
            if (['image/png', 'image/svg+xml', 'image/jpg', 'image/jpeg', 'image/gif'].indexOf(fileToUpload.type) !== -1) {
                const myReader: FileReader = new FileReader();
                myReader.onloadend = (e) => {
                    this.profileInfo.picture = myReader.result;
                    const image = new Image();

                    image.src = myReader.result.toString();
                    image.onload = function() {
                        EXIF.getData(image, () => {
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
                            $('.avatarProfile').css({'background-size': 'cover'});
                            $('.avatarProfile').css({'background-position': 'center'});
                            $('.avatarProfile').css({'transform': 'rotate(' + deg + 'deg)'});
                            $('.avatarProfile').css({'content': '\'' + deg + '\'' });
                        });
                    };
                };
                myReader.readAsDataURL(fileToUpload);
            } else {
                this.notificationService.error('Ceci n\'est pas une image');
            }
        } else {
            this.notificationService.error('Image trop volumineuse (5mo max.)');
        }
    }
}
