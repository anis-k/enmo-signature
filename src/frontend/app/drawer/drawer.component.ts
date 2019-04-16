import { Component } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-drawer',
  templateUrl: 'drawer.component.html',
  styleUrls: ['drawer.component.scss']
})
export class DrawerComponent {

  constructor(private translate: TranslateService, public signaturesService: SignaturesContentService) { }

  openDrawer() {
    this.signaturesService.showSign = true;
    this.signaturesService.showDrawer = true;
  }

  closeDrawer() {
    this.signaturesService.showDrawer = false;
    this.signaturesService.showPad = false;
    this.signaturesService.showSign = false;
  }
}
