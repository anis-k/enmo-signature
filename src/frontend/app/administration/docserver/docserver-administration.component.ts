import { ChangeDetectorRef, Component, OnInit, ViewChild, Pipe, PipeTransform } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "docserver-administration.component.html",
    providers   : [NotificationService]
})

export class DocserverAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl     : string;
    lang        : any = LANG;
    loading     : boolean = false;
    dataSource  : any;

    docserver    : any = {coll_id:"letterbox_coll", docserver_type_id:"DOC", limitSizeFormatted :"50"};
    docserversTypes : any = [];

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    
    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private router: Router, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.docserverCreation);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/docserverTypes')
            .subscribe((data: any) => {
                this.docserversTypes = data.docserverTypes;
                this.loading = false;
            });
    }


    onSubmit(docserver:any) {
        docserver.size_limit_number = docserver.limitSizeFormatted * 1000000000;
        this.http.post(this.coreUrl + 'rest/docservers', docserver)
            .subscribe((data: any) => {     
                this.notify.success(this.lang.docserverAdded);
                this.router.navigate(["/administration/docservers/"]);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
