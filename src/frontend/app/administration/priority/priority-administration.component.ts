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
    templateUrl: "priority-administration.component.html",
    providers: [NotificationService]
})
export class PriorityAdministrationComponent implements OnInit {

    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl         : string;
    id              : string;
    creationMode    : boolean;
    lang            : any       = LANG;
    loading         : boolean   = false;

    priority        : any       = {
        useDoctypeDelay : false,
        color           : "#135f7f",
        delays          : "0",
        working_days    : "false",
        default_priority: false
    };

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
                window['MainHeaderComponent'].refreshTitle(this.lang.priorityCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.loading = false;
            } else {
                window['MainHeaderComponent'].refreshTitle(this.lang.priorityModification);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = false;
                this.id = params['id'];
                this.http.get(this.coreUrl + "rest/priorities/" + this.id)
                    .subscribe((data: any) => {
                        this.priority = data.priority;
                        this.priority.useDoctypeDelay = this.priority.delays != null;
                        if (this.priority.working_days === true) {
                            this.priority.working_days = "true";
                        } else {
                            this.priority.working_days = "false";
                        }
                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit() {
        if (this.priority.useDoctypeDelay == false) {
            this.priority.delays = null;
        }
        this.priority.working_days = this.priority.working_days == "true";
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/priorities", this.priority)
                .subscribe(() => {
                    this.notify.success(this.lang.priorityAdded);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/priorities/" + this.id, this.priority)
                .subscribe(() => {
                    this.notify.success(this.lang.priorityUpdated);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
