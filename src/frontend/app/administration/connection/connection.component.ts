import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { finalize } from 'rxjs/operators';
import { AuthService } from '../../service/auth.service';
import { AlertController } from '@ionic/angular';


export interface Privilege {
    id: string;
    icon: string;
    route: string;
}

export interface Connection {
    id: string;
    allowed: boolean;
}

@Component({
    selector: 'app-administration-connection',
    templateUrl: 'connection.component.html',
    styleUrls: ['../administration.scss', 'connection.component.scss'],
})

export class ConnectionComponent implements OnInit {
    loading: boolean = true;
    privileges: Privilege[] = [
        {
            id: 'manage_ldap_configurations',
            icon: 'server-sharp',
            route: '/administration/connections/ldaps'
        }
    ];
    id: number = 0;
    label: string = '';
    connectionList: Connection[] = [];

    currentConnection: Connection;

    constructor(
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public alertController: AlertController,
        public authService: AuthService
    ) { }

    ngOnInit(): void {
        this.loading = true;
        this.http.get('../rest/configurations',
            {
                params: {
                    identifier: 'connection'
                }
            })
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: (data: any) => {
                    this.label = data.configurations.label;
                    this.id = data.configurations.id;
                    this.connectionList = data.configurations.availableConnections;
                    const activeIndex = this.connectionList.findIndex(conn => conn.id === data.configurations.value);
                    if (this.connectionList[activeIndex] !== undefined) {
                        this.currentConnection = this.connectionList[activeIndex];
                    } else {
                        this.currentConnection = this.connectionList[0];
                    }
                },
            });
    }

    async changeConnection(connection: any) {
        this.http.patch('../rest/configurations/' + this.id, { label: this.label, value: connection })
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    this.authService.authMode = connection;
                    this.notificationService.success('lang.connectionModeUpdated');
                },
            });
    }
}
