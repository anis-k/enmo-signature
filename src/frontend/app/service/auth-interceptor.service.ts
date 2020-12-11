import { Injectable } from '@angular/core';
import { HttpHandler, HttpInterceptor, HttpRequest, HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Router } from '@angular/router';
import { catchError, switchMap } from 'rxjs/operators';
import { NotificationService } from './notification.service';
import { EMPTY } from 'rxjs';
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
        }
    ];

    constructor(public http: HttpClient, private router: Router, public notificationService: NotificationService, public signaturesService: SignaturesContentService, public authService: AuthService) { }

    addAuthHeader(request: HttpRequest<any>) {

        const authHeader = this.authService.getToken();

        return request.clone({
            setHeaders: {
                'Authorization': 'Bearer ' + authHeader
            }
        });
    }

    logout() {
        this.authService.logout();
        this.notificationService.error('lang.sessionExpired');
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
                /*map((data: any) => {
                  console.log('can modify datas for each response');
                  return data;
                }
                ),*/
                catchError(error => {
                    // Disconnect user if bad token process
                    if (this.byPassHandleErrors.filter(url => request.url.indexOf(url.route) > -1 && url.method.indexOf(request.method) > -1).length > 0) {
                        return next.handle(request);
                    } else if (error.status === 401) {
                        return this.http.get('../rest/authenticate/token', {
                            params: {
                                refreshToken: this.authService.getRefreshToken()
                            }
                        }).pipe(
                            switchMap((data: any) => {

                                // Update stored token
                                this.authService.setToken(data.token);

                                // Update user info
                                this.authService.updateUserInfo(data.token);

                                // Clone our request with token updated ant try to resend it
                                request = this.addAuthHeader(request);

                                return next.handle(request).pipe(
                                    catchError(err => {
                                        // Disconnect user if bad token process
                                        if (err.status === 401) {
                                            this.notificationService.handleErrors(err);
                                            this.logout();
                                            return EMPTY;
                                        }
                                    })
                                );

                            }
                            ),
                            catchError(err => {
                                // Disconnect user if bad token process
                                if (err.status === 401) {
                                    this.notificationService.handleErrors(err);
                                    this.logout();
                                }
                                return EMPTY;
                            })
                        );
                    } else if (error.error.errors === 'Password expired : User must change his password') {
                        return this.router.navigate(['/password-modification']);
                    } else {
                        if (request.method === 'GET') {
                            this.frontUrl.forEach(element => {
                                if (request.url.indexOf(element) > -1) {
                                    if (element === '../rest/documents/') {
                                        this.signaturesService.mainDocumentId = null;
                                    }
                                    // this.router.navigate(['/documents']);
                                    const response = new HttpErrorResponse({
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
                        this.notificationService.handleErrors(error);
                        const response = new HttpErrorResponse({
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
}
