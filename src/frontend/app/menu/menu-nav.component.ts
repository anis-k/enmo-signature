import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import {Location} from '@angular/common';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-nav',
    templateUrl :   "menuNav.component.html",
})
export class MenuNavComponent implements OnInit {

    lang: any = LANG;
    coreUrl                     : string;
    mobileMode                      : boolean   = false;
    router :any;
    user       : any       = {};

    constructor(public http: HttpClient, private _location: Location, private _router: Router, private activatedRoute:ActivatedRoute) {
        this.router = _router;
    }

    ngOnInit(): void {
        
        this.mobileMode = angularGlobals.mobileMode;      
        this.coreUrl = angularGlobals.coreUrl;
        
    }

    backClicked() {
        this.router.navigate(['../'],{ relativeTo: this.activatedRoute });
    }
}
