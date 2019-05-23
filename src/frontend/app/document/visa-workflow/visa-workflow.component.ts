import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../service/notification.service';

@Component({
    selector: 'app-visa-workflow',
    templateUrl: 'visa-workflow.component.html',
    styleUrls: ['visa-workflow.component.scss'],
})
export class VisaWorkflowComponent implements OnInit {

    loading: boolean = false;

    @Input('visaWorkflow') visaWorkflow: any;

    constructor(public http: HttpClient, public notificationService: NotificationService) { }

    ngOnInit(): void { }
}
