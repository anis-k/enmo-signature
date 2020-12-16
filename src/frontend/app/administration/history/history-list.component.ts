import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material/dialog';
import { MatSort, Sort } from '@angular/material/sort';
import { TranslateService } from '@ngx-translate/core';
import { map, finalize, tap, catchError } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { AuthService } from '../../service/auth.service';
import { AlertController, IonInfiniteScroll, MenuController } from '@ionic/angular';
import { of } from 'rxjs';


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
        search : '',
        actions : [],
        date : {
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
    @ViewChild(IonInfiniteScroll) infiniteScroll: IonInfiniteScroll;

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
        public alertController: AlertController
    ) { }

    applyFilter(filterValue: string) {
        filterValue = this.latinisePipe.transform(filterValue.toLowerCase());
        this.filters.field = filterValue;
        this.getDatas();
    }

    ionViewWillEnter() {
        this.menu.enable(true, 'left-menu');
        this.menu.enable(true, 'right-menu');
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
        this.gesActions();
        this.getDatas();
    }

    gesActions() {
        this.actions = [
            'emailAdded',
            'documentViewed'
        ];
    }

    getDatas() {
        this.resources = [];
        this.offset = 0;
        this.resources = [
            {
                type: 'VIEW',
                date : '2020-12-15 17:21:07.854099',
                user : 'Barbara BAIN',
                message : '{documentViewed} : recommande_2D_000_000_0003_1',
                object_id : 103,
                ip : '192.168.1.12',
            },
            {
                type: 'ACTION',
                date : '2020-12-15 17:20:37.304331',
                user : 'Jenny JANE-SUR-SAINT-ETIENNE',
                message : '{actionDone} : VAL',
                object_id : 145,
                ip : '192.168.1.12',
            }
        ];
        /*return new Promise((resolve) => {
            this.http.post(`../rest/search/history?limit=10&offset=0`, this.filters)
                .pipe(
                    tap((data: any) => {
                        this.resources = data.history;
                        this.count = data.count;
                        this.infiniteScroll.disabled = false;
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notificationService.handleErrors(err);
                        resolve(false);
                        return of(false);
                    })
                ).subscribe();
        });*/
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
        if (this.filters.search !== '') {
            nb++;
        }
        if (this.filters.date.start !== null) {
            nb++;
        }
        if (this.filters.date.end !== null) {
            nb++;
        }
        if (this.filters.actions.length > 0) {
            nb++;
        }
        return nb;
    }
}
