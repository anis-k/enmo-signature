import { Injectable } from '@angular/core';
import { HttpHandler, HttpInterceptor, HttpRequest, HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Router } from '@angular/router';
import { catchError, switchMap } from 'rxjs/operators';
import { NotificationService } from './notification.service';
import { empty } from 'rxjs';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {

  excludeUrls: string[] = ['../rest/authenticate', '../rest/authenticate/token', '../rest/authenticationInformations', '../rest/password', '../rest/passwordRules', '../rest/languages/fr', '../rest/languages/en'];
  constructor(public http: HttpClient, private router: Router, public notificationService: NotificationService) { }

  addAuthHeader(request: HttpRequest<any>) {

    const authHeader = localStorage.getItem('MaarchParapheurToken');

    return request.clone({
      setHeaders: {
        'Authorization': 'Bearer ' + authHeader
      }
    });
  }

  logout() {
    localStorage.removeItem('MaarchParapheurToken');
    localStorage.removeItem('MaarchParapheurRefreshToken');
    this.router.navigate(['login']);
    this.notificationService.error('lang.sessionExpired');
  }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<any> {
    // We don't want to intercept some routes
    if (this.excludeUrls.indexOf(request.url) > -1) {
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
          if (error.status === 401) {
            return this.http.get('../rest/authenticate/token', {
              params: {
                refreshToken: localStorage.getItem('MaarchParapheurRefreshToken')
              }
            }).pipe(
              switchMap((data: any) => {

                // Update stored token
                localStorage.setItem('MaarchParapheurToken', data.token);
                // Clone our request with token updated ant try to resend it
                request = this.addAuthHeader(request);

                return next.handle(request).pipe(
                  catchError(err => {
                    // Disconnect user if bad token process
                    if (err.status === 401) {
                      this.logout();
                      return empty();
                    }
                  })
                );

              }
              ),
              catchError(err => {
                // Disconnect user if bad token process
                if (err.status === 401) {
                  this.logout();
                }
                return empty();
              })
            );
          }
        })
      );
    }
  }
}
