import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort, Sort } from '@angular/material/sort';
import { map, finalize } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { ConfirmComponent } from '../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../../service/auth.service';
import { AlertController, ModalController, PopoverController } from '@ionic/angular';
import { UsersComponent } from './list/users.component';


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
    displayedColumns: string[];
    usersList: any[];
    sortedData: any[];

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        private route: ActivatedRoute,
        private router: Router,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public dialog: MatDialog,
        public authService: AuthService,
        public popoverController: PopoverController,
        public modalController: ModalController,
        public alertController: AlertController
    ) {
        this.displayedColumns = ['firstname', 'lastname', 'actions'];
        this.group = {
            id: '',
            label: '',
            users: [],
            privileges: []
        };
        this.groupClone = JSON.parse(JSON.stringify(this.group));
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
                        finalize(() => {
                            this.loading = false;
                        })
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
        this.sortedData = this.group.users.slice();
    }

    async openUserList(ev: any) {
        const modal = await this.modalController.create({
            component: UsersComponent,
            componentProps: {
                'users': this.group.users
            }
        });
        await modal.present();
        const { data } = await modal.onWillDismiss();
        if (data !== undefined) {
            this.linkUser(data);
        }
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

    async unlinkUser(userToDelete: any) {
        if (userToDelete.id === this.authService.user.id) {
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
                            this.deleteUser(userToDelete);
                        }
                    }
                ]
            });

            await alert.present();

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

    async deleteGroup() {
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
                        this.http.delete('../rest/groups/' + this.group.id)
                            .subscribe({
                                next: () => {
                                    this.router.navigate(['/administration/groups']);
                                    this.notificationService.success('lang.groupDeleted');
                                },
                            });
                    }
                }
            ]
        });

        await alert.present();
    }

    async togglePrivilege(privilege: any, toggle: boolean) {
        if (privilege.id === 'manage_groups' && privilege.checked) {
            if (!toggle) {
                privilege.checked = !privilege.checked;
            }
            const alert = await this.alertController.create({
                header: this.translate.instant('lang.confirmMsg'),
                message: this.translate.instant('lang.groupWarnMsg'),
                buttons: [
                    {
                        text: this.translate.instant('lang.no'),
                        role: 'cancel',
                        cssClass: 'secondary',
                        handler: () => {
                            privilege.checked = !privilege.checked;
                        }
                    },
                    {
                        text: this.translate.instant('lang.yes'),
                        handler: () => {
                            this.updatePrivilege(privilege);
                        }
                    }
                ]
            });
            await alert.present();
        } else {
            if (!toggle) {
                privilege.checked = !privilege.checked;
            }
            setTimeout(() => {
                this.updatePrivilege(privilege);
            }, 200);
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

    sortData(sort: Sort) {
        const data = this.group.users.slice();
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
