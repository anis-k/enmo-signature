import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-app',
    templateUrl :   "menu.component.html",
})
export class MenuComponent implements OnInit {

    lang: any = LANG;
    coreUrl                     : string;

    constructor(public http: HttpClient) {
    }

    ngOnInit(): void {        
        this.coreUrl = angularGlobals.coreUrl;
    }
}
