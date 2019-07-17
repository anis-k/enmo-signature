import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { NotificationService } from './notification.service';
import { SignaturesContentService } from './signatures.service';
import { LocalStorageService } from './local-storage.service';

@Injectable({
    providedIn: 'root'
})
export class AuthService {

    authMode: string = '';
    changeKey: boolean = false;
    user: any = {};

    constructor(public http: HttpClient,
        private router: Router,
        public notificationService: NotificationService,
        public signaturesService: SignaturesContentService,
        private localStorage: LocalStorageService) { }

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

    logout() {
        this.clearTokens();
        this.router.navigate(['/login']);
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

        this.user = JSON.parse(atob(token.split('.')[1])).user;

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
}
