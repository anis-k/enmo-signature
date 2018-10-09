import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { MatDialog, MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "about-us.component.html",
    styleUrls: ['profile.component.css'],
    providers: [NotificationService]
})
export class AboutUsComponent implements OnInit {

    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    mobileMode                      : boolean   = false;
    applicationMinorVersion              : string;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, public dialog: MatDialog) {
        this.mobileMode = angularGlobals.mobileMode;        
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.aboutUs);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.applicationMinorVersion = angularGlobals.applicationMinorVersion;
        this.loading = false;

    }

}
