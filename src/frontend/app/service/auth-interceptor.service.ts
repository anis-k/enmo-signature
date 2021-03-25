import { Injectable } from '@angular/core';
import { HttpHandler, HttpInterceptor, HttpRequest, HttpClient, HttpErrorResponse } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { Router } from '@angular/router';
import { catchError, filter, switchMap, take } from 'rxjs/operators';
import { NotificationService } from './notification.service';
import { SignaturesContentService } from './signatures.service';
import { AuthService } from './auth.service';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {

    excludeUrls: string[] = ['../rest/authenticate', '../rest/authenticate/token', '../rest/authenticationInformations', '../rest/password', '../rest/passwordRules', '../rest/languages/fr', '../rest/languages/en'];
    frontUrl: string[] = ['../rest/documents/', '../rest/users/', '../rest/groups/', '../rest/configurations/'];

    byPassHandleErrors: any[] = [
        {
            route: '/password',
            method: ['PUT']
        }, {
            route: '/logout',
            method: ['GET']
        }
    ];

    private isRefreshing = false;
    private refreshTokenSubject: BehaviorSubject<any> = new BehaviorSubject<any>(
        null
    );

    constructor(
        public http: HttpClient,
        private router: Router,
        public notificationService: NotificationService,
        public signaturesService: SignaturesContentService,
        public authService: AuthService
    ) { }

    addAuthHeader(request: HttpRequest<any>) {

        const authHeader = this.authService.getToken();

        return request.clone({
            setHeaders: {
                'Authorization': 'Bearer ' + authHeader
            }
        });
    }

    intercept(request: HttpRequest<any>, next: HttpHandler): Observable<any> {
        // We don't want to intercept some routes
        if ((this.excludeUrls.indexOf(request.url) > -1 || request.url.indexOf('/password') > -1) && request.url.indexOf('/passwordRules') === -1 && request.method.indexOf('PUT') === -1) {
            return next.handle(request);
        } else {
            // Add current token in header request
            request = this.addAuthHeader(request);

            // Handle response
            return next.handle(request).pipe(
                /* map((data: any) => {
                  console.log('can modify datas for each response');
                  return data;
                }
                ),*/
                catchError(error => {
                    // Disconnect user if bad token process
                    if (this.byPassHandleErrors.filter(url => request.url.indexOf(url.route) > -1 && url.method.indexOf(request.method) > -1).length > 0) {
                        return next.handle(request);
                    } else if (error.status === 401) {
                        return this.handle401Error(request, next);
                    } else if (error.error.errors === 'Password expired : User must change his password') {
                        return this.router.navigate(['/password-modification']);
                    } else {
                        let response: any;
                        if (request.method === 'GET') {
                            this.frontUrl.forEach(element => {
                                if (request.url.indexOf(element) > -1) {
                                    if (element === '../rest/documents/') {
                                        this.signaturesService.mainDocumentId = null;
                                    }
                                    // this.router.navigate(['/documents']);
                                    response = new HttpErrorResponse({
                                        error: error.error,
                                        status: error.status,
                                        statusText: error.statusText,
                                        headers: error.headers,
                                        url: error.url,
                                    });
                                    return Promise.reject(response);
                                }
                            });
                        }
                        response = new HttpErrorResponse({
                            error: error.error,
                            status: error.status,
                            statusText: error.statusText,
                            headers: error.headers,
                            url: error.url,
                        });
                        return Promise.reject(response);
                    }
                })
            );
        }
    }

    private handle401Error(request: HttpRequest<any>, next: HttpHandler) {
        if (!this.isRefreshing) {
            this.isRefreshing = true;
            this.refreshTokenSubject.next(null);

            return this.authService.refreshToken().pipe(
                switchMap((data: any) => {
                    this.isRefreshing = false;
                    this.refreshTokenSubject.next(data.token);
                    request = this.addAuthHeader(request);
                    return next.handle(request);
                })
            );
        } else {
            return this.refreshTokenSubject.pipe(
                filter((token) => token != null),
                take(1),
                switchMap(() => {
                    request = this.addAuthHeader(request);
                    return next.handle(request);
                })
            );
        }
    }
}
