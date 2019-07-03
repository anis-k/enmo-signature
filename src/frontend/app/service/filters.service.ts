import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { SignaturesContentService } from '../service/signatures.service';

@Injectable()
export class FiltersService {

    constructor(public http: HttpClient, public signaturesService: SignaturesContentService) { }

    resfreshDocuments() {
        this.http.get('../rest/documents?limit=25&offset=0&mode=')
            .subscribe((data: any) => {
                this.signaturesService.mode = '';
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount = data.count;
            });
    }
}
