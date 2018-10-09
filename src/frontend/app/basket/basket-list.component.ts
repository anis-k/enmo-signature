import { ChangeDetectorRef, Component, OnInit, ViewChild, QueryList, ViewChildren, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { merge, Observable, of as observableOf } from 'rxjs';
import { NotificationService } from '../notification.service';
import { MatDialog, MatSidenav, MatExpansionPanel, MatTableDataSource, MatPaginator, MatSort, MatBottomSheet, MatBottomSheetRef, MAT_BOTTOM_SHEET_DATA } from '@angular/material';

import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { startWith, switchMap, map, catchError } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "basket-list.component.html",
    providers: [NotificationService]
})
export class BasketListComponent implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    mobileMode: boolean = false;
    coreUrl: string;
    lang: any = LANG;

    loading: boolean = false;
    docUrl: string = '';
    public innerHtml: SafeHtml;
    basketUrl: string;
    homeData: any;


    @ViewChild('snav') sidenavLeft: MatSidenav;
    @ViewChild('snav2') sidenavRight: MatSidenav;


    displayedColumnsBasket: string[] = ['res_id', 'subject', 'contact_society', 'creation_date'];

    exampleDatabase: ExampleHttpDao | null;
    data: any[] = [];
    resultsLength = 0;
    isLoadingResults = true;
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild('tableBasketListSort') sort: MatSort;
    constructor(changeDetectorRef: ChangeDetectorRef, private route: ActivatedRoute, media: MediaMatcher, public http: HttpClient, public dialog: MatDialog, private sanitizer: DomSanitizer, private bottomSheet: MatBottomSheet) {
        this.mobileMode = angularGlobals.mobileMode;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = false;

        if (this.mobileMode) {
            this.displayedColumnsBasket = ['res_id', 'subject'];
        }

        this.http.get(this.coreUrl + "rest/home")
            .subscribe((data: any) => {
                this.homeData = data;
            });


        this.route.params.subscribe(params => {
            this.basketUrl = this.coreUrl + 'rest/resources/groups/' + params['groupSerialId'] + '/baskets/' + params['basketId'];
            this.http.get(this.coreUrl + "rest/baskets/" + params['basketId'])
                .subscribe((data: any) => {
                    window['MainHeaderComponent'].refreshTitle(data.basket.basket_name);
                    window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                    window['MainHeaderComponent'].setSnavRight(null);
                    this.exampleDatabase = new ExampleHttpDao(this.http);

                    // If the user changes the sort order, reset back to the first page.
                    this.paginator.pageIndex = 0;
                    this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

                    merge(this.sort.sortChange, this.paginator.page)
                        .pipe(
                            startWith({}),
                            switchMap(() => {
                                this.isLoadingResults = true;
                                return this.exampleDatabase!.getRepoIssues(
                                    this.sort.active, this.sort.direction, this.paginator.pageIndex, this.basketUrl);
                            }),
                            map(data => {
                                // Flip flag to show that loading has finished.
                                this.isLoadingResults = false;
                                this.resultsLength = data.number;

                                return data.resources;
                            }),
                            catchError(() => {
                                this.isLoadingResults = false;
                                return observableOf([]);
                            })
                        ).subscribe(data => this.data = data);

                }, () => {
                    location.href = "index.php";
                });
        });


    }

    goTo(row: any) {
        if (this.docUrl == this.coreUrl + 'rest/res/' + row.res_id + '/content' && this.sidenavRight.opened) {
            this.sidenavRight.close();
        } else {
            this.docUrl = this.coreUrl + 'rest/res/' + row.res_id + '/content';
            this.innerHtml = this.sanitizer.bypassSecurityTrustHtml(
                "<iframe style='height:100%;width:100%;' src='" + this.docUrl + "' class='embed-responsive-item'>" +
                "</iframe>");
            this.sidenavRight.open();
        }
    }

    goToDetail(row: any) {
        location.href = "index.php?page=details&dir=indexing_searching&id=" + row.res_id;
    }

    openBottomSheet(row: any): void {
        this.bottomSheet.open(BottomSheetNoteList, {
            data: { resId: row.res_id, chrono: row.alt_identifier },
            panelClass: 'note-width-bottom-sheet'
        });
    }

    openAttachSheet(row: any): void {
        this.bottomSheet.open(BottomSheetAttachmentList, {
            data: { resId: row.res_id, chrono: row.alt_identifier },
        });
    }

    openDiffusionSheet(row: any): void {
        this.bottomSheet.open(BottomSheetDiffusionList, {
            data: { resId: row.res_id, chrono: row.alt_identifier },
        });
    }

    test () {
        this.http.post("index.php?display=true&page=manage_action&module=core",{})
            .subscribe((data: any) => {
                console.log(data);
            });
    }
}
export interface BasketList {
    resources: any[];
    number: number;
}


