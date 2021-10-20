import { Component, Input, OnInit } from '@angular/core';
import { FunctionsService } from '../../service/functions.service';
import { SignaturesContentService } from '../../service/signatures.service';

@Component({
    selector: 'app-main-document-detail',
    templateUrl: 'main-document-detail.component.html',
    styleUrls: ['main-document-detail.component.scss'],
})
export class MainDocumentDetailComponent implements OnInit {

    @Input() mainDocument: any;

    loading: boolean = false;

    constructor(
        public signaturesService: SignaturesContentService,
        public functionsService: FunctionsService,
    ) { }

    ngOnInit(): void { }

}
