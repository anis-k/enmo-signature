import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { FormControl } from '@angular/forms';
import { debounceTime, switchMap, distinctUntilChanged, filter } from 'rxjs/operators';
import { MatPaginator, MatSort, MatTableDataSource, MatSidenav, MatProgressBarModule } from '@angular/material';
import { SelectionModel } from '@angular/cdk/collections';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "contacts-group-administration.component.html",
    providers: [NotificationService]
})
export class ContactsGroupAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;
    creationMode: boolean;
    contactsGroup: any = {};
    contactTypes: any = {};
    nbContact   : number;

    contactTypeSearch: string;

    loading: boolean = false;
    initAutoCompleteContact = true;

    searchTerm: FormControl = new FormControl();
    searchResult: any = [];

    displayedColumns = ['select', 'contact', 'address'];
    displayedColumnsAdded = ['contact', 'address', 'actions'];
    dataSource: any;
    dataSourceAdded: any;
    selection = new SelectionModel<Element>(true, []);

    /** Selects all rows if they are not all selected; otherwise clear selection. */
    masterToggle(event: any) {
        if (event.checked) {
            this.dataSource.data.forEach((row: any) => {
                if (!$j("#check_" + row.addressId + '-input').is(":disabled")) {
                    this.selection.select(row.addressId);
                }
            });
        } else {
            this.selection.clear();
        }
    }

    @ViewChild('paginatorContactList') paginator: MatPaginator;
    //@ViewChild('tableContactList') sortContactList: MatSort;

    @ViewChild('paginatorAdded') paginatorAdded: MatPaginator;
    @ViewChild('tableAdded') sortAdded: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSourceAdded.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);

        this.searchTerm.valueChanges.pipe(
            debounceTime(500),
            filter(value => value.length > 2),
            distinctUntilChanged(),
            switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/contacts', { params: { "search": data, "type": this.contactTypeSearch } }))
        ).subscribe((response: any) => {
            this.searchResult = response;
            this.dataSource = new MatTableDataSource(this.searchResult);
            this.dataSource.paginator = this.paginator;
            //this.dataSource.sort      = this.sortContactList;
        });
        
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.contactTypeSearch = 'all';

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].refreshTitle(this.lang.contactGroupCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.contactsGroup.public = false;
                this.loading = false;
            } else {
                window['MainHeaderComponent'].refreshTitle(this.lang.contactsGroupModification);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

                this.creationMode = false;

                this.http.get(this.coreUrl + 'rest/contactsTypes')
                    .subscribe((data: any) => {
                        this.contactTypes = data.contactsTypes;
                    });

                this.http.get(this.coreUrl + 'rest/contactsGroups/' + params['id'])
                    .subscribe((data: any) => {
                        this.contactsGroup = data.contactsGroup;
                        this.nbContact = this.contactsGroup.nbContacts;
                        setTimeout(() => {
                            this.dataSourceAdded = new MatTableDataSource(this.contactsGroup.contacts);
                            this.dataSourceAdded.paginator = this.paginatorAdded;
                            this.dataSourceAdded.sort = this.sortAdded;
                        }, 0);

                        this.loading = false;
                    });
            }
        });
    }

    saveContactsList(elem: any): void {
        elem.textContent = this.lang.loading + '...';
        elem.disabled = true;
        this.http.post(this.coreUrl + 'rest/contactsGroups/' + this.contactsGroup.id + '/contacts', { 'contacts': this.selection.selected })
            .subscribe((data: any) => {
                this.notify.success(this.lang.contactAdded);
                this.nbContact = this.nbContact + this.selection.selected.length;
                this.selection.clear();
                elem.textContent = this.lang.add;
                this.contactsGroup = data.contactsGroup;
                setTimeout(() => {
                    this.dataSourceAdded = new MatTableDataSource(this.contactsGroup.contacts);
                    this.dataSourceAdded.paginator = this.paginatorAdded;
                    this.dataSourceAdded.sort = this.sortAdded;
                }, 0);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/contactsGroups', this.contactsGroup)
                .subscribe((data: any) => {
                    this.router.navigate(['/administration/contacts-groups/' + data.contactsGroup]);
                    this.notify.success(this.lang.contactsGroupAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + 'rest/contactsGroups/' + this.contactsGroup.id, this.contactsGroup)
                .subscribe(() => {
                    this.router.navigate(['/administration/contacts-groups']);
                    this.notify.success(this.lang.contactsGroupUpdated);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    preDelete(row: any) {
        let r = confirm(this.lang.reallyWantToDeleteContactFromGroup);

        if (r) {
            this.removeContact(this.contactsGroup.contacts[row], row);
        }
    }

    removeContact(contact: any, row: any) {
        this.http.delete(this.coreUrl + "rest/contactsGroups/" + this.contactsGroup.id + "/contacts/" + contact['addressId'])
            .subscribe(() => {
                var lastElement = this.contactsGroup.contacts.length - 1;
                this.contactsGroup.contacts[row] = this.contactsGroup.contacts[lastElement];
                this.contactsGroup.contacts[row].position = row;
                this.contactsGroup.contacts.splice(lastElement, 1);
                this.nbContact = this.nbContact - 1;
                this.dataSourceAdded = new MatTableDataSource(this.contactsGroup.contacts);
                this.dataSourceAdded.paginator = this.paginatorAdded;
                this.dataSourceAdded.sort = this.sortAdded;
                this.notify.success(this.lang.contactDeletedFromGroup);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    launchLoading() {
        if (this.searchTerm.value.length > 2) {
            this.dataSource = null;
            this.initAutoCompleteContact = false;
        }
    }

    isInGrp(address: any): boolean {
        let isInGrp = false;
        this.contactsGroup.contacts.forEach((row: any) => {
            if (row.addressId == address.addressId) {
                isInGrp = true;
            }
        });
        return isInGrp;
    }

    selectAddress(addressId:any) {
        if (!$j("#check_" + addressId + '-input').is(":disabled")) {
            this.selection.toggle(addressId);
        }    
    }
}
