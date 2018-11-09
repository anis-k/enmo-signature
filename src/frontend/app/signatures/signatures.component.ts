import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import { MatBottomSheet, MatBottomSheetRef } from '@angular/material';
import { SignaturesContentService } from '../service/signatures.service';
// TEMP : A effacer une fois l'api en place
import { DomSanitizer } from '@angular/platform-browser';
import * as $ from 'jquery';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-signatures',
  templateUrl: 'signatures.component.html',
  styleUrls: ['signatures.component.styl']
})
export class SignaturesComponent implements OnInit {

  inAllPage = false;

  constructor(public http: HttpClient, public signaturesService: SignaturesContentService, private bottomSheetRef: MatBottomSheet,
     private sanitization: DomSanitizer) {
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
          encodedSignature: localStorage.getItem('signature').replace(/\"/gi, '')
        }
      );
      console.log(this.signaturesService.signaturesList);
  }

  showAnnotation() {
    this.signaturesService.annotationMode = true;
    this.signaturesService.lockNote = true;
    this.bottomSheetRef.dismiss();
  }

  selectSignature(signature: any) {
    signature.positionX = $('.pdf-page-canvas').width() - 140;
    signature.positionY = $('.pdf-page-canvas').height() - 140;
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
    this.bottomSheetRef.dismiss();
  }

  toggleAllPage() {
    this.inAllPage = !this.inAllPage;
  }
}
