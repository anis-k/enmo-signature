import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { isNgTemplate } from '@angular/compiler';
import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { Router } from '@angular/router';
import { ActionSheetController, AlertController, LoadingController, MenuController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { AuthService } from '../service/auth.service';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'search.component.html',
    styleUrls: ['search.component.scss'],
})
export class SearchComponent implements OnInit {

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
            id: 'workflowStates',
            type: 'checkbox',
            val: [],
            values: [
                {
                    id: 'PROG',
                    label: 'lang.inprogress'
                },
                {
                    id: 'STOP',
                    label: 'lang.interrupt'
                },
                {
                    id: 'VAL',
                    label: 'lang.end'
                },
                {
                    id: 'REF',
                    label: 'lang.refused'
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
    offset: number = 0;
    limit: number = 10;
    count: number = 0;

    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

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
        public actionSheetController: ActionSheetController
    ) { }

    ngOnInit(): void { }

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

    onSubmit() {
        console.log(this.formatDatas());
        this.search();
        this.menu.close('right-menu');
    }

    toggleItem(filter: any, item: any, state: boolean) {
        if (!state) {
            const index = filter.val.indexOf(item.id);
            filter.val.splice(index, 1);
        } else {
            filter.val.push(item.id);
        }
    }

    formatDatas() {
        const objToSend: any = {};
        const tmpArr = this.filters.filter((filter: any) => (filter.type === 'text' && filter.val !== '') || (filter.type !== 'text' && filter.val.length > 0));

        tmpArr.forEach((filter: any) => {
            if (filter.id === 'workflowUsers') {
                objToSend[filter.id] = filter.val.map((item: any) => item.id);
            } else {
                objToSend[filter.id] = filter.val;
            }
        });
        return objToSend;
    }

    async openActions(item: any) {
        const buttons: any[] = [];
        this.actions.forEach(element => {
            if (this.canShowButton(element.id, item)) {
                buttons.push({
                    text: this.translate.instant('lang.' + element.id),
                    icon: element.icon,
                    handler: () => {
                        this[element.id](item);
                    }
                });
            }
        });
        const actionSheet = await this.actionSheetController.create({
            header: this.translate.instant('lang.actions') + ' - ' + item.reference,
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
        return new Promise((resolve) => {
            this.http.post(`../rest/search/documents?limit=10&offset=0`, this.formatDatas())
                .pipe(
                    tap((data: any) => {
                        this.ressources = data.documents.map((res: any) => {
                            return {
                                ...res,
                                reason : this.getReason(res)
                            };
                        });
                        this.count = data.count;
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

    loadData(event: any) {
        this.offset = this.offset + this.limit;

        this.http.post('../rest/search/documents?limit=' + this.limit + '&offset=' + this.offset, this.formatDatas()).pipe(
            tap((data: any) => {
                this.ressources = this.ressources.concat(data.documents.map((res: any) => {
                    return {
                        ...res,
                        reason : this.getReason(res)
                    };
                }));
                event.target.complete();
                if (this.count === this.ressources.length) {
                    event.target.disabled = true;
                }
            })
        ).subscribe();
    }

    async interruptWorkflow() {
        const alert = await this.alertController.create({
            header: this.translate.instant('lang.warning'),
            message: this.translate.instant('lang.areYouSure'),
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
                            // DO SOMETHING
                            setTimeout(() => {
                                load.dismiss();
                            }, 3000);
                        });
                    }
                }
            ]
        });
        await alert.present();
    }

    async newWorkflow(item: any) {
        this.router.navigate(['/indexation'], { state: { documentId: item.id } });
    }

    async openPromptProof(item: any) {
        const alert = await this.alertController.create({
            header: this.translate.instant('lang.proof'),
            inputs: [
                {
                    name: 'option1',
                    type: 'checkbox',
                    label: 'Option 1',
                    value: 'value1',
                },
                {
                    name: 'option2',
                    type: 'checkbox',
                    label: 'Option 2',
                    value: 'value2'
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
                    handler: async () => {
                        await this.downloadProof(item);
                        alert.dismiss();
                    }
                }
            ]
        });
        await alert.present();
    }

    downloadProof(item: any) {
        return new Promise((resolve) => {
            this.http.get(`../rest/documents/${item.id}/proof`)
                .pipe(
                    tap(() => {
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
}
