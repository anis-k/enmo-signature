import { Component, Input, OnInit, Output, EventEmitter } from '@angular/core';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';

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

    constructor(public http: HttpClient, public signaturesService: SignaturesContentService, private sanitizer: DomSanitizer) { }

    ngOnInit(): void {
        this.docList.forEach((element: any, index: number) => {
            if (element.imgContent[1] === undefined && index > 0 ) {
                this.http.get('../rest/attachments/' + element.id + '/thumbnails/1')
                .subscribe((data: any) => {
                    element.imgContent[1] = data.fileContent;
                });
            }
        });
    }

    loadDoc(id: string) {
        this.triggerEvent.emit(id);
        if (this.signaturesService.mobileMode) {
            this.snavRightComponent.close();
        }
    }
}
