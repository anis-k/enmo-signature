import { Component, Input, OnInit } from '@angular/core';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../service/auth.service';

@Component({
    selector: 'app-visa-workflow',
    templateUrl: 'visa-workflow.component.html',
    styleUrls: ['visa-workflow.component.scss'],
})
export class VisaWorkflowComponent implements OnInit {

    loading: boolean = false;

    // tslint:disable-next-line:no-input-rename
    @Input('visaWorkflow') visaWorkflow: any;

    // tslint:disable-next-line:no-input-rename
    @Input('snavRightComponent') snavRightComponent: MatSidenav;

    constructor(public http: HttpClient, public signaturesService: SignaturesContentService, public authService: AuthService) { }

    ngOnInit(): void {
        this.visaWorkflow.forEach((element: any) => {
            if (element.userPicture === undefined  && element.userDisplay !== '') {
                this.http.get('../rest/users/' + element.userId + '/picture')
                .subscribe((data: any) => {
                    element.userPicture = data.picture;
                });
            }
        });
    }
}
