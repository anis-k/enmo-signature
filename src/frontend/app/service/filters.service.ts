import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { SignaturesContentService } from '../service/signatures.service';
import { Router } from '@angular/router';

@Injectable()
export class FiltersService {

    currentIndex: number = 0;
    offset: number = 0;
    limit: number = 10;

    constructor(
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        private router: Router
    ) { }

    resfreshDocuments(redirect: boolean = false) {
        const limit = this.limit + this.offset;

        this.http.get('../rest/documents?limit=' + limit + '&offset=0' + '&mode=' + this.signaturesService.mode)
            .subscribe((data: any) => {
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount = data.count;
                if (redirect) {
                    if (this.signaturesService.documentsList[this.currentIndex] !== undefined) {
                        this.router.navigate(['/documents/' + this.signaturesService.documentsList[this.currentIndex].id]);
                    } else if (this.signaturesService.documentsList.length > 0) {
                        this.router.navigate(['/documents/' + this.signaturesService.documentsList[0].id]);
                    } else {
                        this.router.navigate(['/home']);
                    }
                }
            });
    }
}
