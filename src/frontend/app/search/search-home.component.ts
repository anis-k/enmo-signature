import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;
declare var angularGlobals : any;


@Component({
    selector: 'search-home',
    templateUrl :   "search-home.component.html",
})
export class SearchHomeComponent implements OnInit {

    lang: any = LANG;
    coreUrl : string;

    constructor(public http: HttpClient) {
    }

    ngOnInit(): void {        
        this.coreUrl = angularGlobals.coreUrl;
    }

}
