import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { ActivatedRoute, ParamMap, Router } from '@angular/router';
import { ActionSheetController, AlertController, IonInfiniteScroll, LoadingController, MenuController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { of } from 'rxjs';
import { catchError, exhaustMap, tap } from 'rxjs/operators';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { AuthService } from '../service/auth.service';
import { FunctionsService } from '../service/functions.service';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'search.component.html',
    styleUrls: ['search.component.scss'],
    providers: [DatePipe]
})
export class SearchComponent implements OnInit {

    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

    @ViewChild(IonInfiniteScroll) infiniteScroll: IonInfiniteScroll;

    loading: boolean = false;
    filesToUpload: any[] = [];
    errors: any[] = [];

    filters: any[] = [
        {
            id: 'title',
            type: 'text',
            val: '',
            values: []
        },
        {
            id: 'reference',
            type: 'text',
            val: '',
            values: []
        },
        {
            id: 'documentId',
            type: 'text',
            val: '',
            values: []
        },
        {
            id: 'workflowStates',
            type: 'checkbox',
            val: [],
            values: [
                {
                    id: 'PROG',
                    label: 'lang.inprogress',
                    selected: false
                },
                {
                    id: 'STOP',
                    label: 'lang.interrupt',
                    selected: false
                },
                {
                    id: 'VAL',
                    label: 'lang.end',
                    selected: false
                },
                {
                    id: 'REF',
                    label: 'lang.refused',
                    selected: false
                }
            ]
        },
        {
            id: 'workflowUsers',
            type: 'autocompleteUsers',
            val: [],
            values: []
        }
    ];

    actions: any[] = [
        {
            icon: 'hand-left-outline',
            id: 'interruptWorkflow'
        },
        {
            icon: 'document-outline',
            id: 'newWorkflow'
        },
    ];

