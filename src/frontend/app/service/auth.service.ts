import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { finalize } from 'rxjs/operators';
import { Router } from '@angular/router';
import { NotificationService } from './notification.service';
import { SignaturesContentService } from './signatures.service';

@Injectable({
    providedIn: 'root'
})
export class AuthService {

    authMode: string = '';
    changeKey: boolean = false;
    user: any = {};
    loadingConnection: boolean = false;
    loadingForm: boolean = false;

    constructor(public http: HttpClient, private router: Router, public notificationService: NotificationService, public signaturesService: SignaturesContentService) { }

    login(login: string, password: string) {
        this.loadingConnection = true;
        this.http.post('../rest/authenticate', { 'login': login, 'password': password }, { observe: 'response' })
            .pipe(
                finalize(() => {
                    this.loadingConnection = false;
                })
            )
            .subscribe({
                next: (data: any) => {
                    this.loadingForm = true;
                    this.saveTokens(data.headers.get('Token'), data.headers.get('Refresh-Token'));
                    this.user = {};

                    $('.maarchLogo').css({ 'transform': 'translateY(0px)' });
                    setTimeout(() => {
                        this.router.navigate(['/documents']);
                    }, 700);
                },
                error: err => {
                    if (err.status === 401) {
                        this.notificationService.error('lang.wrongLoginPassword');
                    } else {
                        this.notificationService.handleErrors(err);
                    }
                }
            });
    }

    getToken() {
        return localStorage.getItem('MaarchParapheurToken');
    }

    setToken(token: string) {
        localStorage.setItem('MaarchParapheurToken', token);
    }

    getRefreshToken() {
        return localStorage.getItem('MaarchParapheurRefreshToken');
    }

    setRefreshToken(refreshToken: string) {
        localStorage.setItem('MaarchParapheurRefreshToken', refreshToken);
    }

    clearTokens() {
        localStorage.removeItem('MaarchParapheurToken');
        localStorage.removeItem('MaarchParapheurRefreshToken');
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
        if (this.getToken() !== null) {
            return true;
        } else {
            return false;
        }
    }

    updateUserInfo(token: string) {
        this.user = JSON.parse(atob(token.split('.')[1])).user;
    }
}
