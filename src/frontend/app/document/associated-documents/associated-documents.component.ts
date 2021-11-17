import { Component, Input, OnInit, Output, EventEmitter, ViewChild } from '@angular/core';
import { Router } from '@angular/router';

@Component({
    selector: 'app-associated-documents',
    templateUrl: 'associated-documents.component.html',
    styleUrls: ['associated-documents.component.scss'],
})
export class AssociatedDocumentsComponent implements OnInit {

    @Input() associatedDocuments: any;

    constructor(
        public router: Router
    ) { }

    ngOnInit(): void {
    }

    goTo(id: number) {
        this.router.navigate([`/documents/${id}`]);
    }
}
