import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { FiltersService } from '../service/filters.service';
import { AuthService } from '../service/auth.service';

@Component({
    templateUrl: '../modal/reject-info.html',
    styleUrls: ['../modal/reject-info.scss']
})
export class RejectInfoBottomSheetComponent implements OnInit {
    date: Date = new Date();
    constructor(
        public signaturesService: SignaturesContentService,
        public filtersService: FiltersService,
        public authService: AuthService,
        private bottomSheetRef: MatBottomSheetRef<RejectInfoBottomSheetComponent>
    ) { }

    ngOnInit(): void {
        setTimeout(() => {
            this.signaturesService.currentToobal = 'mainDocumentDetail';
            this.filtersService.resfreshDocuments(true);
            this.bottomSheetRef.dismiss();
        }, 2000);
    }
}
