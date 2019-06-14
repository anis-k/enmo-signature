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

    if (this.cookieService.check('maarchParapheurLang')) {
      const cookieInfoLang = this.cookieService.get('maarchParapheurLang');
      translate.setDefaultLang(cookieInfoLang);
    } else {
      this.cookieService.set( 'maarchParapheurLang', 'fr' );
      translate.setDefaultLang('fr');
    }
  }
}
