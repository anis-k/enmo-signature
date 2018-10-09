import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { FormControl, Validators} from '@angular/forms';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any): any;
declare var angularGlobals: any;

@Component({
    templateUrl: "status-administration.component.html",
    providers: [NotificationService]
})
export class StatusAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;

    creationMode: boolean;
    statusIdAvailable: boolean;

    statusIdentifier: string;
    status: any = {
        id: null,
        label_status: null,
        can_be_searched: null,
        can_be_modified: null,
        img_filename: 'fm-letter'
    };
    statusImages: any = "";

    loading: boolean = false;

    statusId = new FormControl('', [Validators.required, Validators.pattern(/^[\w.-]*$/)]);

    getErrorMessage() {
        return this.statusId.hasError('required') ? this.lang.enterValue :
            this.statusId.hasError('pattern') ? this.lang.patternId : '';
    }

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
            if (typeof params['identifier'] == "undefined") {
                window['MainHeaderComponent'].refreshTitle(this.lang.statusCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.http.get(this.coreUrl + 'rest/administration/statuses/new')
                    .subscribe((data) => {
                        this.status.img_filename = "fm-letter";
                        this.status.can_be_searched = true;
                        this.status.can_be_modified = true;
                        this.statusImages = data['statusImages'];
                        this.creationMode = true;
                        this.loading = false;
                    });
                this.statusIdAvailable = false;
            } else {
                window['MainHeaderComponent'].refreshTitle(this.lang.statusModification);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = false;
                this.statusIdentifier = params['identifier'];
                this.getStatusInfos(this.statusIdentifier);
                this.statusIdAvailable = true;
                this.loading = false;
            }
        });
    }

    getStatusInfos(statusIdentifier: string) {
        this.http.get(this.coreUrl + 'rest/statuses/' + statusIdentifier)
            .subscribe((data) => {
                this.status = data['status'][0];
                if (this.status.can_be_searched == 'Y') {
                    this.status.can_be_searched = true;
                } else {
                    this.status.can_be_searched = false;
                }
                if (this.status.can_be_modified == 'Y') {
                    this.status.can_be_modified = true;
                } else {
                    this.status.can_be_modified = false;
                }
                this.statusImages = data['statusImages'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    isAvailable() {
        if (this.status.id) {
            this.http.get(this.coreUrl + "rest/status/" + this.status.id)
                .subscribe(() => {
                    this.statusIdAvailable = false;
                }, (err) => {
                    this.statusIdAvailable = false;
                    if (err.error.errors == "id not found") {
                        this.statusIdAvailable = true;
                    }
                });
        } else {
            this.statusIdAvailable = false;
        }
    }

    submitStatus() {
        if (this.creationMode == true) {
            this.http.post(this.coreUrl + 'rest/statuses', this.status)
                .subscribe(() => {
                    this.notify.success(this.lang.statusAdded);
                    this.router.navigate(['administration/statuses']);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.creationMode == false) {

            this.http.put(this.coreUrl + 'rest/statuses/' + this.statusIdentifier, this.status)
                .subscribe(() => {
                    this.notify.success(this.lang.statusUpdated);
                    this.router.navigate(['administration/statuses']);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
