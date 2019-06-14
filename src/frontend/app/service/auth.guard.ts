
import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { Observable, BehaviorSubject } from 'rxjs';
import { tap } from 'rxjs/operators';
import { CookieService } from 'ngx-cookie-service';
import { HttpClient } from '@angular/common/http';
import { SignaturesContentService } from './signatures.service';

@Injectable({
    providedIn: 'root'
})
export class AuthGuard implements CanActivate {

    constructor(public http: HttpClient, private router: Router, public signaturesService: SignaturesContentService, private cookieService: CookieService) { }

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
        if (this.cookieService.check('maarchParapheurAuth')) {
            console.log('Cookie ok !');
            if (this.signaturesService.userLogged.id === undefined) {
                const cookieInfo = JSON.parse(atob(this.cookieService.get('maarchParapheurAuth')));
                this.http.get('../rest/users/' + cookieInfo.id)
                    .subscribe((data: any) => {
                        this.signaturesService.userLogged = data.user;

                        if (this.signaturesService.signaturesList.length === 0) {
                            this.http.get('../rest/users/' + this.signaturesService.userLogged.id + '/signatures')
                                .subscribe((dataSign: any) => {
                                    this.signaturesService.signaturesList = dataSign.signatures;
                                });
                        }
                    },
                        (err: any) => {
                            this.router.navigateByUrl('/login');
                        });
                return true;
            } else {
                return true;
            }
        } else {
            console.log('auth failed !');
            this.router.navigateByUrl('/login');
            return false;
        }
    }
}
