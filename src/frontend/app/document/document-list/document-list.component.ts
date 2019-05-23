import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../service/notification.service';

@Component({
    selector: 'app-document-list',
    templateUrl: 'document-list.component.html',
    styleUrls: ['document-list.component.scss'],
})
export class DocumentListComponent implements OnInit {

    loading: boolean = false;

    attachments: any =  [];

    constructor(public http: HttpClient, public notificationService: NotificationService) { }

    ngOnInit(): void { }

    loadDocumentList(mainDocid: number) {
        if (this.attachments.length === 0 || mainDocid !== this.attachments[0].id) {
            this.attachments =  [{
                'id': 1408,
                'main' : true,
                'filename': '0024_878476521.txt',
                'thumbnailUrl': 'http://10.2.95.136/maarch_courrier_develop/rest/res/1529/attachments/1408/thumbnail'
            }, {
                'id': 1407,
                'main' : false,
                'filename': '0024_878476521.txt',
                'thumbnailUrl': 'http://10.2.95.136/maarch_courrier_develop/rest/res/1529/attachments/1408/thumbnail'
            }, {
                'id': 1406,
                'main' : false,
                'filename': '0023_1165105382.txt',
                'thumbnailUrl': 'http://10.2.95.136/maarch_courrier_develop/rest/res/1529/attachments/1408/thumbnail'
            }];
        }
    }
}
