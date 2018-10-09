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
    templateUrl: "action-administration.component.html",
    providers: [NotificationService]
})
export class ActionAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;
    creationMode: boolean;
    action: any = {};
    statuses: any[] = [];
    actionPagesList: any[] = [];
    categoriesList: any[] = [];
    keywordsList: any[] = [];

    loading: boolean = false;

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

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].refreshTitle(this.lang.actionCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;

                this.http.get(this.coreUrl + 'rest/initAction')
                    .subscribe((data: any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statuses = data.statuses;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                    });
            }
            else {
                window['MainHeaderComponent'].refreshTitle(this.lang.actionModification);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);
                
                this.creationMode = false;

                this.http.get(this.coreUrl + 'rest/actions/' + params['id'])
                    .subscribe((data: any) => {
                        this.action = data.action;
                        this.categoriesList = data.categoriesList;
                        this.statuses = data.statuses;

                        this.actionPagesList = data.action_pagesList;
                        this.keywordsList = data.keywordsList;
                        this.loading = false;
                    });
            }
        });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/actions', this.action)
                .subscribe(() => {
                    this.router.navigate(['/administration/actions']);
                    this.notify.success(this.lang.actionAdded);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + 'rest/actions/' + this.action.id, this.action)
                .subscribe(() => {
                    this.router.navigate(['/administration/actions']);
                    this.notify.success(this.lang.actionUpdated);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}