import { Component, Input, OnInit, Output, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../service/notification.service';
import { MatSidenav } from '@angular/material';

@Component({
    selector: 'app-document-list',
    templateUrl: 'document-list.component.html',
    styleUrls: ['document-list.component.scss'],
})
export class DocumentListComponent implements OnInit {

    loading: boolean = false;

    // tslint:disable-next-line:no-input-rename
    @Input('mainDocument') mainDocument: any;
    // tslint:disable-next-line:no-input-rename
    @Input('currentDocId') currentDocId: any;
    // tslint:disable-next-line:no-input-rename
    @Input('snavRightComponent') snavRightComponent: MatSidenav;

    @Output() triggerEvent = new EventEmitter<string>();

    constructor(public http: HttpClient, public notificationService: NotificationService) { }

    ngOnInit(): void { }


    loadDoc(id: string) {
        this.triggerEvent.emit(id);
        this.snavRightComponent.close();
    }
}
