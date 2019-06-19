import { Injectable, Injector } from '@angular/core';
import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { Router } from '@angular/router';
import { catchError, tap, map, finalize } from 'rxjs/operators';
import { NotificationService } from './notification.service';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {

  constructor(private injector: Injector, private router: Router, public notificationService: NotificationService) { }

  addAuthHeader(request: HttpRequest<any>) {

    const authHeader = localStorage.getItem('MaarchParapheur');

    return request.clone({
      setHeaders: {
        'Authorization': 'Bearer ' + authHeader
      }
    });
  }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<any> {
    // We don't want to intercept rest/log
    if (request.url.includes('../rest/log')) {
      return next.handle(request);
    } else {
      // Add current token in header request
      request = this.addAuthHeader(request);

      // Handle response
      return next.handle(request).pipe(
        // Upate current token with token received in response request (if exist)
        map((data: any) => {
          if (data.headers !== undefined && data.headers.get('Token') !== null) {
            console.log('Token Refresh!');
            localStorage.setItem('MaarchParapheur', data.headers.get('Token'));
          }
          return data;
        }
        ),
        catchError(error => {
          // Disconnect user if bad token process
          if (error.status === 401) {
            console.log('Token expired !');
            localStorage.removeItem('MaarchParapheur');
            this.router.navigate(['login']);
            this.notificationService.error('lang.sessionExpired');
            return Observable;
          }
        })
      );
    }
  }
}
