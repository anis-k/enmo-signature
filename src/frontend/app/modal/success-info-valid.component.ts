import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { AuthService } from '../service/auth.service';
import { FiltersService } from '../service/filters.service';

@Component({
    templateUrl: 'success-info-valid.html',
    styleUrls: ['success-info-valid.scss']
})
export class SuccessInfoValidBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(
        public signaturesService: SignaturesContentService,
        public filtersService: FiltersService,
        public authService: AuthService,
        private bottomSheetRef: MatBottomSheetRef<SuccessInfoValidBottomSheetComponent>
    ) { }

    ngOnInit(): void {
        setTimeout(() => {
            this.signaturesService.currentToobal = 'mainDocumentDetail';
            this.filtersService.resfreshDocuments(true);
            this.bottomSheetRef.dismiss();
        }, 2000);
    }
}
