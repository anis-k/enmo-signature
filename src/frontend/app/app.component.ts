import { Component, ViewEncapsulation } from '@angular/core';
import { CookieService } from 'ngx-cookie-service';
import { SignaturesContentService } from './service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from './service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { MatDialog } from '@angular/material/dialog';
import { MatIconRegistry } from '@angular/material/icon';
import { AuthService } from './service/auth.service';
import { MenuController } from '@ionic/angular';
import { Router } from '@angular/router';
import { environment } from '../core/environments/environment';
import { Platform } from '@ionic/angular';
@Component({
    selector: 'app-root',
    templateUrl: 'app.component.html',
    encapsulation: ViewEncapsulation.None,
    styleUrls: ['app.component.scss']
})

export class AppComponent {
    isPortrait: boolean;
    debugMode: boolean;
    showLeftContent: boolean = false;
    showRightContent: boolean = false;
    constructor(private translate: TranslateService,
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public sanitizer: DomSanitizer,
        private cookieService: CookieService,
        public notificationService: NotificationService,
        public dialog: MatDialog, iconReg: MatIconRegistry,
        public authService: AuthService,
        private menu: MenuController,
        public router: Router,
        public platform: Platform
    ) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/frontend/assets/logo_white.svg'));

        if (this.cookieService.check('maarchParapheurLang')) {
            const cookieInfoLang = this.cookieService.get('maarchParapheurLang');
            translate.setDefaultLang(cookieInfoLang);
        } else {
            this.cookieService.set('maarchParapheurLang', 'fr');
            translate.setDefaultLang('fr');
        }
        if (!environment.production) {
            this.debugMode = true;
        }
    }

    test() {
        console.log(this.router.url);
        return true;
    }

    allowedRoute() {
        return ['/', '/login', '/forgot-password', '/update-password', '/password-modification'].indexOf(this.router.url) === -1;
    }
}
