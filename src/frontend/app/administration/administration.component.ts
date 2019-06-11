import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { HttpClient } from '@angular/common/http';

export interface Privilege {
    title: string;
    description: string;
    icon: string;
    route: string;
}

@Component({
    selector: 'app-administration',
    templateUrl: 'administration.component.html',
    styleUrls: ['administration.component.scss'],
})

export class AdministrationComponent implements OnInit {

    privileges: Privilege[] = [];

    constructor(public http: HttpClient, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    ngOnInit(): void {
        this.http.get('../rest/administrativePrivileges')
            .subscribe((data: any) => {
                data.privileges.forEach((element: any) => {
                    this.privileges.push({
                        title: element.id,
                        description: element.id + 'Desc',
                        icon: element.icon,
                        route: element.route
                    }
                    );
                });
                console.log(this.privileges);
            }, (err) => {
                this.notificationService.handleErrors(err);
            });
    }
}
