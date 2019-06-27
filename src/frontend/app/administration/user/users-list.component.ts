import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatTableDataSource, MatPaginator, MatSort, MatDialog } from '@angular/material';
import { ConfirmComponent } from '../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { map, tap, finalize } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { AuthService } from '../../service/auth.service';


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

export class UsersListComponent implements OnInit {

    userList: User[] = [];
    dataSource: MatTableDataSource<User>;
    displayedColumns: string[];
    loading: boolean = true;

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    constructor(public http: HttpClient, private translate: TranslateService, private latinisePipe: LatinisePipe, public dialog: MatDialog, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public authService: AuthService) {
        if (this.signaturesService.smartphoneMode) {
            this.displayedColumns = ['firstname', 'lastname', 'actions'];
        } else {
            this.displayedColumns = ['firstname', 'lastname', 'email', 'actions'];
        }
        this.dataSource = new MatTableDataSource(this.userList);
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
        this.dataSource.data = this.userList;
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
        this.http.get('../rest/users' + '?mode=all')
            .pipe(
                map((data: any) => data.users),
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: data => {
                    this.userList = data;
                    this.updateDataTable();
                },
            });
    }


    delete(userToDelete: User) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: '', title: 'lang.confirmMsg', msg: '' } });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
                this.http.delete('../rest/users/' + userToDelete.id)
                    .pipe(
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: data => {
                            const indexToDelete = this.userList.findIndex(user => user.id === userToDelete.id);

                            this.userList.splice(indexToDelete, 1);

                            this.updateDataTable();

                            this.notificationService.success('lang.userDeleted');

                        },
                    });
            }
        });
    }
}
