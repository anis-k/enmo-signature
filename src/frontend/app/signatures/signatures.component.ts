import { Component, OnInit } from '@angular/core';
import { MatBottomSheet } from '@angular/material';
import { SignaturesContentService } from '../service/signatures.service';
// TEMP : A effacer une fois l'api en place
import { DomSanitizer } from '@angular/platform-browser';
import * as $ from 'jquery';
import { HttpClient } from '@angular/common/http';
import { trigger, transition, style, animate, stagger, query } from '@angular/animations';
import { NotificationService } from '../service/notification.service';

@Component({
  selector: 'app-signatures',
  templateUrl: 'signatures.component.html',
  styleUrls: ['signatures.component.scss'],
  animations: [
    trigger('listAnimation', [
      transition('* => *', [ // each time the binding value changes
        query(':enter', [
          style({ opacity: 0 }),
          stagger(100, [
            animate('0.5s', style({ opacity: 1 }))
          ])
        ], { optional: true })
      ])
    ])
  ],
})
export class SignaturesComponent implements OnInit {

  inAllPage = false;
  count = 0;

  constructor(public http: HttpClient, public signaturesService: SignaturesContentService, private bottomSheetRef: MatBottomSheet,
     private sanitization: DomSanitizer, public notificationService: NotificationService) {
  }
  ngOnInit() {
    // TO DO IMPLEMENT ROUTE SIGNATURES USER LIST

  }

  openSignatures() {
    this.signaturesService.showSign = true;
  }

  closeSignatures() {
    this.signaturesService.showSign = false;
  }

  openPad() {
    this.signaturesService.showPad = true;
    this.closeSignatures();
  }

  reloadSignatures() {
    this.signaturesService.signaturesList.unshift(
        {
          id : this.signaturesService.newSign.id,
          encodedSignature: this.signaturesService.newSign.encodedSignature
        }
      );
      this.signaturesService.newSign = {};
  }

  showAnnotation() {
    this.signaturesService.annotationMode = true;
    this.signaturesService.lockNote = true;
    this.bottomSheetRef.dismiss();
  }

  selectSignature(signature: any) {
    signature.positionX = this.signaturesService.workingAreaWidth - 140;
    signature.positionY = this.signaturesService.workingAreaHeight - 140;
    signature.pdfAreaX = this.signaturesService.workingAreaWidth;
    signature.pdfAreaY = this.signaturesService.workingAreaHeight;
    if (this.inAllPage) {
      for (let index = 1; index <= this.signaturesService.totalPage; index++) {
        if (!this.signaturesService.signaturesContent[index]) {
          this.signaturesService.signaturesContent[index] = [];
        }
        this.signaturesService.signaturesContent[index].push(JSON.parse(JSON.stringify(signature)));
      }
    } else {
      if (!this.signaturesService.signaturesContent[this.signaturesService.currentPage]) {
        this.signaturesService.signaturesContent[this.signaturesService.currentPage] = [];
      }
      this.signaturesService.signaturesContent[this.signaturesService.currentPage].push(JSON.parse(JSON.stringify(signature)));
    }
    $('.mat-sidenav-content').animate({ scrollTop: $(document).height() }, 1000);
    $('.mat-sidenav-content').animate({ scrollLeft: $(document).width() }, 1000);
    this.bottomSheetRef.dismiss();
  }

  removeSignature(signature: any, i: any) {
    this.http.delete('../rest/users/ ' + '1' + '/signatures/' + signature.id)
      .subscribe(() => {
          this.signaturesService.signaturesList.splice(i, 1);
          this.notificationService.success('Signature supprimée');
          this.bottomSheetRef.dismiss();
      }, () => {
          console.log('error !');
      });
  }

  toggleAllPage() {
    this.inAllPage = !this.inAllPage;
  }

  tapEvent(signature: any, i: any) {
    this.count++;
    $('[class*=remove_icon_]').hide();
    $('.remove_icon_' + i).show();
    setTimeout(() => {
      if (this.count === 1) {
        this.count = 0;
      } else if (this.count > 1) {
        this.count = 0;
        this.selectSignature(signature);
      }
    }, 250);
  }
}
