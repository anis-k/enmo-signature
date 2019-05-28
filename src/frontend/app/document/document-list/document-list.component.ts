import { Component, Input, OnInit, Output, EventEmitter } from '@angular/core';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../../service/signatures.service';

@Component({
    selector: 'app-document-list',
    templateUrl: 'document-list.component.html',
    styleUrls: ['document-list.component.scss'],
})
export class DocumentListComponent implements OnInit {

    loading: boolean = false;

    // tslint:disable-next-line:no-input-rename
    @Input('docList') docList: any;
    // tslint:disable-next-line:no-input-rename
    @Input('currentDocId') currentDocId: any;
    // tslint:disable-next-line:no-input-rename
    @Input('snavRightComponent') snavRightComponent: MatSidenav;

    @Output() triggerEvent = new EventEmitter<string>();

    constructor(public signaturesService: SignaturesContentService) { }

    ngOnInit(): void { }


    loadDoc(id: string) {
        this.triggerEvent.emit(id);
        if (this.signaturesService.mobileMode) {
            this.snavRightComponent.close();
        }
    }
}
