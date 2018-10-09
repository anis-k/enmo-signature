import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "reports-administration.component.html",
    providers: [NotificationService]
})
export class ReportsAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    groups: any[] = [];
    reports: any[] = [];
    selectedGroup: string = "";

    loading: boolean = false;
    loadingOptions: boolean = false;


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
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.reports);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/reports/groups')
            .subscribe((data: any) => {
                this.groups = data['groups'];
                this.loadReports(0)
                this.loading = false;
            }, () => {
                location.href = "index.php";
            });
    }

    loadReports(index: any) {
        this.selectedGroup = this.groups[index].group_id;
        this.loadingOptions = true;
        this.http.get(this.coreUrl + 'rest/reports/groups/' + this.groups[index].group_id)
            .subscribe((data: any) => {
                this.reports = data['reports'];
                this.loadingOptions = false;
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    saveReport() {
        this.http.put(this.coreUrl + 'rest/reports/groups/' + this.selectedGroup, this.reports)
            .subscribe(() => {
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}