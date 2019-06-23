import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { map, tap, finalize } from 'rxjs/operators';

export interface Privilege {
    id: string;
    icon: string;
    route: string;
}

@Component({
    selector: 'app-administration',
    templateUrl: 'administration.component.html',
    styleUrls: ['../administration.scss', 'administration.component.scss'],
})

export class AdministrationComponent implements OnInit {

    loading: boolean = true;
    privileges: Privilege[] = [];

    constructor(public http: HttpClient, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    ngOnInit(): void {
        this.http.get('../rest/administrativePrivileges')
        .pipe(
            map((data: any) => data.privileges),
            tap(() => this.loading = true),
            finalize(() => this.loading = false)
        )
        .subscribe({
            next: data => this.privileges = data,
        });
    }
}
