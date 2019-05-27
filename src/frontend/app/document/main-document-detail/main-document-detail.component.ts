import { Component, Input, OnInit } from '@angular/core';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../../service/signatures.service';

@Component({
    selector: 'app-main-document-detail',
    templateUrl: 'main-document-detail.component.html',
    styleUrls: ['main-document-detail.component.scss'],
})
export class MainDocumentDetailComponent implements OnInit {

    loading: boolean = false;

    // tslint:disable-next-line:no-input-rename
    @Input('mainDocument') mainDocument: any;

    // tslint:disable-next-line:no-input-rename
    @Input('snavRightComponent') snavRightComponent: MatSidenav;

    constructor(public signaturesService: SignaturesContentService) { }

    ngOnInit(): void { }

}