export class ExampleHttpDao {

    constructor(private http: HttpClient) { }

    getRepoIssues(sort: string, order: string, page: number, href: string): Observable<BasketList> {
        let offset = page * 10;
        const requestUrl = `${href}?limit=10&offset=${offset}`;

        return this.http.get<BasketList>(requestUrl);
    }
}

@Component({
    templateUrl: 'note-list.component.html',
})
export class BottomSheetNoteList {
    coreUrl: string;
    lang: any = LANG;
    notes: any;
    loading: boolean = true;

    constructor(public http: HttpClient, private bottomSheetRef: MatBottomSheetRef<BottomSheetNoteList>, @Inject(MAT_BOTTOM_SHEET_DATA) public data: any) { }

    ngOnInit(): void {


    }
    ngAfterViewInit() {
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + "rest/res/" + this.data.resId + "/notes")
            .subscribe((data: any) => {
                this.notes = data;
                this.loading = false;
            });
    }
}


@Component({
    templateUrl: 'attachment-list.component.html',
})
export class BottomSheetAttachmentList {
    coreUrl: string;
    lang: any = LANG;
    attachments: any;
    attachmentTypes: any;
    loading: boolean = true;

    constructor(public http: HttpClient, private bottomSheetRef: MatBottomSheetRef<BottomSheetAttachmentList>, @Inject(MAT_BOTTOM_SHEET_DATA) public data: any) { }

    ngOnInit(): void {


    }
    ngAfterViewInit() {
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + "rest/res/" + this.data.resId + "/attachments")
            .subscribe((data: any) => {
                this.attachments = data.attachments;
                this.attachmentTypes = data.attachment_types;
                this.loading = false;
            });
    }
}

@Component({
    templateUrl: 'diffusion-list.component.html',
})
export class BottomSheetDiffusionList {
    coreUrl: string;
    lang: any = LANG;
    listinstance: any = [];
    visaCircuit: any;
    avisCircuit: any;
    roles: any = [];
    loading: boolean = true;
    tabVisaCircuit: boolean = false;
    tabAvisCircuit: boolean = false;

    constructor(public http: HttpClient, private bottomSheetRef: MatBottomSheetRef<BottomSheetDiffusionList>, @Inject(MAT_BOTTOM_SHEET_DATA) public data: any) { }

    ngOnInit(): void {


    }
    ngAfterViewInit() {
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + "rest/res/" + this.data.resId + "/listinstance")
            .subscribe((data: any) => {
                if (data != null) {
                    this.roles = Object.keys(data);
                    this.listinstance = data;
                }

                this.http.get(this.coreUrl + "rest/res/" + this.data.resId + "/visaCircuit")
                    .subscribe((data: any) => {
                        this.visaCircuit = data;
                        if (this.visaCircuit.length > 0) {
                            this.tabVisaCircuit = true;
                        }

                        this.http.get(this.coreUrl + "rest/res/" + this.data.resId + "/avisCircuit")
                            .subscribe((data: any) => {
                                this.avisCircuit = data;
                                if (this.avisCircuit.length > 0) {
                                    this.tabAvisCircuit = true;
                                }
                                this.loading = false;
                            });
                    });
            });
    }
}


