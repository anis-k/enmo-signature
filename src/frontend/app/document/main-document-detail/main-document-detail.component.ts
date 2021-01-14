import { Component, Input, OnInit } from '@angular/core';
import { FunctionsService } from '../../service/functions.service';
import { SignaturesContentService } from '../../service/signatures.service';

@Component({
    selector: 'app-main-document-detail',
    templateUrl: 'main-document-detail.component.html',
    styleUrls: ['main-document-detail.component.scss'],
})
export class MainDocumentDetailComponent implements OnInit {

    loading: boolean = false;

    @Input() mainDocument: any;

    constructor(
        public signaturesService: SignaturesContentService,
        public functionsService: FunctionsService,
    ) { }

    ngOnInit(): void { }

}
