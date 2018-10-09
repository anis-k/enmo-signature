import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav, MatPaginator, MatTableDataSource, MatSort } from '@angular/material';


declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "baskets-administration.component.html",
    providers: [NotificationService]
})
export class BasketsAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    baskets                         : any[]     = [];
    basketsOrder                    : any[]     = [];

    displayedColumns    = ['basket_id', 'basket_name', 'basket_desc', 'actions'];
    dataSource          : any;

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
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.baskets);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + "rest/baskets")
            .subscribe((data: any) => {
                this.baskets = data['baskets'];
                this.loading = false;
                setTimeout(() => {
                    this.http.get(this.coreUrl + "rest/sortedBaskets")
                        .subscribe((data: any) => {
                            this.basketsOrder = data['baskets'];
                        }, () => {
                            location.href = "index.php";
                        });
                    this.dataSource = new MatTableDataSource(this.baskets);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    delete(basket: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + basket['basket_name'] + ' »');

        if (r) {
            this.http.delete(this.coreUrl + "rest/baskets/" + basket['basket_id'])
                .subscribe((data: any) => {
                    this.notify.success(this.lang.basketDeleted);
                    this.baskets = data['baskets'];
                    this.dataSource = new MatTableDataSource(this.baskets);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                    this.http.get(this.coreUrl + "rest/sortedBaskets")
                        .subscribe((data: any) => {
                            this.basketsOrder = data['baskets'];
                        }, () => {
                            location.href = "index.php";
                        });
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    updateBasketOrder(currentBasket: any) {
        this.http.put(this.coreUrl + "rest/sortedBaskets/" + currentBasket.basket_id, this.basketsOrder)
            .subscribe((data: any) => {
                this.baskets = data['baskets'];
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
