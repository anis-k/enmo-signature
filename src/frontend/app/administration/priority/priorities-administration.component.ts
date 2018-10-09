import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import {MatPaginator, MatTableDataSource, MatSort, MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "priorities-administration.component.html",
    providers: [NotificationService]
})
export class PrioritiesAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl         : string;
    lang            : any       = LANG;
    loading         : boolean   = false;

    priorities      : any[]     = [];
    prioritiesOrder : any[]     = [];
    dataSource      : any;
    displayedColumns            = ['label', 'delays', 'working_days', 'default_priority', 'actions'];


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
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.priorities);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + 'rest/priorities')
            .subscribe((data: any) => {
                this.priorities = data["priorities"];
                this.loading = false;
                this.http.get(this.coreUrl + "rest/sortedPriorities")
                    .subscribe((data: any) => {
                        this.prioritiesOrder = data['priotities'];
                    }, () => {
                        location.href = "index.php";
                    });
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.priorities);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            })
    }

    deletePriority(id: string) {
        let r = confirm(this.lang.deleteMsg);

        if (r) {
            this.http.delete(this.coreUrl + "rest/priorities/" + id)
                .subscribe((data: any) => {
                    this.priorities = data["priorities"];
                    this.dataSource = new MatTableDataSource(this.priorities);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.priorityDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                })
        }
    }

    updatePrioritiesOrder() {
        this.http.put(this.coreUrl + "rest/sortedPriorities", this.prioritiesOrder)
            .subscribe((data: any) => {
                this.prioritiesOrder = data['priorities'];
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
