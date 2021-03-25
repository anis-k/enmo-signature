import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Input, EventEmitter, Output, ViewChild } from '@angular/core';
import { of } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../service/notification.service';

@Component({
    selector: 'plugin-autocomplete',
    templateUrl: 'autocomplete.component.html',
    styleUrls: ['autocomplete.component.scss'],
})
export class PluginAutocompleteComponent implements OnInit {

    @Input() currentItems: any[] = [];
    @Input() singleMode: boolean;
    @Input() required: boolean;
    @Input('datas') options: any;
    @Input() routeDatas: string[];
    @Input('labelPlaceholder') placeholder: string;
    @Input('targetSearchKey') key: string[];
    @Input() subInfoKey: string;

    @Output('triggerEvent') selectedOpt = new EventEmitter();

    @ViewChild('searchInput', { static: false }) searchInput: any;

    myControl = new FormControl();
    loading = false;

    searchValue: string = '';

    listInfo: string;

    editMode: boolean = false;

    itemList: any[] = [];

    constructor(
        public http: HttpClient,
        public notificationService: NotificationService,
    ) { }

    ngOnInit() { }

    getDatas(ev: any) {
        if (ev.detail.value === '') {
            this.itemList = [];
        } else {
            this.http.get('../rest/autocomplete/users?search=' + ev.detail.value).pipe(
                tap((res: any) => {
                    this.itemList = res;
                }),
                catchError(err => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    selectItem(item: any) {
        this.currentItems.push(item);
        this.searchValue = '';
        this.itemList = [];
        this.editMode = false;
        this.selectedOpt.emit(item);
    }

    removeItem(index: number) {
        this.currentItems.splice(index, 1);
    }

    toggleEdit() {
        this.editMode = !this.editMode;
        if (this.editMode) {
            setTimeout(() => {
                this.searchInput.setFocus();
            }, 0);
        }
    }
}
