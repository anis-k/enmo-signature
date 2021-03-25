import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { NotificationService } from './notification.service';
import { SignaturesContentService } from './signatures.service';
import { LocalStorageService } from './local-storage.service';
import { NavController } from '@ionic/angular';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class AuthService {

    authMode: string = 'default';
    changeKey: boolean = false;
    user: any = {};
    signatureRoles: any[] = [];

    constructor(public http: HttpClient,
        private router: Router,
        public notificationService: NotificationService,
        public signaturesService: SignaturesContentService,
        private localStorage: LocalStorageService,
        public navCtrl: NavController
    ) { }

    getToken() {
        return this.localStorage.get('MaarchParapheurToken');
    }

    setToken(token: string) {
        this.localStorage.save('MaarchParapheurToken', token);
    }

    getRefreshToken() {
        return this.localStorage.get('MaarchParapheurRefreshToken');
    }

    setRefreshToken(refreshToken: string) {
        this.localStorage.save('MaarchParapheurRefreshToken', refreshToken);
    }

    clearTokens() {
        this.localStorage.remove('MaarchParapheurToken');
        this.localStorage.remove('MaarchParapheurRefreshToken');
    }

    refreshToken() {
        return this.http
            .get<any>('../rest/authenticate/token', { params: { refreshToken: this.getRefreshToken() } })
            .pipe(
                tap((data) => {
                    // Update stored token
                    this.setToken(data.token);

                    // Update user info
                    this.updateUserInfo(data.token);
                }),
                catchError((error) => {
                    this.logout();
                    this.notificationService.error('lang.sessionExpired');
                    return of(false);
                })
            );
    }

    logout() {
        const refreshToken = this.getRefreshToken();
        if (refreshToken === null) {
            this.clearTokens();
            this.navCtrl.navigateRoot('/login');
            return;
        }

        this.http.get('../rest/authenticate/logout')
            .pipe(
                tap(() => {
                    this.clearTokens();
                    this.navCtrl.navigateRoot('/login');
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    this.clearTokens();
                    this.navCtrl.navigateRoot('/login');
                    return of(false);
                })
            ).subscribe();
    }

    saveTokens(token: string, refreshToken: string) {
        this.setToken(token);
        this.setRefreshToken(refreshToken);
    }

    isAuth(): boolean {
        return this.getToken() !== null;
    }

    updateUserInfo(token: string) {
        const currentPicture  = this.user.picture;

        const tokenData = JSON.parse(atob(token.split('.')[1]));

        this.user = tokenData.user;
        this.authMode = tokenData.connection;

        this.user.picture = currentPicture;
    }

    updateUserInfoWithTokenRefresh() {
        this.http.get('../rest/authenticate/token', {
            params: {
                refreshToken: this.getRefreshToken()
            }
        }).subscribe({
            next: (data: any) => {
                this.setToken(data.token);

                this.updateUserInfo(this.getToken());
            },
            error: err => {
                this.notificationService.handleErrors(err);
            }
        });
    }

    setUser(value: any) {
        this.user = value;
    }

    getSignatureMode(id: string) {
        return id === 'visa' ? 'stamp' : id;
    }

    getWorkflowMode(id: string) {
        return this.signatureRoles.filter((item: any) => item.id === id)[0].type;
    }

    setCachedUrl(url: string) {
        this.localStorage.save('MaarchParapheurCacheUrl', url);
    }

    getCachedUrl() {
        return this.localStorage.get('MaarchParapheurCacheUrl');
    }

    cleanCachedUrl() {
        return this.localStorage.remove('MaarchParapheurCacheUrl');
    }
}
