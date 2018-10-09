import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MAT_DIALOG_DATA, MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "groups-administration.component.html",
    providers: [NotificationService]
})
export class GroupsAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;
    
    dialogRef                       : MatDialogRef<any>;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    config                          : any       = {};
    groups                          : any[]     = [];
    groupsForAssign                 : any[]     = [];


    displayedColumns    = ['group_id', 'group_desc', 'actions'];
    dataSource          = new MatTableDataSource(this.groups);


    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher,public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.groups);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + "rest/groups")
            .subscribe((data: any) => {
                this.groups = data['groups'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.groups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    preDelete(group: any) {
        if (group.users.length == 0) {
            let r = confirm("Etes vous sûr de vouloir supprimer ce groupe ?");

            if (r) {
                this.deleteGroup(group);
            }
        } else {
            this.groupsForAssign = [];
            this.groups.forEach((tmpGroup) => {
                if (group.group_id != tmpGroup.group_id) {
                    this.groupsForAssign.push(tmpGroup);
                }
            });
            this.config = { data: { id: group.id, group_desc: group.group_desc, groupsForAssign: this.groupsForAssign, users: group.users } };
            this.dialogRef = this.dialog.open(GroupsAdministrationRedirectModalComponent, this.config);
            this.dialogRef.afterClosed().subscribe((result: string) => {
                if (result) {
                    if (result == "_NO_REPLACEMENT") {
                        this.deleteGroup(group);
                    } else {
                        this.http.put(this.coreUrl + "rest/groups/" + group.id + "/reassign/" + result, {})
                            .subscribe((data: any) => {
                                this.deleteGroup(group);
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                    }
                }
                this.dialogRef = null;
            });
        }

    }

    deleteGroup(group: any) {
        this.http.delete(this.coreUrl + "rest/groups/" + group['id'])
            .subscribe((data: any) => {
                setTimeout(() => {
                    this.groups = data['groups'];
                    this.dataSource = new MatTableDataSource(this.groups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
                this.notify.success(this.lang.groupDeleted);

            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}

@Component({
    templateUrl: "groups-administration-redirect-modal.component.html"
})
export class GroupsAdministrationRedirectModalComponent {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<GroupsAdministrationRedirectModalComponent>) {
    }
}