import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatTableDataSource, MatPaginator, MatSort, MatDialog } from '@angular/material';
import { ConfirmComponent } from '../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { map, tap, finalize } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';


export interface Ldap {
    id: string;
    label: string;
    description: string;
}

@Component({
    selector: 'app-administration-ldap-list',
    templateUrl: 'ldap-list.component.html',
    styleUrls: ['../administration.scss', 'ldap-list.component.scss'],
})

export class LdapListComponent implements OnInit {

    ldapList: Ldap[] = [];
    dataSource: MatTableDataSource<Ldap>;
    displayedColumns: string[];
    loading: boolean = true;

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    constructor(public http: HttpClient, private translate: TranslateService, private latinisePipe: LatinisePipe, public dialog: MatDialog, public signaturesService: SignaturesContentService, public notificationService: NotificationService) {

        this.displayedColumns = ['label', 'description', 'actions'];

        this.dataSource = new MatTableDataSource(this.ldapList);
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
        this.dataSource.data = this.ldapList;
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
        this.ldapList = [
            {
                id: '1',
                label: 'ldap service courrier',
                description: 'ldap principal'
            }
        ];
        this.updateDataTable();
        this.loading = false;
        /*this.http.get('../rest/ldap')
            .pipe(
                map((data: any) => data.users),
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: data => {
                    this.ldapList = data;
                    this.updateDataTable();
                },
            });*/
    }


    delete(ldapToDelete: Ldap) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
                this.http.delete('../rest/ldap/' + ldapToDelete.id)
                    .pipe(
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: data => {
                            const indexToDelete = this.ldapList.findIndex(user => user.id === ldapToDelete.id);

                            this.ldapList.splice(indexToDelete, 1);

                            this.updateDataTable();

                            this.notificationService.success('lang.ldapDeleted');

                        },
                    });
            }
        });
    }
}
