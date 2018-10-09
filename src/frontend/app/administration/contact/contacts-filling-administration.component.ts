import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "contacts-filling-administration.component.html",
    providers: [NotificationService]
})
export class ContactsFillingAdministrationComponent implements OnInit {

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;

    contactsFilling: any = {};

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

        this.http.get(this.coreUrl + 'rest/contactsFilling')
            .subscribe((data: any) => {
                this.contactsFilling = data.contactsFilling;

                this.loading = false;
            });
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/contactsFilling', this.contactsFilling)
            .subscribe(() => {
                this.router.navigate(['/administration/contacts-filling']);
                // this.notify.success(this.lang.contactsGroupUpdated);

            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
