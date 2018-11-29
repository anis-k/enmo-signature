import { Component, OnInit, Input, ElementRef, EventEmitter, Output } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { DomSanitizer } from '@angular/platform-browser';


@Component({
    selector: 'app-document-note-list',
    templateUrl: 'document-note-list.component.html',
})
export class DocumentNoteListComponent implements OnInit {


    @Input() canvas: ElementRef;
    @Input() scale: number;
    @Output() triggerEvent = new EventEmitter<string>();

    constructor(private sanitization: DomSanitizer, public signaturesService: SignaturesContentService) { }

    ngOnInit(): void { }

    triggerAddnnotation (event: any) {
        this.triggerEvent.emit(event);
    }
}
