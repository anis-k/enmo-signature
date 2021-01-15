import { Component, OnInit, Input, ViewChild, Renderer2, ElementRef } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatExpansionPanel } from '@angular/material/expansion';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { CookieService } from 'ngx-cookie-service';
import * as EXIF from 'exif-js';
import { TranslateService } from '@ngx-translate/core';
import { FiltersService } from '../service/filters.service';
import { Router } from '@angular/router';
import { tap, exhaustMap, filter, catchError, finalize, switchMap } from 'rxjs/operators';
import { AuthService } from '../service/auth.service';
import { Observable, of } from 'rxjs';
import { ModalController } from '@ionic/angular';

@Component({
    selector: 'app-my-profile',
    templateUrl: 'profile.component.html',
    styleUrls: ['profile.component.scss'],
})

export class ProfileComponent implements OnInit {

    @ViewChild('passwordContent') passwordContent: MatExpansionPanel;
    @ViewChild('avatarProfile') avatarProfile: ElementRef;
    currentTool = 'info';
    profileInfo: any = {
        substitute: null,
        preferences: []
    };
    preferenceInfo: any = {};
    avatarInfo: any = {
        picture: '',
        pictureOrientation: ''
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

    usersList: any = [];

    ruleText = '';
    otherRuleText = '';

    disableState = false;
    msgButton = 'lang.validate';
    loading: boolean = false;

    slideOpts = {
        initialSlide: 0,
        speed: 400
    };
    showHideContent = false;
    userList: any[] = [];

    constructor(private translate: TranslateService,
        public http: HttpClient,
        private router: Router,
        public sanitizer: DomSanitizer,
        public notificationService: NotificationService,
        public signaturesService: SignaturesContentService,
        public authService: AuthService,
        private cookieService: CookieService,
        public filtersService: FiltersService,
        private renderer: Renderer2,
        public modalController: ModalController
    ) { }

    ngOnInit(): void {
        this.initProfileInfo();
        this.getPassRules(); 
    }

    dismissModal() {
        this.modalController.dismiss('cancel');
    }

    test(ev: any) {
        if (ev.detail.value === '') {
            this.userList = [];
        } else if (ev.detail.value.length >= 3) {
            this.http.get('../rest/autocomplete/users?search=' + ev.detail.value).pipe(
                tap((res: any) => {
                    this.userList = res.filter((item: any) => item.id !== this.profileInfo.id);                                        
                }),
                catchError(err => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    initProfileInfo() {
        this.profileInfo = JSON.parse(JSON.stringify(this.authService.user));
        this.preferenceInfo = this.profileInfo.preferences;
        this.avatarInfo.picture = this.profileInfo.picture;
        delete this.profileInfo.picture;
        delete this.profileInfo.preferences;
    }

    initTab(val: any) {
        this.currentTool = val;
        if (val === 'pref') {
            setTimeout(() => {
                this.drawSample();
            }, 200);
        }
    }

    closeProfile() {
        // this.renderer.setStyle(this.avatarProfile.nativeElement, 'transform', 'rotate(0deg)');
        // this.renderer.setStyle(this.avatarProfile.nativeElement, 'content', '');
        setTimeout(() => {
            this.initProfileInfo();
        }, 200);
        this.dismissModal();
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

    allowValidate() {
        if (this.disableState) {
            return true;
        } else if (this.password.newPassword !== '' && (this.handlePassword.error || this.password.newPassword !== this.password.passwordConfirmation || this.password.currentPassword.length === 0 || this.password.newPassword.length === 0 || this.password.passwordConfirmation.length === 0)) {
            return true;
        } else {
            return false;
        }
    }

    async submitProfile() {
        this.disableState = true;
        this.msgButton = 'lang.sending';

        this.http.put('../rest/users/' + this.authService.user.id + '/preferences', this.preferenceInfo).pipe(
            tap(() => {
                this.disableState = false;
                this.msgButton = 'lang.validate';
                this.setLang(this.preferenceInfo.lang);
                this.cookieService.set('maarchParapheurLang', this.preferenceInfo.lang);
                // this.renderer.setStyle(this.avatarProfile.nativeElement, 'transform', 'rotate(0deg)');
            }),
            exhaustMap(() => this.authService.authMode === 'default' ? this.http.put('../rest/users/' + this.authService.user.id, this.profileInfo) :  new Promise(resolve => {resolve(true); })),
            exhaustMap(() => {
                this.authService.updateUserInfoWithTokenRefresh();

                if (this.password.newPassword === '') {
                    this.closeProfile();
                    this.notificationService.success('lang.profileUpdated');
                    return of(false);
                } else if (this.authService.authMode === 'default') {
                    const headers = new HttpHeaders({
                        'Authorization': 'Bearer ' + this.authService.getToken()
                    });
                    return this.http.put('../rest/users/' + this.authService.user.id + '/password', this.password, { observe: 'response', headers: headers });
                } else {
                    return of(false);
                }
            }),
            filter(data => !!data),
            tap((dataPass: any) => {
                this.authService.saveTokens(dataPass.headers.get('Token'), dataPass.headers.get('Refresh-Token'));
                this.password.newPassword = '';
                this.password.passwordConfirmation = '';
                this.password.currentPassword = '';
                this.notificationService.success('lang.profileUpdated');
            }),
            catchError(err => {
                if (err.status === 401) {
                    this.notificationService.error('lang.wrongPassword');
                } else {
                    this.notificationService.handleErrors(err);
                }
                return of(false);
            })
        ).subscribe();
    }

    changePicture() {
        this.msgButton = 'lang.sending';
        this.disableState = true;
        this.http.put('../rest/users/' + this.authService.user.id + '/picture', this.avatarInfo).pipe(
            tap(() => {
                this.authService.user.picture = this.avatarInfo.picture;
                this.renderer.setStyle(this.avatarProfile.nativeElement, 'background-size', 'cover');
                this.renderer.setStyle(this.avatarProfile.nativeElement, 'background-position', 'center');
                this.renderer.setStyle(this.avatarProfile.nativeElement, 'transform', 'rotate(' + this.avatarInfo.pictureOrientation + 'deg)');
                this.renderer.setStyle(this.avatarProfile.nativeElement, 'content', '\'' + this.avatarInfo.pictureOrientation + '\'');
                this.notificationService.success('lang.profileUpdated');
            }),
            finalize(() => {
                this.msgButton = 'lang.validate';
                this.disableState = false;
            })
        ).subscribe();
    }

    selectSubstitute(newUserSubtituted: any) {
        this.userList = [];
        this.http.put('../rest/users/' + this.authService.user.id + '/substitute', { substitute: newUserSubtituted.id })
            .subscribe(() => {
                this.authService.updateUserInfoWithTokenRefresh();
                this.filtersService.resfreshDocuments();
                if (this.signaturesService.documentsList.length > 0 && this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].owner === false) {
                    this.router.navigate(['/documents']);
                }
                this.notificationService.success('lang.substituteEnabled');
            });
    }

    deleteSubstitute() {
        const r = confirm(this.translate.instant('lang.deleteSubstitution') + ' ?');

        if (r) {
            this.profileInfo.substitute = null;

            this.http.put('../rest/users/' + this.authService.user.id + '/substitute', { substitute: this.profileInfo.substitute })
                .subscribe(() => {
                    this.authService.updateUserInfoWithTokenRefresh();
                    this.filtersService.resfreshDocuments();
                    if (this.signaturesService.documentsList.length > 0 && this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].owner === false) {
                        this.router.navigate(['/documents']);
                    }
                    this.notificationService.success('lang.substitutionDeleted');
                });
        }
    }

    handleFileInput(files: FileList) {
        if (this.passwordContent) {
            this.passwordContent.close();
        }
        const fileToUpload = files.item(0);
        // this.renderer.setStyle(this.avatarProfile.nativeElement, 'content', '');

        if (fileToUpload.size <= 5000000) {
            if (['image/png', 'image/jpg', 'image/jpeg', 'image/gif'].indexOf(fileToUpload.type) !== -1) {
                const myReader: FileReader = new FileReader();
                myReader.onloadend = (e) => {
                    const image = new Image();
                    image.src = myReader.result.toString();
                    this.avatarInfo.picture = myReader.result;
                    image.onload = () => this.fixImgOrientation(image);
                };
                myReader.readAsDataURL(fileToUpload);
            } else {
                this.notificationService.error('lang.notAnImage');
            }
        } else {
            this.notificationService.error('lang.imageTooBig');
        }
    }

    fixImgOrientation(image: any) {
        EXIF.getData((image as any), () => {
            let deg = 0;
            const orientation = EXIF.getTag(image, 'Orientation');
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
            this.avatarInfo.pictureOrientation = deg;
            this.changePicture();
        });
    }

    drawSample() {
        const c = document.getElementById('sampleNote');
        const ctx = (<HTMLCanvasElement>c).getContext('2d');
        ctx.clearRect(0, 0, 100, 100);
        ctx.beginPath();
        ctx.lineWidth = this.preferenceInfo.writingSize;
        ctx.moveTo(0, 0);
        ctx.lineTo(100, 100);
        ctx.moveTo(100, 0);
        ctx.lineTo(0, 100);
        ctx.stroke();
    }

    initProfileTab(e: any) {
        if (e.index === 1) {
            this.drawSample();
        }
    }

    counter(i: number) {
        return new Array(i);
    }

    setLang(lang: any) {
        this.translate.use(lang);
    }

    toggleSignature(i: number) {
        this.http.patch('../rest/users/' + this.authService.user.id + '/signatures/' + this.signaturesService.signaturesList[i].id + '/substituted', { 'substituted': !this.signaturesService.signaturesList[i].substituted })
            .subscribe(() => {
                this.signaturesService.signaturesList[i].substituted = !this.signaturesService.signaturesList[i].substituted;
                this.notificationService.success('lang.modificationSaved');
            });
    }

    checkSubstitutes(id: number, substitute: boolean) {
        return this.authService.user.substitutes.includes(id) || substitute === true ? true : false;
    }
}
