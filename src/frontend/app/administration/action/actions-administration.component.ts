import { ChangeDetectorRef, Component, ViewChild, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatSidenav} from '@angular/material';

declare function $j(selector: any): any;
declare var angularGlobals: any;

@Component({
    templateUrl: "actions-administration.component.html",
    providers: [NotificationService]
})

export class ActionsAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;
    search: string = null;

    actions: any[] = [];
    titles: any[] = [];

    loading: boolean = false;

    displayedColumns = ['id', 'label_action', 'history', 'actions'];
    dataSource = new MatTableDataSource(this.actions);
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
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.actions);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/actions')
            .subscribe((data) => {
                this.actions = data['actions'];
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.actions);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    deleteAction(action: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + action.label_action + ' »');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/actions/' + action.id)
                .subscribe((data: any) => {
                    this.actions = data.actions;
                    this.dataSource = new MatTableDataSource(this.actions);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.notify.success(this.lang.actionDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
