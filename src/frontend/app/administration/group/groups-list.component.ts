import { Component, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort, Sort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { TranslateService } from '@ngx-translate/core';
import { map, finalize } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { AlertController } from '@ionic/angular';


export interface Group {
    id: string;
    label: string;
}

@Component({
    selector: 'app-administration-groups-list',
    templateUrl: 'groups-list.component.html',
    styleUrls: ['../administration.scss', 'groups-list.component.scss'],
})

export class GroupsListComponent {

    groupList: Group[] = [];
    sortedData: any[];
    dataSource: MatTableDataSource<Group>;
    displayedColumns: string[];
    loading: boolean = true;

    @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: true }) sort: MatSort;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        private latinisePipe: LatinisePipe,
        public dialog: MatDialog,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public alertController: AlertController
    ) {
        this.displayedColumns = ['label', 'actions'];
    }

    applyFilter(filterValue: string) {
        filterValue = this.latinisePipe.transform(filterValue.toLowerCase());

        this.sortedData = this.groupList.filter(
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
        this.http.get('../rest/groups')
            .pipe(
                map((data: any) => data.groups),
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: data => {
                    this.groupList = data;
                    this.sortedData = this.groupList.slice();
                },
            });
    }

    async delete(groupToDelete: Group) {
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
                        this.http.delete('../rest/groups/' + groupToDelete.id)
                    .pipe(
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: data => {
                            const indexToDelete = this.groupList.findIndex(group => group.id === groupToDelete.id);

                            this.groupList.splice(indexToDelete, 1);

                            this.sortedData = this.groupList.slice();

                            this.notificationService.success('lang.groupDeleted');

                        },
                    });
                    }
                }
            ]
        });

        await alert.present();
    }

    sortData(sort: Sort) {
        const data = this.groupList.slice();
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
