import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../../service/signatures.service';
import { NotificationService } from '../../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatTableDataSource, MatPaginator, MatSort, MatDialog } from '@angular/material';
import { ConfirmComponent } from '../../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { map, tap, finalize } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { AuthService } from '../../../service/auth.service';


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
    dataSource: MatTableDataSource<Ldap>;
    displayedColumns: string[];
    loading: boolean = true;

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    constructor(public http: HttpClient, private translate: TranslateService, private latinisePipe: LatinisePipe, public dialog: MatDialog, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public authService: AuthService) {

        this.displayedColumns = ['label', 'actions'];

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
        this.loading = false;
    }


    delete(ldapToDelete: Ldap) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: '', title: 'lang.confirmMsg', msg: '' } });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
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
        });
    }
}
