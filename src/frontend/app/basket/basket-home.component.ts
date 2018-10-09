import { Component, OnInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any) : any;
declare var angularGlobals : any;


@Component({
    selector: 'basket-home',
    templateUrl: "basket-home.component.html",
})
export class BasketHomeComponent implements OnInit {

    lang: any = LANG;
    coreUrl : string;
    mobileMode                      : boolean   = false;
    
    @Input() homeData: any;
    @Input() snavL: MatSidenav;

    constructor(public http: HttpClient) {
        this.mobileMode = angularGlobals.mobileMode;
    }

    ngOnInit(): void {        
        this.coreUrl = angularGlobals.coreUrl;
    }

    goTo(basketId:any,groupId:any) {
        window.location.href="index.php?page=view_baskets&module=basket&baskets="+basketId+"&groupId="+groupId;
    }

    goToRedirect(basketId:any,owner:any) {
        window.location.href="index.php?page=view_baskets&module=basket&baskets="+basketId+"_"+owner+"&groupId=";
    }

    closePanelLeft() {
        if(this.mobileMode) {
            this.snavL.close();
        }
    }
}
