import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any): any;
declare var angularGlobals: any;


@Component({
    templateUrl: "parameter-administration.component.html",
    providers: [NotificationService]
})
export class ParameterAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;
    
    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    parameter                       : any       = {};
    type                            : string;
    creationMode                    : boolean;


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].refreshTitle(this.lang.parameterCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.loading = false;
            } else {
                window['MainHeaderComponent'].refreshTitle(this.lang.parameterModification);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = false;
                this.http.get(this.coreUrl + "rest/parameters/" + params['id'])
                    .subscribe((data: any) => {
                        this.parameter = data.parameter;

                        if (typeof (this.parameter.param_value_int) == "number") {
                            this.type = "int";
                        } else if (this.parameter.param_value_date) {
                            this.type = "date";
                        } else {
                            this.type = "string";
                        }

                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit() {
        if (this.type == 'date') {
            this.parameter.param_value_int = null;
            this.parameter.param_value_string = null;
        }
        else if (this.type == 'int') {
            this.parameter.param_value_date = null;
            this.parameter.param_value_string = null;
        }
        else if (this.type == 'string') {
            this.parameter.param_value_date = null;
            this.parameter.param_value_int = null;
        }

        if (this.creationMode == true) {
            this.http.post(this.coreUrl + 'rest/parameters', this.parameter)
                .subscribe(() => {
                    this.router.navigate(['administration/parameters']);
                    this.notify.success(this.lang.parameterAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.creationMode == false) {
            this.http.put(this.coreUrl + 'rest/parameters/' + this.parameter.id, this.parameter)
                .subscribe(() => {
                    this.router.navigate(['administration/parameters']);
                    this.notify.success(this.lang.parameterUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
