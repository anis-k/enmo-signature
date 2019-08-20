import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Input, EventEmitter, Output, ViewChild, ElementRef } from '@angular/core';
import { Observable, of, forkJoin } from 'rxjs';
import { map, startWith, debounceTime, filter, distinctUntilChanged, switchMap, tap } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';

@Component({
    selector: 'plugin-autocomplete',
    templateUrl: 'autocomplete.component.html',
    styleUrls: ['autocomplete.component.scss'],
})
export class PluginAutocompleteComponent implements OnInit {
    myControl = new FormControl();
    loading = false;

    listInfo: string;

    type = {
        user: 'fa-user',
        entity: 'fa-sitemap'
    };

    @Input('singleMode') singleMode: boolean;
    @Input('required') required: boolean;
    @Input('datas') options: any;
    @Input('routeDatas') routeDatas: string[];
    @Input('labelPlaceholder') placeholder: string;
    @Input('targetSearchKey') key: string[];
    @Input('subInfoKey') subInfoKey: string;
    @Input('excludeClause') excludeClause: any[];

    // tslint:disable-next-line:no-output-rename
    @Output('triggerEvent') selectedOpt = new EventEmitter();

    @ViewChild('autoCompleteInput') autoCompleteInput: ElementRef;

    filteredOptions: Observable<string[]>;

    constructor(
        private translate: TranslateService,
        public http: HttpClient,
        private latinisePipe: LatinisePipe
    ) { }

    ngOnInit() {
        this.placeholder = this.placeholder === undefined ? this.translate.instant('lang.chooseValue') : this.placeholder;

        if (this.routeDatas !== undefined) {
            this.initAutocompleteRoute();
        } else {
            this.initAutocompleteData();
        }
    }

    initAutocompleteData() {
        this.listInfo = this.translate.instant('lang.noAvailableValue');
        this.filteredOptions = this.myControl.valueChanges
            .pipe(
                startWith(''),
                map(value => this._filter(value))
            );
    }

    initAutocompleteRoute() {
        this.listInfo = this.translate.instant('lang.autocompleteInfo');
        this.options = [];
        this.myControl.valueChanges
            .pipe(
                tap(() => this.loading = true),
                tap(() => {
                    this.options = [];
                    this.filteredOptions = of(this.options);
                }),
                debounceTime(300),
                tap(() => this.loading = false),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                tap(() => this.loading = true),
                switchMap((data: any) => this.getDatas(data)),
                tap((data: any) => {
                    this.listInfo = data.length === 0 ? this.translate.instant('lang.noAvailableValue') : '';
                    this.options = data;
                    this.filteredOptions = of(this.options);
                    this.loading = false;
                }),
            ).subscribe();
    }

    getDatas(data: string) {
        const arrayObs: any[] = [];
        const test: any = [];
        this.routeDatas.forEach(element => {
            arrayObs.push(this.http.get('..' + element, { params: { 'search': data } }));
        });

        return forkJoin(arrayObs).pipe(
            map(dataMap => {
                dataMap.forEach((element: any) => {
                    element.forEach((element2: any) => {
                        if (this.isExcludeData(element2)) {
                            element2.disabled = true;
                        }
                        test.push(element2);
                    });
                });
                return test;
            })
        );
    }

    isExcludeData(data: any) {
        let state = false;
        if (this.excludeClause !== undefined) {
            this.excludeClause.forEach(clause => {
                if (data[Object.keys(clause)[0]] === (Object.values(clause)[0])) {
                    state = true;
                }
            });
        }
        return state;
    }

    selectOpt(ev: any) {
        if (this.singleMode !== undefined) {
            const fullWord: any[] = [];
            this.key.forEach(element => {
                fullWord.push(ev.option.value[element]);
            });
            this.myControl.setValue(fullWord.join(' '));
        }
        this.resetAutocomplete();
        this.autoCompleteInput.nativeElement.blur();
        this.selectedOpt.emit(ev.option.value);
    }

    resetAutocomplete() {
        if (this.singleMode === undefined) {
            this.myControl.setValue('');
        }
        if (this.routeDatas !== undefined) {
            this.options = [];
            this.listInfo = this.translate.instant('lang.autocompleteInfo');
        }
    }

    private _filter(value: string): string[] {
        if (typeof value === 'string' && value !== '') {
            const filterValue = this.latinisePipe.transform(value.toLowerCase());
            return this.options.filter(
                (option: any) => {
                    let state = false;
                    this.key.forEach(element => {
                        if (this.latinisePipe.transform(option[element].toLowerCase()).includes(filterValue)) {
                            state = true;
                        }
                    });
                    return state;
                }
            );
        } else {
            return this.options;
        }
    }
}
