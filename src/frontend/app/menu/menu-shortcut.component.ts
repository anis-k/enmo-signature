import { Component, OnInit, Inject }    from '@angular/core';
import { Router }               from '@angular/router';
import { HttpClient }           from '@angular/common/http';
import { Location }             from '@angular/common';
import { LANG }                 from '../translate.component';
import { ShortcutMenuService }  from '../../service/shortcut-menu.service';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialog } from '@angular/material';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-shortcut',
    templateUrl : "menu-shortcut.component.html",
})
export class MenuShortcutComponent implements OnInit {

    coreUrl     : string;
    lang        : any       = LANG;
    mobileMode  : boolean   = false;
    router      : any;
    dialogRef   : MatDialogRef<any>;
    config      : any       = {};
    speedDialFabButtons : any = [];
    speedDialFabColumnDirection = 'column';


    constructor(public http: HttpClient, private _location: Location, private _router: Router, public shortcut: ShortcutMenuService, public dialog: MatDialog) {
        this.mobileMode = angularGlobals.mobileMode;
        this.router = _router;
        /**/
    }

    ngOnInit(): void {      
        this.coreUrl = angularGlobals.coreUrl;
        setTimeout(() => {
            if(this.shortcut.shortcutsData.user.indexingGroups.length > 0) {
                this.shortcut.shortcutsData.user.indexingGroups.forEach((group: any) => {
                    this.speedDialFabButtons.push({
                        icon: 'fa fa-plus',
                        tooltip: this.lang.indexingWithProfile+' '+ group.label,
                        label: group.label,
                        id: group.groupId
                    });
                });
            }
        }, 500);
    }

    onSpeedDialFabClicked(btn: any, shortcut:any) {
        location.href = shortcut.servicepage+'&groupId='+btn.id;
    }

    gotToMenu(shortcut:any) {
        if (shortcut.angular == 'true') {
            this.router.navigate([shortcut.servicepage]);
        } else {
            location.href = shortcut.servicepage;
        }
    }
}
@Component({
    templateUrl: "indexing-group-modal.component.html",
    styles: [".mat-dialog-content{max-height: 65vh;width:600px;}"]
})
export class IndexingGroupModalComponent {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<IndexingGroupModalComponent>) {
    }
}