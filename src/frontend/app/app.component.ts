import { Component, ViewEncapsulation } from '@angular/core';
import { CookieService } from 'ngx-cookie-service';
import { SignaturesContentService } from './service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from './service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  encapsulation: ViewEncapsulation.None,
  styleUrls: ['app.component.scss']
})

export class AppComponent {

  constructor(private translate: TranslateService, public http: HttpClient, public signaturesService: SignaturesContentService, public sanitizer: DomSanitizer, private cookieService: CookieService, public notificationService: NotificationService) {
    translate.setDefaultLang('en');

    if (this.cookieService.check('maarchParapheurAuth')) {
      const cookieInfo = JSON.parse(atob(this.cookieService.get('maarchParapheurAuth')));

      this.http.get('../rest/users/' + cookieInfo.id)
        .subscribe((data: any) => {
          this.signaturesService.userLogged = data.user;
          this.translate.use(this.signaturesService.userLogged.preferences.lang);
        },
          (err: any) => {
            this.notificationService.handleErrors(err);
          });
    }
  }
}
