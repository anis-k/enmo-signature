import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { AlertController, PopoverController } from '@ionic/angular';
import { NotificationService } from '../../../service/notification.service';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { AuthService } from '../../../service/auth.service';

@Component({
    selector: 'app-visa-workflow-models',
    templateUrl: 'visa-workflow-models.component.html',
    styleUrls: ['visa-workflow-models.component.scss'],
})
export class VisaWorkflowModelsComponent implements OnInit {

    @Input() currentWorkflow: any[] = [];

    visaWorkflowModels: any[] = [];

    constructor(
        public http: HttpClient,
        public popoverController: PopoverController,
        public alertController: AlertController,
        public notificationService: NotificationService,
        public authService: AuthService
    ) { }

    ngOnInit(): void {
        this.getVisaUserModels();
    }

    async createModel() {
        const alert = await this.alertController.create({
            header: 'Nouveau modèle',
            message: 'Le circuit en cours sera sauvegardé.',
            inputs: [
                {
                    name: 'title',
                    type: 'text',
                    placeholder: 'Label'
                },
            ],
            buttons: [
                {
                    text: 'Cancel',
                    role: 'cancel',
                    cssClass: 'secondary',
                    handler: () => { }
                }, {
                    text: 'Ok',
                    handler: (data: any) => {
                        if (data.title !== '') {
                            this.saveModel(data.title);
                            return true;
                        } else {
                            this.notificationService.error('Label mandatory');
                            return false;
                        }
                    }
                }
            ]
        });

        await alert.present();
    }

    saveModel(title: string) {
        const objToSend: any = {
            title: title,
            items: this.currentWorkflow.map((item: any) => {
                return {
                    userId: item.userId,
                    mode: this.authService.getWorkflowMode(item.role),
                    signatureMode: this.authService.getSignatureMode(item.role)
                };
            })
        };
        this.http.post('../rest/workflowTemplates', objToSend).pipe(
            tap((res: any) => {
                this.notificationService.success('Modèle créé');
                this.visaWorkflowModels.push({ id: res.id, title: title });
            }),
            catchError(err => {
                this.notificationService.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    async removeModel(model: any) {
        const alert = await this.alertController.create({
            header: 'Supprimer',
            message: 'Supprimer le modèle ?',
            buttons: [
                {
                    text: 'Non',
                    role: 'cancel',
                    cssClass: 'secondary',
                    handler: () => { }
                }, {
                    text: 'Oui',
                    handler: () => {
                        this.http.delete(`../rest/workflowTemplates/${model.id}`).pipe(
                            tap(() => {
                                this.visaWorkflowModels = this.visaWorkflowModels.filter((item: any) => item.id !== model.id);
                                this.notificationService.success(`Modèle ${model.title} supprimé`);
                            }),
                            catchError(err => {
                                this.notificationService.handleErrors(err);
                                return of(false);
                            })
                        ).subscribe();
                    }
                }
            ]
        });

        await alert.present();
    }

    getVisaUserModels() {
        this.http.get('../rest/workflowTemplates').pipe(
            tap((data: any) => {
                this.visaWorkflowModels = data.workflowTemplates;
            }),
            catchError(err => {
                this.notificationService.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    loadVisaWorkflow(model: any) {
        this.http.get(`../rest/workflowTemplates/${model.id}`).pipe(
            tap((data: any) => {
                const workflows: any[] = data.workflowTemplate.items.map((item: any) => {
                    const obj: any = {
                        'userId': item.userId,
                        'userDisplay': item.userLabel,
                        'role': item.mode === 'visa' ? 'visa' : item.signatureMode,
                        'processDate': null,
                        'current': false,
                        'modes': [
                            'visa',
                            'sign',
                            'stamp',
                        ]
                    };
                    return obj;
                });
                this.popoverController.dismiss(workflows);
            }),
            catchError(err => {
                this.notificationService.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
