import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';

import { AutoCompletePlugin } from '../../../plugins/autocomplete.plugin';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any): any;
declare var angularGlobals: any;


@Component({
    templateUrl: "update-status-administration.component.html",
    styleUrls: ['update-status-administration.component.css'],
    providers: [NotificationService]
})
export class UpdateStatusAdministrationComponent extends AutoCompletePlugin implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    statuses                        : any[]     = [];
    statusId                        : string    = "";
    resId                           : string    = "";
    chrono                          : string    = "";
    resIdList                       : string[]  = [];
    chronoList                      : string[]  = [];

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        super(http, ['statuses']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.updateStatus);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = false;
    }

    onSubmit() {
        var body = {
            "status": this.statusId
        };
        if (this.resIdList.length > 0) {
            body["resId"] = this.resIdList;
        } else if (this.chronoList.length > 0) {
            body["chrono"] = this.chronoList;
        }        

        this.http.put(this.coreUrl + "rest/res/resource/status", body)
            .subscribe(() => {
                this.resId = "";
                this.chrono = "";
                this.statusId = "";
                this.resIdList = [];
                this.chronoList = [];
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    addResId()  :void {
        if(this.resIdList.indexOf(this.resId) == -1){
            this.resIdList.push(this.resId);
        }        
        this.resId = ""
    }

    addChrono() :void { 
        if(this.chronoList.indexOf(this.chrono) == -1){
            this.chronoList.push(this.chrono);
        }
        this.chrono = "";
    }

    removeResId(resId: string) :void {
        var resIdIndex = this.resIdList.indexOf(resId);
        this.resIdList.splice(resIdIndex,1);
    }

    removeChrono(chrono: string)  :void {
        var chronoIndex = this.chronoList.indexOf(chrono);
        this.chronoList.splice(chronoIndex,1);
    }

    resetInput(e: any) {
        if (e.index == 0) {
            this.resId = "";
        } else {
            this.chrono = "";
        }
    }
}
