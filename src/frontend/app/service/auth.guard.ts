
import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { CookieService } from 'ngx-cookie-service';
import { HttpClient } from '@angular/common/http';
import { SignaturesContentService } from './signatures.service';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from './auth.service';
import { map } from 'rxjs/operators';
import { Observable } from 'rxjs';
import { LocalStorageService } from './local-storage.service';
import { AlertComponent } from '../plugins/alert.component';
import { MatDialog } from '@angular/material';

@Injectable({
    providedIn: 'root'
})
export class AuthGuard implements CanActivate {

    constructor(private translate: TranslateService,
        public http: HttpClient,
        private router: Router,
        public signaturesService: SignaturesContentService,
        private cookieService: CookieService,
        public authService: AuthService,
        private localStorage: LocalStorageService,
        public dialog: MatDialog) { }

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> | boolean {

        let tokenInfo = this.authService.getToken();
        if (tokenInfo !== null) {
            if (this.authService.user.id === undefined) {
                this.authService.user = JSON.parse(atob(tokenInfo.split('.')[1])).user;

                this.translate.use(this.authService.user.preferences.lang);
                this.cookieService.set('maarchParapheurLang', this.authService.user.preferences.lang);

                if (this.signaturesService.signaturesList.length === 0) {
                    this.http.get('../rest/users/' + this.authService.user.id + '/signatures')
                        .subscribe((dataSign: any) => {
                            this.signaturesService.signaturesList = dataSign.signatures;
                        });
                }

                if (this.authService.user.picture === undefined) {
                    this.http.get('../rest/users/' + this.authService.user.id + '/picture')
                        .subscribe((dataPic: any) => {
                            this.authService.user.picture = dataPic.picture;
                        });
                }
            }

            return true;
        } else {
            return this.http.get('../rest/authenticationInformations')
                .pipe(
                    map((data: any) => {
                        this.authService.authMode = data.connection;
                        this.authService.changeKey = data.changeKey;
                        this.localStorage.setAppSession(data.instanceId);
                        tokenInfo = this.authService.getToken();

                        if (tokenInfo !== null) {
                            this.authService.user = JSON.parse(atob(tokenInfo.split('.')[1])).user;

                            this.translate.use(this.authService.user.preferences.lang);
                            this.cookieService.set('maarchParapheurLang', this.authService.user.preferences.lang);

                            if (this.signaturesService.signaturesList.length === 0) {
                                this.http.get('../rest/users/' + this.authService.user.id + '/signatures')
                                    .subscribe((dataSign: any) => {
                                        this.signaturesService.signaturesList = dataSign.signatures;
                                    });
                            }

                            if (this.authService.user.picture === undefined) {
                                this.http.get('../rest/users/' + this.authService.user.id + '/picture')
                                    .subscribe((dataPic: any) => {
                                        this.authService.user.picture = dataPic.picture;
                                    });
                            }

                            if (this.authService.changeKey) {
                                this.dialog.open(AlertComponent, { autoFocus: false, disableClose: true, data: { mode: 'warning', title: 'lang.warnPrivateKeyTitle', msg: 'lang.warnPrivateKey' } });
                            }
                            return true;
                        } else {
                            this.authService.logout();
                            return false;
                        }
                    })
                );

        }
    }
}
