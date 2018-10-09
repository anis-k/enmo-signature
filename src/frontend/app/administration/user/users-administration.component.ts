import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MAT_DIALOG_DATA, MatSidenav } from '@angular/material';

import { AutoCompletePlugin } from '../../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "users-administration.component.html",
    styleUrls: ['users-administration.component.css'],
    providers: [NotificationService]
})
export class UsersAdministrationComponent extends AutoCompletePlugin implements OnInit {

    /*RESPONSIVE*/
    private _mobileQueryListener            : () => void;
    mobileQuery                             : MediaQueryList;

    /*HEADER*/
    titleHeader                             : string;
    @ViewChild('snav') public sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight : MatSidenav;

    dialogRef                               : MatDialogRef<any>;

    coreUrl                                 : string;
    lang                                    : any                   = LANG;
    loading                                 : boolean               = false;

    data                                    : any[]                 = [];
    config                                  : any                   = {};
    userDestRedirect                        : any                   = {};
    userDestRedirectModels                  : any[]                 = [];
    quota                                   : any                   = {};
    user                                    : any                   = {};

    dataSource          = new MatTableDataSource(this.data);
    displayedColumns    = ['user_id', 'lastname', 'firstname', 'status', 'mail', 'actions'];


    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        super(http, ['users']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.users);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.user = angularGlobals.user;
        this.loading = true;

        this.http.get(this.coreUrl + 'rest/users')
            .subscribe((data: any) => {
                this.data = data['users'];
                this.quota = data['quota'];
                if (this.quota.actives > this.quota.userQuota) {
                    this.notify.error(this.lang.quotaExceeded);
                }

                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    suspendUser(user: any) {
        if (user.inDiffListDest == 'Y') {
            user.mode = 'up';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listTemplates/entityDest/itemId/' + user.user_id)
                .subscribe((data: any) => {
                    this.userDestRedirectModels = data.listTemplates;
                    this.config = { data: { userDestRedirect: this.userDestRedirect, userDestRedirectModels: this.userDestRedirectModels } };
                    this.dialogRef = this.dialog.open(UsersAdministrationRedirectModalComponent, this.config);
                    this.dialogRef.afterClosed().subscribe((result: string) => {
                        if (result) {
                            user.enabled = 'N';
                            user.redirectListModels = result;
                            //first, update listModels
                            this.http.put(this.coreUrl + 'rest/listTemplates/entityDest/itemId/' + user.user_id, user)
                                .subscribe((data: any) => {
                                    if (data.errors) {
                                        user.enabled = 'Y';
                                        this.notify.error(data.errors);
                                    } else {
                                        //then suspend user
                                        this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                                            .subscribe(() => {
                                                user.inDiffListDest = 'N';
                                                this.notify.success(this.lang.userSuspended);
                                                if (this.quota.userQuota) {
                                                    this.quota.inactives++;
                                                    this.quota.actives--;
                                                }

                                            }, (err) => {
                                                user.enabled = 'Y';
                                                this.notify.error(err.error.errors);
                                            });
                                    }
                                }, (err) => {
                                    this.notify.error(err.error.errors);
                                });
                        }
                        this.dialogRef = null;
                    });

                }, (err) => {
                    console.log(err);
                    location.href = "index.php";
                });
        } else {
            let r = confirm(this.lang.confirmAction + ' ' + this.lang.suspend + ' « ' + user.user_id + ' »');

            if (r) {
                user.enabled = 'N';
                this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe(() => {
                        this.notify.success(this.lang.userSuspended);
                        if (this.quota.userQuota) {
                            this.quota.inactives++;
                            this.quota.actives--;
                        }
                    }, (err) => {
                        user.enabled = 'Y';
                        this.notify.error(err.error.errors);
                    });
            }
        }
    }

    activateUser(user: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.authorize + ' « ' + user.user_id + ' »');

        if (r) {
            user.enabled = 'Y';
            this.http.put(this.coreUrl + 'rest/users/' + user.id, user)
                .subscribe(() => {
                    this.notify.success(this.lang.userAuthorized);
                    if (this.quota.userQuota) {
                        this.quota.inactives--;
                        this.quota.actives++;
                        if (this.quota.actives > this.quota.userQuota) {
                            this.notify.error(this.lang.quotaExceeded);
                        }
                    }
                }, (err) => {
                    user.enabled = 'N';
                    this.notify.error(err.error.errors);
                });
        }
    }

    deleteUser(user: any) {
        if (user.inDiffListDest == 'Y') {
            user.mode = 'del';
            this.userDestRedirect = user;
            this.http.get(this.coreUrl + 'rest/listTemplates/entityDest/itemId/' + user.user_id)
                .subscribe((data: any) => {
                    this.userDestRedirectModels = data.listTemplates;
                    this.config = { data: { userDestRedirect: this.userDestRedirect, userDestRedirectModels: this.userDestRedirectModels } };
                    this.dialogRef = this.dialog.open(UsersAdministrationRedirectModalComponent, this.config);
                    this.dialogRef.afterClosed().subscribe((result: string) => {
                        if (result) {
                            user.redirectListModels = result;
                            //first, update listModels
                            this.http.put(this.coreUrl + 'rest/listTemplates/entityDest/itemId/' + user.user_id, user)
                                .subscribe((data: any) => {
                                    if (data.errors) {
                                        this.notify.error(data.errors);
                                    } else {
                                        //then delete user
                                        this.http.delete(this.coreUrl + 'rest/users/' + user.id)
                                            .subscribe(() => {
                                                for (let i in this.data) {
                                                    if (this.data[i].id == user.id) {
                                                        this.data.splice(Number(i), 1);
                                                    }
                                                }
                                                this.dataSource = new MatTableDataSource(this.data);
                                                this.dataSource.paginator = this.paginator;
                                                this.dataSource.sort = this.sort;

                                                if (this.quota.userQuota && user.enabled == 'Y') {
                                                    this.quota.actives--;
                                                } else if (this.quota.userQuota && user.enabled == 'N') {
                                                    this.quota.inactives--;
                                                }

                                                this.notify.success(this.lang.userDeleted + ' « ' + user.user_id + ' »');

                                            }, (err) => {
                                                this.notify.error(err.error.errors);
                                            });
                                    }
                                }, (err) => {
                                    this.notify.error(err.error.errors);
                                });
                        }
                    });

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + user.user_id + ' »');

            if (r) {
                this.http.delete(this.coreUrl + 'rest/users/' + user.id, user)
                    .subscribe(() => {
                        for (let i in this.data) {
                            if (this.data[i].id == user.id) {
                                this.data.splice(Number(i), 1);
                            }
                        }
                        this.dataSource = new MatTableDataSource(this.data);
                        this.dataSource.paginator = this.paginator;
                        this.dataSource.sort = this.sort;
                        this.notify.success(this.lang.userDeleted);
                        if (this.quota.userQuota && user.enabled == 'Y') {
                            this.quota.actives--;
                        } else if (this.quota.userQuota && user.enabled == 'N') {
                            this.quota.inactives--;
                        }
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
        }
    }
}
@Component({
    templateUrl: "users-administration-redirect-modal.component.html",
    styles: [".mat-dialog-content{height:260px;max-height: 65vh;}"]
})
export class UsersAdministrationRedirectModalComponent extends AutoCompletePlugin {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<UsersAdministrationRedirectModalComponent>) {
        super(http, ['users']);
    }
    sendFunction() {
        var valid = true;
        this.data.userDestRedirectModels.each(function (element: any) {
            if (!element.redirectUserId) {
                valid = false;
            }
        });

        return valid;
    }
}
