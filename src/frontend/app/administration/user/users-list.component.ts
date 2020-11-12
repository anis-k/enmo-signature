import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { MatSort, Sort } from '@angular/material/sort';
import { TranslateService } from '@ngx-translate/core';
import { map, finalize } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { AuthService } from '../../service/auth.service';
import { AlertController } from '@ionic/angular';


export interface User {
    id: string;
    firstname: string;
    lastname: string;
    login: string;
    email: string;
    subtitute: boolean;
}

@Component({
    selector: 'app-administration-users-list',
    templateUrl: 'users-list.component.html',
    styleUrls: ['../administration.scss', 'users-list.component.scss'],
})

export class UsersListComponent {

    userList: User[] = [];
    sortedData: any[];
    displayedColumns: string[];
    loading: boolean = true;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        private latinisePipe: LatinisePipe,
        public dialog: MatDialog,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public authService: AuthService,
        public alertController: AlertController
    ) {

        if (this.signaturesService.mobileMode) {
            this.displayedColumns = ['firstname', 'lastname', 'email'];
        } else {
            this.displayedColumns = ['firstname', 'lastname', 'email', 'actions'];
        }
    }

    applyFilter(filterValue: string) {
        filterValue = this.latinisePipe.transform(filterValue.toLowerCase());

        this.sortedData = this.userList.filter(
            (option: any) => {
                let state = false;
                this.displayedColumns.forEach(element => {
                    if (option[element] && this.latinisePipe.transform(option[element].toLowerCase()).includes(filterValue)) {
                        state = true;
                    }
                });
                return state;
            }
        );
    }

    ionViewWillEnter() {
        this.http.get('../rest/users' + '?mode=all')
            .pipe(
                map((data: any) => data.users),
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: data => {
                    this.userList = data;
                    this.sortedData = this.userList.slice();
                },
            });
    }


    async delete(userToDelete: User) {
        const alert = await this.alertController.create({
            // cssClass: 'custom-alert-danger',
            header: this.translate.instant('lang.confirmMsg'),
            buttons: [
                {
                    text: this.translate.instant('lang.no'),
                    role: 'cancel',
                    cssClass: 'secondary',
                    handler: () => { }
                },
                {
                    text: this.translate.instant('lang.yes'),
                    handler: () => {
                        this.http.delete('../rest/users/' + userToDelete.id)
                            .pipe(
                                finalize(() => this.loading = false)
                            )
                            .subscribe({
                                next: data => {
                                    const indexToDelete = this.userList.findIndex(user => user.id === userToDelete.id);

                                    this.userList.splice(indexToDelete, 1);

                                    this.sortedData = this.userList.slice();

                                    this.notificationService.success('lang.userDeleted');

                                },
                            });
                    }
                }
            ]
        });

        await alert.present();
    }

    sortData(sort: Sort) {
        const data = this.userList.slice();
        if (!sort.active || sort.direction === '') {
            this.sortedData = data;
            return;
        }

        this.sortedData = data.sort((a, b) => {
            const isAsc = sort.direction === 'asc';
            return compare(a[sort.active], b[sort.active], isAsc);
        });
    }


}

function compare(a: number | string, b: number | string, isAsc: boolean) {
    return (a < b ? -1 : 1) * (isAsc ? 1 : -1);
}
