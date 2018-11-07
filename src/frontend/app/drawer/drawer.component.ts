import { Component } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
  selector: 'app-drawer',
  templateUrl: 'drawer.component.html',
  styleUrls: ['drawer.component.styl']
})
export class DrawerComponent {

  constructor(public signaturesService: SignaturesContentService) { }

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
