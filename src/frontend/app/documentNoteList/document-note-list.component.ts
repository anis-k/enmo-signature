import { Component, OnInit, Input, ElementRef, EventEmitter, Output } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';


@Component({
    selector: 'app-document-note-list',
    templateUrl: 'document-note-list.component.html',
})
export class DocumentNoteListComponent implements OnInit {


    constructor(private translate: TranslateService, private sanitization: DomSanitizer, public signaturesService: SignaturesContentService) { }

    ngOnInit(): void { }

}
