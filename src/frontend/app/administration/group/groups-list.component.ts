import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatTableDataSource, MatPaginator, MatSort, MatDialog } from '@angular/material';
import { ConfirmComponent } from '../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { map, tap, finalize } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';


export interface Group {
    id: string;
    label: string;
}

@Component({
    selector: 'app-administration-groups-list',
    templateUrl: 'groups-list.component.html',
    styleUrls: ['../administration.scss', 'groups-list.component.scss'],
})

export class GroupsListComponent implements OnInit {

    groupList: Group[] = [];
    dataSource: MatTableDataSource<Group>;
    displayedColumns: string[];
    loading: boolean = true;

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    constructor(public http: HttpClient, private translate: TranslateService, private latinisePipe: LatinisePipe, public dialog: MatDialog, public signaturesService: SignaturesContentService, public notificationService: NotificationService) {
        this.displayedColumns = ['label', 'actions'];
        this.dataSource = new MatTableDataSource(this.groupList);
        this.dataSource.filterPredicate = (data, filter) => {
            let state = false;
            this.displayedColumns.forEach(column => {
                if (data[column] !== undefined) {
                    const cleanData = this.latinisePipe.transform(data[column].trim().toLowerCase());
                    const cleanFilter = this.latinisePipe.transform(filter.trim().toLowerCase());
                    if (cleanData.indexOf(cleanFilter) !== -1) {
                        state = true;
                    }
                }
            });
            return state;
        };
    }

    updateDataTable() {
        this.dataSource.data = this.groupList;
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
    }

    applyFilter(filterValue: string) {
        this.dataSource.filter = filterValue;

        if (this.dataSource.paginator) {
            this.dataSource.paginator.firstPage();
        }
    }

    ngOnInit(): void {
        this.http.get('../rest/groups')
            .pipe(
                map((data: any) => data.groups),
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: data => {
                    this.groupList = data;
                    this.updateDataTable();
                },
            });
    }


    delete(groupToDelete: Group) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: '', title: 'lang.confirmMsg', msg: '' } });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
                this.http.delete('../rest/groups/' + groupToDelete.id)
                    .pipe(
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: data => {
                            const indexToDelete = this.groupList.findIndex(group => group.id === groupToDelete.id);

                            this.groupList.splice(indexToDelete, 1);

                            this.updateDataTable();

                            this.notificationService.success('lang.groupDeleted');

                        },
                    });
            }
        });
    }
}
