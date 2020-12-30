import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { MatSort, Sort } from '@angular/material/sort';
import { TranslateService } from '@ngx-translate/core';
import { tap, catchError } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { AuthService } from '../../service/auth.service';
import { AlertController, MenuController } from '@ionic/angular';
import { of } from 'rxjs';
import { SortPipe } from '../../plugins/sorting.pipe';


export interface User {
    id: string;
    firstname: string;
    lastname: string;
    login: string;
    email: string;
    subtitute: boolean;
}

@Component({
    selector: 'app-administration-history-list',
    templateUrl: 'history-list.component.html',
    styleUrls: ['history-list.component.scss'],
    providers: [SortPipe]
})

export class HistoryListComponent {

    sortedData: any[];
    displayedColumns: string[] = ['creation_date', 'user', 'info', 'ip'];
    loading: boolean = true;

    resources: any[] = [];
    offset: number = 0;
    limit: number = 10;
    count: number = 0;

    filters: any = {
        user: '',
        messageTypes: [],
        date: {
            start: null,
            end: null
        }
    };

    aventTypesIcon: any = {
        'VIEW': 'eye-outline',
        'CREATION': 'add-circle-outline',
        'ACTION': 'settings-outline',
        'THUMBNAIL': 'image-outline',
        'EMAIL': 'mail-outline',
        'SUPPRESSION': 'trash-bin-outline',
        'MODIFICATION': 'create-outline',
        'LOGIN': 'log-in-outline'
    };

    actions: any[] = [];
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        private menu: MenuController,
        private latinisePipe: LatinisePipe,
        public viewContainerRef: ViewContainerRef,
        public dialog: MatDialog,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public authService: AuthService,
        public alertController: AlertController,
        public sortPipe: SortPipe
    ) { }

    applyFilter(filterValue: string) {
        filterValue = this.latinisePipe.transform(filterValue.toLowerCase());
        this.filters.user = filterValue;
        this.getDatas();
    }

    openFilters() {
        this.menu.open('right-menu');
    }

    ionViewWillEnter() { 
        this.menu.enable(true, 'left-menu');
        this.menu.enable(true, 'right-menu');
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
        this.gesActions();
        this.getDatas();
        setTimeout(() => {
            $(".checkedAction").each((index, element) => {
                if (this.filters.messageTypes.includes($(element).val())) {
                    $(element).prop("checked", true);
                }
            });
        }, 100);
    }

    gesActions() {
        this.http.get(`../rest/history/messageTypes`, this.filters)
            .pipe(
                tap((data: any) => {
                    this.actions = data.messageTypes.map((item: any) => {
                        return {
                            id: item,
                            label: this.translate.instant('lang.' + item)
                        };
                    });
                    this.actions = this.sortPipe.transform(this.actions, 'label');   
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
    }

    getDatas() {
        this.resources = [];
        this.offset = 0;
        return new Promise((resolve) => {
            this.http.post(`../rest/history?limit=10&offset=0`, this.filters)
                .pipe(
                    tap((data: any) => {
                        this.resources = data.history;
                        this.count = data.total;                                                
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notificationService.handleErrors(err);
                        resolve(false);
                        return of(false);
                    })
                ).subscribe();
        });
    }

    sortData(sort: Sort) {
        console.log(sort);
    }

    loadData(event: any) {
        if (this.count <= this.limit) {
            event.target.complete();
            event.target.disabled = true;
        } else {
            this.offset = this.offset + this.limit;

            this.http.post('../rest/history?limit=' + this.limit + '&offset=' + this.offset, this.filters).pipe(
                tap((data: any) => {
                    this.resources = this.resources.concat(data.history);
                    event.target.complete();
                    if (this.count === this.resources.length) {
                        event.target.disabled = true;
                    }
                })
            ).subscribe();
        }
    }

    getNbFilters() {
        let nb = 0;
        if (this.filters.user !== '') {
            nb++;
        }
        if (this.filters.date.start !== null) {
            nb++;
        }
        if (this.filters.date.end !== null) {
            nb++;
        }
        if (this.uniqueArray(this.filters.messageTypes).length > 0) {
            nb += this.uniqueArray(this.filters.messageTypes).length;
        }
        return nb;
    }

    toggleAction(ev: any) {
        if (ev.checked) {
            this.filters.messageTypes.push(ev.value);
        } else {
            this.filters.messageTypes = this.filters.messageTypes.filter((item: any) => item !== ev.value);
        }
        this.getDatas();
    }

    clearFilters() {
        $(".checkedAction").each(function(){
            $(this).prop("checked", false);
        });  
        document.querySelector('ion-searchbar').getInputElement().then((searchInput) => {
            searchInput.value = '';
         });  
        this.filters.user = ''; 
        this.filters.date.start = this.filters.date.end = null;
    }

    removeFilter(filter: any) {
        if (this.filters.messageTypes.includes(filter)) {
            $(".checkedAction").each(function() {
                if ($(this).val() === filter) {
                    $(this).prop("checked", false);
                    return false;
                }
            }); 
        }
        if (this.filters.user === filter) {
            document.querySelector('ion-searchbar').getInputElement().then((searchInput) => {
                searchInput.value = '';
            });  
            this.filters.user = '';   
        }
        if (this.filters.date.start === filter) {
            this.filters.date.start = null;
        }
        if (this.filters.date.end === filter) {
            this.filters.date.end = null;
        }
    }

    // to remove duplicates in array
    uniqueArray(array: any[]) {
        let i: number, j: any;
        const length = array.length;
        const uniqueArray = [];
        const obj = {};
        for (i = 0; i < length; i++) {
            obj[array[i]] = 0;
        }
        for (j in obj) {
            uniqueArray.push(j);
        }
        array = uniqueArray;
        return array;
    }
}
