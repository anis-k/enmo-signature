import { Component, ViewEncapsulation } from '@angular/core';
import { CookieService } from 'ngx-cookie-service';
import { SignaturesContentService } from './service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from './service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { MatDialog, MatIconRegistry } from '@angular/material';
import { AlertComponent } from './plugins/alert.component';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  encapsulation: ViewEncapsulation.None,
  styleUrls: ['app.component.scss']
})

export class AppComponent {

  constructor(private translate: TranslateService, public http: HttpClient, public signaturesService: SignaturesContentService, public sanitizer: DomSanitizer, private cookieService: CookieService, public notificationService: NotificationService, public dialog: MatDialog, iconReg: MatIconRegistry) {
    iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));

    this.http.get('../rest/authenticationInformations')
      .subscribe((data: any) => {
        this.signaturesService.authMode = data.connection;
        this.signaturesService.changeKey = data.changeKey;
        if (this.signaturesService.changeKey) {
          this.dialog.open(AlertComponent, { autoFocus: false, disableClose: true, data: { mode: 'warning', title: 'lang.warnPrivateKeyTitle', msg: 'lang.warnPrivateKey' } });
        }
      }, (err: any) => {
        this.notificationService.handleErrors(err);
      });
    if (this.cookieService.check('maarchParapheurLang')) {
      const cookieInfoLang = this.cookieService.get('maarchParapheurLang');
      translate.setDefaultLang(cookieInfoLang);
    } else {
      this.cookieService.set('maarchParapheurLang', 'fr');
      translate.setDefaultLang('fr');
    }
  }
}
