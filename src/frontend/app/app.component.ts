import { Component, ViewEncapsulation } from '@angular/core';
import { CookieService } from 'ngx-cookie-service';
import { empty } from 'rxjs';
import { SignaturesContentService } from './service/signatures.service';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  encapsulation: ViewEncapsulation.None,
  styleUrls: ['app.component.styl']
})

export class AppComponent {

  constructor(public signaturesService: SignaturesContentService, private cookieService: CookieService) {
    if (this.cookieService.check( 'maarchParafUserId')) {
      this.signaturesService.userLogged.id = this.cookieService.get( 'maarchParafUserId');
      this.signaturesService.userLogged.firstname = this.cookieService.get( 'maarchParafUserFirstname');
      this.signaturesService.userLogged.lastname = this.cookieService.get( 'maarchParafUserLastname');
      this.signaturesService.userLogged.mail = this.cookieService.get( 'maarchParafUserMail');
    }
  }
}
