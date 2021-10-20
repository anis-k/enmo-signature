import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../../service/signatures.service';
import { NotificationService } from '../../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';
import { finalize } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { AuthService } from '../../../service/auth.service';
import { AlertController } from '@ionic/angular';
import { Sort } from '@angular/material/sort';


export interface Ldap {
    id: number;
    label: string;
}

@Component({
    selector: 'app-administration-ldap-list',
    templateUrl: 'ldap-list.component.html',
    styleUrls: ['../../administration.scss', 'ldap-list.component.scss'],
})

export class LdapListComponent implements OnInit {

    ldapList: Ldap[] = [];
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

        this.displayedColumns = ['label', 'actions'];
    }

    updateDataTable() {
        this.sortedData = this.ldapList.slice();
    }

    applyFilter(filterValue: string) {
        filterValue = this.latinisePipe.transform(filterValue.toLowerCase());

        this.sortedData = this.ldapList.filter(
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

    ngOnInit(): void { }

    ionViewWillEnter() {
        this.http.get('../rest/configurations',
            {
                params: {
                    identifier: 'ldapServer'
                }
            })
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: (data: any) => {
                    this.ldapList = data.configurations;
                    this.updateDataTable();
                },
            });
    }


    async delete(ldapToDelete: Ldap) {
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
                        this.http.delete('../rest/configurations/' + ldapToDelete.id)
                            .pipe(
                                finalize(() => this.loading = false)
                            )
                            .subscribe({
                                next: () => {
                                    const indexToDelete = this.ldapList.findIndex(ldap => ldap.id === ldapToDelete.id);

                                    this.ldapList.splice(indexToDelete, 1);

                                    this.updateDataTable();

                                    this.notificationService.success('lang.ldapDeleted');

                                },
                            });
                    }
                }
            ]
        });
        await alert.present();
    }

    sortData(sort: Sort) {
        const data = this.ldapList.slice();
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
