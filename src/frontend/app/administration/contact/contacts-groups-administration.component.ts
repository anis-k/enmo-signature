import { ChangeDetectorRef, Component, ViewChild, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA, MatSidenav } from '@angular/material';


declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "contacts-groups-administration.component.html",
    providers: [NotificationService]
})

export class ContactsGroupsAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;
    search: string = null;

    contactsGroups: any[] = [];
    titles: any[] = [];

    loading: boolean = false;

    displayedColumns = ['label', 'description', 'nbContacts', 'public', 'owner', 'actions',];
    dataSource = new MatTableDataSource(this.contactsGroups);
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.contactsGroups);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/contactsGroups')
            .subscribe((data) => {
                this.contactsGroups = data['contactsGroups'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.contactsGroups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    deleteContactsGroup(row: any) {
        var contactsGroup = this.contactsGroups[row];
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + contactsGroup.label + ' »');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/contactsGroups/' + contactsGroup.id)
                .subscribe(() => {
                    var lastElement = this.contactsGroups.length - 1;
                    this.contactsGroups[row] = this.contactsGroups[lastElement];
                    this.contactsGroups[row].position = row; 
                    this.contactsGroups.splice(lastElement, 1);

                    this.dataSource = new MatTableDataSource(this.contactsGroups);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.contactsGroupDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