    ressources: any[] = [];
    currentFilters: any[] = [];
    offset: number = 0;
    limit: number = 10;
    count: number = 0;
    openedLine = '';
    reActiveInfinite: any;
    documentId: any;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        public router: Router,
        private menu: MenuController,
        public signaturesService: SignaturesContentService,
        public viewContainerRef: ViewContainerRef,
        public notificationService: NotificationService,
        public authService: AuthService,
        public loadingController: LoadingController,
        public alertController: AlertController,
        public actionSheetController: ActionSheetController,
        public datePipe: DatePipe,
        public functionsService: FunctionsService,
        private _activatedRoute: ActivatedRoute,
    ) { }

    ngOnInit(): void {
        this._activatedRoute.queryParamMap.subscribe((paramMap: ParamMap) => {
            if (!this.functionsService.empty(paramMap.get('documentId'))) {
                this.documentId = paramMap.get('documentId');
                this.filters.filter((element: any) => element.id === 'documentId')[0].val = this.documentId;
                this.search();
            }
        });
    }

    ionViewWillEnter() {
        this.menu.enable(true, 'left-menu');
        this.menu.enable(true, 'right-menu');
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
        setTimeout(() => {
            this.menu.open('right-menu');
        }, 500);
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }

    toggleSlide(slidingItem: any, resId: any) {
        slidingItem.getOpenAmount()
            .then((res: any) => {
                if (res === 0) {
                    this.openedLine = resId;
                    slidingItem.open('end');
                } else {
                    this.openedLine = '';
                    slidingItem.close('end');
                }
            });
    }

    onSliding(ev: any, resId: any) {
        if (ev.detail.ratio === 1) {
            this.openedLine = resId;
        } else {
            this.openedLine = '';
        }
    }

    onSubmit() {
        this.search();
        this.menu.close('right-menu');
    }

    toggleItem(filter: any, item: any, state: any) {
        if (!state) {
            const index = filter.val.indexOf(item.id);
            filter.val.splice(index, 1);
        } else {
            filter.val.push(item.id);
        }
    }

    formatDatas() {
        const objToSend: any = {};
        const tmpArr = JSON.parse(JSON.stringify(this.filters.filter((filter: any) => (filter.type === 'text' && filter.val !== '') || (filter.type !== 'text' && filter.val.length > 0))));

        tmpArr.forEach((filter: any) => {
            if (filter.id === 'workflowUsers') {
                objToSend[filter.id] = filter.val.map((item: any) => item.id);
            } else if (filter.id === 'workflowStates') {
                objToSend[filter.id] = filter.values.filter((item: any) => item.selected).map((item: any) => item.id);
            } else {
                objToSend[filter.id] = filter.val;
            }
        });
        return objToSend;
    }

    formatListDatas(data: any) {
        return data.map((res: any) => ({
            ...res,
            reason: this.getReason(res),
            currentUser: this.getCurrentUser(res)
        }));
    }

    getNbFilters() {
        let nb_filters = 0;
        for (let index = 0; index < this.currentFilters.length; index++) {
            if (!Array.isArray(this.currentFilters[index].val) && this.currentFilters[index].val !== '') {
                nb_filters++;
            }

            if (Array.isArray(this.currentFilters[index].val) && this.currentFilters[index].val.length > 0) {
                nb_filters += this.currentFilters[index].val.length;
            }
        }
        return nb_filters;
    }

    async openActions(item: any) {
        const buttons: any[] = [];
        this.actions.forEach(element => {
            if (this.canShowButton(element.id, item)) {
                buttons.push({
                    text: item.state === 'PROG' && element.id === 'newWorkflow' ? this.translate.instant('lang.' + element.id + 'Prog') : this.translate.instant('lang.' + element.id),
                    icon: element.icon,
                    handler: () => {
                        this[element.id](item);
                    }
                });
            }
        });
        const actionSheet = await this.actionSheetController.create({
            header: this.translate.instant('lang.actions') + (item.reference !== null ? ' - ' + item.reference : ''),
            buttons: buttons
        });
        await actionSheet.present();
    }

    canShowButton(id: string, item: any) {
        if (id === 'interruptWorkflow' && item.canInterrupt) {
            return true;
        } else if (id === 'newWorkflow' && item.canReaffect) {
            return true;
        } else {
            return false;
        }
    }

    search() {
        this.loadingController.create({
            message: this.translate.instant('lang.processing'),
            spinner: 'dots'
        }).then(async (load: HTMLIonLoadingElement) => {
            load.present();
            await this.launchSearch();
            load.dismiss();
        });
    }

    launchSearch() {
        this.ressources = [];
        this.offset = 0;
        this.refreshCurrentFilter();

        return new Promise((resolve) => {
            this.http.post('../rest/search/documents?limit=10&offset=0', this.formatDatas())
                .pipe(
                    tap((data: any) => {
                        this.ressources = this.formatListDatas(data.documents);
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
        });
    }

    refreshCurrentFilter() {
        this.currentFilters = JSON.parse(JSON.stringify(this.filters.filter((item: any) => !this.functionsService.empty(item.val))));

        if (this.currentFilters.filter((item: any) => item.id === 'workflowStates').length > 0) {
            this.currentFilters.filter((item: any) => item.id === 'workflowStates')[0].val = this.currentFilters.filter((item: any) => item.id === 'workflowStates')[0].values.filter((item: any) => item.selected);
            if (this.currentFilters.filter((item: any) => item.id === 'workflowStates')[0].val.length === 0) {
                this.currentFilters = this.currentFilters.filter((item: any) => item.id !== 'workflowStates');
            }
        }
    }

    loadData(event: any) {
        if (this.count <= this.limit) {
            event.target.complete();
            event.target.disabled = true;
        } else {
            this.offset = this.offset + this.limit;

            this.http.post('../rest/search/documents?limit=' + this.limit + '&offset=' + this.offset, this.formatDatas()).pipe(
                tap((data: any) => {
                    this.ressources = this.ressources.concat(this.formatListDatas(data.documents));
                    event.target.complete();
                    if (this.count === this.ressources.length) {
                        event.target.disabled = true;
                    }
                })
            ).subscribe();
        }
    }

    async interruptWorkflow(item: any) {
        return new Promise(async (resolve) => {
            const confirm = await this.alertController.create({
                header: this.translate.instant('lang.warning'),
                message: this.translate.instant('lang.warnInterrupt'),
                buttons: [
                    {
                        text: this.translate.instant('lang.cancel'),
                        role: 'cancel',
                        cssClass: 'secondary',
                        handler: () => { }
                    },
                    {
                        text: this.translate.instant('lang.validate'),
                        handler: () => {
                            this.loadingController.create({
                                message: this.translate.instant('lang.processing'),
                                spinner: 'dots'
                            }).then(async (load: HTMLIonLoadingElement) => {
                                load.present();
                                await this.launchInterrupt(item);
                                this.launchSearch();
                                resolve(true);
                                load.dismiss();
                            });
                        }
                    }
                ]
            });
            await confirm.present();
        });
    }

    launchInterrupt(item: any) {
        return new Promise((resolve) => {
            this.http.put(`../rest/documents/${item.id}/workflows/interrupt`, {})
                .pipe(
                    tap(() => {
                        this.notificationService.success('lang.documentInterrupted');
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

    async newWorkflow(item: any) {
        if (item.state === 'PROG') {
            await this.interruptWorkflow(item);
        }
        this.router.navigate(['/indexation'], { state: { documentId: item.id } });
    }

    async openPromptProof(item: any) {
        const alert = await this.alertController.create({
            cssClass: 'promptProof',
            header: this.translate.instant('lang.download'),
            inputs: [
                {
                    name: 'option1',
                    type: 'radio',
                    label: this.translate.instant('lang.proof'),
                    value: 'onlyProof',
                    checked: true
                },
                {
                    name: 'option1',
                    type: 'radio',
                    label: this.translate.instant('lang.completeFolder'),
                    value: 'all',
                },

            ],
            buttons: [
                {
                    text: this.translate.instant('lang.cancel'),
                    role: 'cancel',
                    cssClass: 'secondary',
                    handler: () => { }
                },
                {
                    text: this.translate.instant('lang.validate'),
                    handler: async (mode) => {
                        await this.downloadProof(item, mode);
                        alert.dismiss();
                    }
                }
            ]
        });
        await alert.present();
    }

    downloadProof(item: any, mode: string) {
        const onlyProof = mode === 'onlyProof' ? '&onlyProof=true' : '';

        return new Promise((resolve) => {
            this.http.get(`../rest/documents/${item.id}/proof?mode=stream${onlyProof}`, { responseType: 'blob' as 'json' })
                .pipe(
                    tap((data: any) => {
                        const today = new Date();
                        const filename = 'proof_' + item.id + '_' + this.datePipe.transform(today, 'dd-MM-y') + '.' + data.type.replace('application/', '');
                        const downloadLink = document.createElement('a');
                        downloadLink.href = window.URL.createObjectURL(data);
                        downloadLink.setAttribute('download', filename);
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
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

    getReason(item: any) {
        return item.workflow.map((line: any) => line.reason).filter((reason: any) => reason !== null);
    }

    getCurrentUser(item: any) {
        const currentUserWorkflow = item.workflow.filter((line: any) => line.current === true);
        return currentUserWorkflow.length > 0 ? currentUserWorkflow[0].userId : null;
    }

    goTo(resId: number) {
        this.router.navigate([`/documents/${resId}`]);
    }

    clearFilters() {
        for (let index = 0; index < this.filters.length; index++) {
            if (!Array.isArray(this.filters[index].val) && this.filters[index].val !== '') {
                this.filters[index].val = '';
            }

            if (Array.isArray(this.filters[index].val)) {
                this.filters[index].val = [];
                this.filters[index].values = this.filters[index].values.map((item: any) => ({
                    ...item,
                    selected : false
                }));
            }
        }
        if (this.ressources.length > 0) {
            this.launchSearch();
        }
    }

    removeFilter(filter: any, item: any) {
        if (!Array.isArray(filter.val)) {
            this.filters.find((element: any) => element.id === filter.id).val = '';
        } else {
            if (filter.id === 'workflowStates') {
                this.filters.find((element: any) => element.id === filter.id).values.filter((element: any) => element.id === item)[0].selected = false;
            } else {
                const index = filter.val.indexOf(item);
                this.filters.filter((element: any) => element.id === filter.id)[0].val.splice(index, 1);
            }
        }
        this.launchSearch();
    }

    getLabel(filter: any) {
        const obj = this.filters.filter((item: any) => item.id === 'workflowStates')[0].values;
        return obj.find((element: any) => element.id === filter).label;
    }

    checkInput() {
        if ((this.filters.find((el: any) => el.id === 'title').val === '') && (this.filters.find((el: any) => el.id === 'reference').val === '') && (this.filters.find((el: any) => el.id === 'documentId').val === '')) {
            if ((this.filters.find((el: any) => el.id === 'workflowStates').val.length === 0) && (this.filters.find((el: any) => el.id === 'workflowUsers').val.length === 0)) {
                this.clearFilters();
                this.currentFilters = [];
            }
        }
    }
}
