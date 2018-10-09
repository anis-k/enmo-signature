import { ChangeDetectorRef, Component, OnInit, ViewChild }  from '@angular/core';
import { HttpClient }                                       from '@angular/common/http';
import { MediaMatcher }                                     from '@angular/cdk/layout';
import { MatPaginator, MatTableDataSource, MatSort, MatSidenav }        from '@angular/material';
import { LANG }                                             from '../../translate.component';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "history-administration.component.html"
})
export class HistoryAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;
    limitExceeded                   : boolean   = false;
    batchLimitExceeded              : boolean   = false;

    data                            : any[]     = [];
    batchData                       : any[]     = [];
    startDate                       : Date      = new Date();
    endDate                         : Date      = new Date();
    batchStartDate                  : Date      = new Date();
    batchEndDate                    : Date      = new Date();


    dataSource          = new MatTableDataSource(this.data);
    batchDataSource     = new MatTableDataSource(this.batchData);
    displayedColumns    = ['event_date', 'event_type', 'user_id', 'info', 'remote_ip'];
    batchDisplayedColumns = ['event_date', 'total_processed', 'total_errors', 'info', 'module_name'];

    accessBatchHistory              : boolean   = true;
    accessHistory                   : boolean   = true;


    @ViewChild('paginator') paginator: MatPaginator;
    @ViewChild('batchPaginator') batchPaginator: MatPaginator;
    @ViewChild('sort') sort: MatSort;
    @ViewChild('batchSort') batchSort: MatSort;
    applyFilter(filterValue: string, historyType : string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        if(historyType == 'normal'){
            this.dataSource.filter = filterValue;
        } else {
            this.batchDataSource.filter = filterValue
        }        
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient) {
        $j("link[href='merged_css.php']").remove();

        this.startDate.setHours(0,0,0,0);
        this.startDate.setMonth(this.endDate.getMonth()-1);
        this.endDate.setHours(23,59,59,59);

        this.batchStartDate.setHours(0,0,0,0);
        this.batchStartDate.setMonth(this.endDate.getMonth()-1);
        this.batchEndDate.setHours(23,59,59,59);

        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.history);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + 'rest/histories', {params: {"startDate" : (this.startDate.getTime() / 1000).toString(), "endDate" : (this.endDate.getTime() / 1000).toString()}})
            .subscribe((data: any) => {
                this.data = data['histories'];
                this.limitExceeded = data['limitExceeded'];
                this.loading = false;
                setTimeout(() => {
                    this.accessHistory = true;
                    this.dataSource = new MatTableDataSource(this.data);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (data: any) => {
                if(data['error'].errors == 'Service forbidden'){
                        this.loading = false;
                        this.accessHistory = false;
                } else {
                    location.href = "index.php";
                }                
            });

        this.http.get(this.coreUrl + 'rest/batchHistories', {params: {"startDate" : (this.batchStartDate.getTime() / 1000).toString(), "endDate" : (this.batchEndDate.getTime() / 1000).toString()}})
            .subscribe((data: any) => {
                this.batchData = data['batchHistories'];
                this.batchLimitExceeded = data['limitExceeded'];
                this.loading = false;
                setTimeout(() => {
                    this.accessBatchHistory = true;
                    this.batchDataSource = new MatTableDataSource(this.batchData);
                    this.batchDataSource.paginator = this.batchPaginator;
                    this.batchDataSource.sort = this.batchSort;
                }, 0);
            }, (data: any) => {
                if(data['error'].errors == 'Service forbidden'){
                        this.loading = false
                        this.accessBatchHistory = false;
                } else {
                    location.href = "index.php";
                }
                
            });
            
    }

    ngAfterViewInit() {
        this.batchDataSource.paginator = this.batchPaginator;
        this.batchDataSource.sort = this.batchSort;
    }
    
    _setDataSource(indexNumber : any) {
        setTimeout(() => {
            switch (indexNumber) {
                case 0:
                  !this.dataSource.paginator ? this.dataSource.paginator = this.paginator : null;
                  !this.dataSource.sort ? this.dataSource.sort = this.sort : null;
                  break;
                case 1:
                  !this.batchDataSource.paginator ? this.batchDataSource.paginator = this.batchPaginator : null;
                  !this.batchDataSource.sort ? this.batchDataSource.sort = this.batchSort : null;
            }
        });
    }

    refreshHistory(historyType : string) {
        if (historyType == 'normal') {
            this.startDate.setHours(0,0,0,0);
            this.endDate.setHours(23,59,59,59);
            this.http.get(this.coreUrl + 'rest/histories', {params: {"startDate" : (this.startDate.getTime() / 1000).toString(), "endDate" : (this.endDate.getTime() / 1000).toString()}})
                .subscribe((data: any) => {
                    this.data = data['histories'];
                    this.limitExceeded = data['limitExceeded'];
                    this.loading = false;
                    setTimeout(() => {
                        this.dataSource = new MatTableDataSource(this.data);
                        this.dataSource.paginator = this.paginator;
                        this.dataSource.sort = this.sort;
                    }, 0);
                }, () => {
                    location.href = "index.php";
                });
        } else {
            this.batchStartDate.setHours(0,0,0,0);
            this.batchEndDate.setHours(23,59,59,59);
            this.http.get(this.coreUrl + 'rest/batchHistories', {params: {"startDate" : (this.batchStartDate.getTime() / 1000).toString(), "endDate" : (this.batchEndDate.getTime() / 1000).toString()}})
                .subscribe((data: any) => {
                    this.batchData = data['batchHistories'];
                    this.batchLimitExceeded = data['limitExceeded'];
                    this.loading = false;
                    setTimeout(() => {
                        this.accessBatchHistory = true;
                        this.batchDataSource = new MatTableDataSource(this.batchData);
                        this.batchDataSource.paginator = this.batchPaginator;
                        this.batchDataSource.sort = this.batchSort;
                    }, 0);
                }, (data: any) => {
                    if(data['error'].errors == 'Service forbidden'){
                        this.loading = false;
                        this.accessBatchHistory = false;
                    } else {
                        location.href = "index.php";
                    }
                });
        }
    }
}
