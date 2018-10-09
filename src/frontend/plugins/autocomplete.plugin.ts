import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { FormControl } from '@angular/forms';
import { Observable, empty } from 'rxjs';
import { startWith, map, debounceTime, filter, distinctUntilChanged, switchMap } from 'rxjs/operators';

declare const angularGlobals: any;

export class AutoCompletePlugin {
    coreUrl: string;
    userCtrl: FormControl;
    visaUserCtrl: FormControl;
    statusCtrl: FormControl;
    elementCtrl: FormControl;
    filteredVisaUsers: Observable<any[]>;
    filteredUsers: Observable<any[]>;
    filteredElements: Observable<any[]>;
    filteredStatuses: Observable<any[]>;
    visaUserList: any[] = [];
    userList: any[] = [];
    elemList: any[] = [];
    statusesList: any[] = [];

    constructor(public http: HttpClient, target: any[]) {
        this.coreUrl = angularGlobals.coreUrl;

        if (target.indexOf('users') != -1) {
            this.userCtrl = new FormControl();
            this.userCtrl.valueChanges.pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/users', { params: { "search": data } }))
            ).subscribe((response: any) => {
                if (response.length == 0) {
                    this.userCtrl.setErrors({'noResult': true})
                }
                this.filteredUsers = this.userCtrl.valueChanges
                    .pipe(
                        startWith(''),
                        map(user => user ? this.autocompleteFilterUser(user) : response.slice())
                    );
            });
        }
        if (target.indexOf('adminUsers') != -1) {
            this.userCtrl = new FormControl();
            this.userCtrl.valueChanges.pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/users/administration', { params: { "search": data } }))
            ).subscribe((response: any) => {
                if (response.length == 0) {
                    this.userCtrl.setErrors({'noResult': true})
                }
                this.filteredUsers = this.userCtrl.valueChanges
                    .pipe(
                        startWith(''),
                        map(user => user ? this.autocompleteFilterUser(user) : response.slice())
                    );
            });
        }

        if (target.indexOf('statuses') != -1) {
            this.statusCtrl = new FormControl();
            this.http.get(this.coreUrl + 'rest/autocomplete/statuses')
                .subscribe((data: any) => {
                    this.statusesList = data;
                    this.filteredStatuses = this.statusCtrl.valueChanges
                        .pipe(
                            startWith(''),
                            map(status => status ? this.autocompleteFilterStatuses(status) : this.statusesList.slice())
                        );
                }, () => {
                    location.href = "index.php";
                });
        }
        if (target.indexOf('usersAndEntities') != -1) {
            this.elementCtrl = new FormControl();
            this.elemList = [];

            this.elementCtrl.valueChanges.pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/users', { params: { "search": data } }))
            ).subscribe((response: any) => {
                this.elemList = response;
            });

            this.elementCtrl.valueChanges.pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/entities', { params: { "search": data } }))
            ).subscribe((response: any) => {
                this.elemList = this.elemList.concat(response);
                if (this.elemList.length == 0) {
                    this.elementCtrl.setErrors({'noResult': true})
                }
                this.filteredElements = this.elementCtrl.valueChanges
                    .pipe(
                        startWith(''),
                        map(elem => elem ? this.autocompleteFilterUser(elem) : this.elemList.slice())
                    );
            });
        }
        if (target.indexOf('entities') != -1) {
            this.elementCtrl = new FormControl();
            this.elementCtrl.valueChanges.pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/entities', { params: { "search": data } }))
            ).subscribe((response: any) => {
                if (response.length == 0) {
                    this.elementCtrl.setErrors({'noResult': true})
                }
                this.filteredElements = this.elementCtrl.valueChanges
                    .pipe(
                        startWith(''),
                        map(elem => elem ? this.autocompleteFilterUser(elem) : response.slice())
                    );
            });

        } else if (target.indexOf('visaUsers') != -1) {
            this.visaUserCtrl = new FormControl();
            this.visaUserCtrl.valueChanges.pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/users/visa', { params: { "search": data } }))
            ).subscribe((response: any) => {
                if (response.length == 0) {
                    this.visaUserCtrl.setErrors({'noResult': true})
                }
                this.filteredVisaUsers = this.visaUserCtrl.valueChanges
                    .pipe(
                        startWith(''),
                        map(user => user ? this.autocompleteFilterUser(user) : response.slice())
                    );
            });

        } else {

        }

    }

    autocompleteFilterVisaUser(name: string) {
        return this.visaUserList.filter(user =>
            user.idToDisplay.toLowerCase().indexOf(name.toLowerCase()) >= 0);
    }

    autocompleteFilterUser(name: string) {
        return this.userList.filter(user =>
            user.idToDisplay.toLowerCase().indexOf(name.toLowerCase()) >= 0);
    }

    autocompleteFilterStatuses(name: string) {
        return this.statusesList.filter(status =>
            status.idToDisplay.toLowerCase().indexOf(name.toLowerCase()) >= 0);
    }

    autocompleteFilterElements(name: string) {
        return this.elemList.filter(elem =>
            elem.idToDisplay.toLowerCase().indexOf(name.toLowerCase()) >= 0);
    }

}