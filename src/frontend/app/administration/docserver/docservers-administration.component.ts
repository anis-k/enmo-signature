import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatSort, MatSidenav } from '@angular/material';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl : "docservers-administration.component.html",
    providers   : [NotificationService]
})

export class DocserversAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl             : string;
    lang                : any = LANG;
    loading             : boolean = false;
    dataSource          : any;

    docservers          : any = [];
    docserversClone     : any = [];
    docserversTypes     : any = {};

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    
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
        window['MainHeaderComponent'].refreshTitle(this.lang.docservers);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/docservers')
            .subscribe((data: any) => {
                this.docservers = data.docservers;
                this.docserversClone = JSON.parse(JSON.stringify(this.docservers));
                this.docserversTypes = data.types;
                this.loading = false;
            });
    }

    toggleDocserver(docserver: any) {

        docserver.is_readonly = !docserver.is_readonly;
    }

    cancelModification(docserverType: any, index: number) {
        this.docservers[docserverType][index] = JSON.parse(JSON.stringify(this.docserversClone[docserverType][index]));
    }

    checkModif(docserver: any, docserversClone: any) {
        docserver.size_limit_number = docserver.limitSizeFormatted * 1000000000;
        if (JSON.stringify(docserver) === JSON.stringify(docserversClone)) {
            return true 
        } else {
            if (docserver.size_limit_number >= docserver.actual_size_number && docserver.limitSizeFormatted > 0 && /^[\d]*$/.test(docserver.limitSizeFormatted) ) {
                return false;
            } else {
                return true;
            } 
        }
    }

    onSubmit(docserver: any, i: number) {
        docserver.size_limit_number = docserver.limitSizeFormatted * 1000000000;
        this.http.put(this.coreUrl + 'rest/docservers/' + docserver.id, docserver)
            .subscribe((data: any) => {
                this.docservers[docserver.docserver_type_id][i] = data['docserver'];
                this.docserversClone[docserver.docserver_type_id][i] = JSON.parse(JSON.stringify(this.docservers[docserver.docserver_type_id][i]));
                this.notify.success(this.lang.docserverUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    delete(docserver: any, i: number) {
        let r = null;
        if (docserver.actual_size_number == 0) {
            r = confirm(this.lang.delete+' ?');
        } else {
            r = confirm(this.lang.docserverdeleteWarning);     
        }
        
        if (r) {
            this.http.delete(this.coreUrl + 'rest/docservers/'+docserver.id)
            .subscribe(() => {
                this.docservers[docserver.docserver_type_id].splice(i, 1);
                this.notify.success(this.lang.docserverDeleted);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }
    }
}
