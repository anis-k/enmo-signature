import { Component, ViewEncapsulation } from '@angular/core';
import { CookieService } from 'ngx-cookie-service';
import { SignaturesContentService } from './service/signatures.service';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  encapsulation: ViewEncapsulation.None,
  styleUrls: ['app.component.scss']
})

export class AppComponent {

  constructor(public signaturesService: SignaturesContentService, private cookieService: CookieService) {

    if (this.cookieService.check( 'maarchParapheurAuth')) {
      const cookieInfo = JSON.parse(atob(this.cookieService.get('maarchParapheurAuth')));
      this.signaturesService.userLogged = cookieInfo;
    }
  }
}
