import { Component, Input, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';

@Component({
    selector: 'app-main-document-detail',
    templateUrl: 'main-document-detail.component.html',
    styleUrls: ['main-document-detail.component.scss'],
})
export class MainDocumentDetailComponent implements OnInit {

    loading: boolean = false;

    @Input() mainDocument: any;

    constructor(public signaturesService: SignaturesContentService) { }

    ngOnInit(): void { }

}
