import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { MatBottomSheetRef } from '@angular/material';
import { TranslateService } from '@ngx-translate/core';

@Component({
    templateUrl: '../modal/reject-info.html',
    styleUrls: ['../modal/reject-info.scss']
})
export class RejectInfoBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(private translate: TranslateService, private router: Router, public signaturesService: SignaturesContentService, private bottomSheetRef: MatBottomSheetRef<RejectInfoBottomSheetComponent>) { }
    ngOnInit(): void {
        setTimeout(() => {
            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList]) {
                this.router.navigate(['/documents/' + this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].id]);
            } else {
                this.router.navigate(['/documents']);
            }
            this.bottomSheetRef.dismiss();
        }, 2000);
     }
}
