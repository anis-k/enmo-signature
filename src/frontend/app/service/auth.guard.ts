
import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { CookieService } from 'ngx-cookie-service';
import { HttpClient } from '@angular/common/http';
import { SignaturesContentService } from './signatures.service';
import { TranslateService } from '@ngx-translate/core';

@Injectable({
    providedIn: 'root'
})
export class AuthGuard implements CanActivate {

    constructor(private translate: TranslateService, public http: HttpClient, private router: Router, public signaturesService: SignaturesContentService, private cookieService: CookieService) { }

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {

        const tokenInfo = localStorage.getItem('MaarchParapheurToken');
        if (tokenInfo !== null) {
            if (this.signaturesService.userLogged.id === undefined) {
                const userInfo = JSON.parse(atob(tokenInfo.split('.')[1])).user;
                this.signaturesService.userLogged = userInfo;

                this.translate.use(this.signaturesService.userLogged.preferences.lang);
                this.cookieService.set('maarchParapheurLang', this.signaturesService.userLogged.preferences.lang);

                if (this.signaturesService.signaturesList.length === 0) {
                    this.http.get('../rest/users/' + this.signaturesService.userLogged.id + '/signatures')
                        .subscribe((dataSign: any) => {
                            this.signaturesService.signaturesList = dataSign.signatures;
                        });
                }

                if (this.signaturesService.userLogged.picture === undefined) {
                    this.http.get('../rest/users/' + this.signaturesService.userLogged.id + '/picture')
                        .subscribe((dataPic: any) => {
                            this.signaturesService.userLogged.picture = dataPic.picture;
                        });
                }
            }

            return true;
        } else {
            localStorage.removeItem('MaarchParapheurToken');
            localStorage.removeItem('MaarchParapheurRefreshToken');
            this.router.navigateByUrl('/login');
            return false;
        }
    }
}
