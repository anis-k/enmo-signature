import { Component, OnInit, Input } from '@angular/core';
import { DocumentNotePadComponent } from '../documentNotePad/document-note-pad.component';

@Component({
    selector: 'app-overlay',
    templateUrl: 'overlay.component.html',
    styleUrls: ['overlay.component.scss'],
})
export class OverlayComponent implements OnInit {


    // tslint:disable-next-line:no-input-rename
    @Input('appDocumentNotePad') appDocumentNotePad: DocumentNotePadComponent;

    constructor() { }

    ngOnInit(): void { }
}
