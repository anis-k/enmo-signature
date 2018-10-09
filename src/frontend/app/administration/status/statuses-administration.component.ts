import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "statuses-administration.component.html",
    providers: [NotificationService]
})
export class StatusesAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl     : string;
    lang        : any = LANG;
    loading     : boolean = false;

    statuses    : Status[] = [];

    displayedColumns = ['img_filename', 'id', 'label_status', 'identifier'];
    dataSource = new MatTableDataSource(this.statuses);

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
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.statuses);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + 'rest/statuses')
            .subscribe((data: any) => {
                this.statuses = data.statuses;
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.statuses);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);

            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteStatus(status: any) {
        var resp = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + status.id + ' »');
        if (resp) {
            this.http.delete(this.coreUrl + 'rest/statuses/' + status.identifier)
                .subscribe((data: any) => {
                    this.statuses = data.statuses;
                    this.dataSource = new MatTableDataSource(this.statuses);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.statusDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

}

export interface Status {
    id: string;
    can_be_modified: string;
    can_be_searchead: string;
    identifier: number;
    img_filename: string;
    is_system: string;
    label_status: string;
    maarch_module: string;
}