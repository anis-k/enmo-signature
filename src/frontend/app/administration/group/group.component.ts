import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog, MatTableDataSource, MatPaginator, MatSort } from '@angular/material';
import { map, tap, finalize, startWith } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { ConfirmComponent } from '../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { AuthService } from '../../service/auth.service';


export interface Group {
    id: string;
    label: string;
    users: any[];
    privileges: any[];
}

@Component({
    selector: 'app-administration-group',
    templateUrl: 'group.component.html',
    styleUrls: ['../administration.scss', 'group.component.scss'],
})

export class GroupComponent implements OnInit {

    creationMode: boolean = true;
    loading: boolean = true;
    group: Group;
    groupClone: Group;
    title: string = '';
    dataSource: MatTableDataSource<any>;
    displayedColumns: string[];
    usersList: any[];


    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    constructor(public http: HttpClient, private translate: TranslateService, private route: ActivatedRoute, private router: Router, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog, public authService: AuthService) {
        this.displayedColumns = ['firstname', 'lastname', 'actions'];
        this.group = {
            id: '',
            label: '',
            users: [],
            privileges: []
        };
        this.dataSource = new MatTableDataSource(this.group.users);
    }

    ngOnInit(): void {
        this.route.params.subscribe((params: any) => {
            if (params['id'] === undefined) {
                this.creationMode = true;
                this.title = this.translate.instant('lang.groupCreation');
                this.loading = false;
                this.groupClone = JSON.parse(JSON.stringify(this.group));
            } else {
                this.creationMode = false;
                this.usersList = [];

                this.http.get('../rest/groups/' + params['id'])
                    .pipe(
                        map((data: any) => data.group),
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: data => {
                            this.group = data;
                            this.groupClone = JSON.parse(JSON.stringify(this.group));
                            this.title = this.group.label;
                            this.updateDataTable();
                        },
                    });

                this.http.get('../rest/users?mode=all')
                    .pipe(
                        map((data: any) => data.users)
                    )
                    .subscribe({
                        next: data => {
                            this.usersList = data;
                        }
                    });
            }
        });
    }

    updateDataTable() {
        this.dataSource.data = this.group.users;
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
    }

    canValidate() {
        if (this.group.label === this.groupClone.label) {
            return false;
        } else {
            return true;
        }
    }

    onSubmit() {
        if (this.creationMode) {
            this.createGroup();
        } else {
            this.modifyGroup();
        }
    }

    linkUser(user: any) {
        this.http.put('../rest/groups/' + this.group.id + '/users', { userId: user.id })
            .subscribe({
                next: () => {
                    this.group.users.push(user);
                    this.updateDataTable();
                    this.notificationService.success('lang.userAdded');
                },
            });
    }

    unlinkUser(userToDelete: any) {
        if (userToDelete.id === this.authService.user.id) {
            const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: 'warning', title: 'lang.confirmMsg', msg: 'lang.groupWarnMsg' } });

            dialogRef.afterClosed().subscribe(result => {
                if (result === 'yes') {
                    this.deleteUser(userToDelete);
                }
            });
        } else {
            this.deleteUser(userToDelete);
        }
    }


    deleteUser(userToDelete: any) {
        this.http.delete('../rest/groups/' + this.group.id + '/users/' + userToDelete.id, {})
            .subscribe({
                next: () => {
                    const indexToDelete = this.group.users.findIndex((user: any) => user.id === userToDelete.id);
                    this.group.users.splice(indexToDelete, 1);
                    this.updateDataTable();
                    this.notificationService.success('lang.userDeleted');
                },
            });
    }

    modifyGroup() {
        this.loading = true;
        this.http.put('../rest/groups/' + this.group.id, this.group)
            .subscribe({
                next: () => {
                    this.router.navigate(['/administration/groups']);
                    this.notificationService.success('lang.groupUpdated');
                },
            });
    }

    createGroup() {
        this.loading = true;
        this.http.post('../rest/groups', this.group)
            .subscribe({
                next: (data: any) => {
                    this.router.navigate(['/administration/groups/' + data.id]);
                    this.notificationService.success('lang.groupAdded');
                },
            });
    }

    deleteGroup() {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: '', title: 'lang.confirmMsg', msg: '' } });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
                this.http.delete('../rest/groups/' + this.group.id)
                    .subscribe({
                        next: () => {
                            this.router.navigate(['/administration/groups']);
                            this.notificationService.success('lang.groupDeleted');
                        },
                    });
            }
        });
    }

    togglePrivilege(privilege: any) {
        if (privilege.id === 'manage_groups') {
            const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, data: { mode: 'warning', title: 'lang.confirmMsg', msg: 'lang.groupWarnMsg' } });

            dialogRef.afterClosed().subscribe(result => {
                if (result === 'yes') {
                    this.updatePrivilege(privilege);
                } else {
                    privilege.checked = !privilege.checked;
                }
            });
        } else {
            this.updatePrivilege(privilege);
        }
    }

    updatePrivilege(privilege: any) {
        this.http.put('../rest/groups/' + this.group.id + '/privilege/' + privilege.id, { checked: privilege.checked })
            .subscribe({
                next: () => {
                    this.notificationService.success('lang.privilegeUpdated');
                },
            });
    }

    cancel() {
        this.router.navigate(['/administration/groups']);
    }

    isInGroup(user: any) {
        if (this.group.users.findIndex((userG: any) => userG.id === user.id) > -1) {
            return true;
        } else {
            return false;
        }
    }
}
