import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { MatBottomSheetRef } from '@angular/material';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../service/auth.service';
import { FiltersService } from '../service/filters.service';

@Component({
    templateUrl: 'success-info-valid.html',
    styleUrls: ['success-info-valid.scss']
})
export class SuccessInfoValidBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(private translate: TranslateService, private router: Router, public signaturesService: SignaturesContentService, public filtersService: FiltersService, public authService: AuthService, private bottomSheetRef: MatBottomSheetRef<SuccessInfoValidBottomSheetComponent>) { }
     ngOnInit(): void {
        setTimeout(() => {
            if (this.signaturesService.documentsList[this.signaturesService.indexDocumentsList]) {
                this.signaturesService.sideNavRigtDatas = {
                    mode : 'mainDocumentDetail',
                    width : '450px',
                    locked : false,
                };
                this.filtersService.resfreshDocuments();
                this.router.navigate(['/documents/' + this.signaturesService.documentsList[this.signaturesService.indexDocumentsList].id]);
            } else {
                this.signaturesService.sideNavRigtDatas = {
                    mode : 'mainDocumentDetail',
                    width : '450px',
                    locked : false,
                };
                this.router.navigate(['/documents']);
            }
            this.bottomSheetRef.dismiss();
        }, 2000);
     }
}
