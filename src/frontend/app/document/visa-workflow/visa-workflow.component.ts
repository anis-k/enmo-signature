import { Component, Input, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../service/auth.service';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../service/notification.service';
import { PopoverController } from '@ionic/angular';
import { VisaWorkflowModelsComponent } from './models/visa-workflow-models.component';

@Component({
    selector: 'app-visa-workflow',
    templateUrl: 'visa-workflow.component.html',
    styleUrls: ['visa-workflow.component.scss'],
})
export class VisaWorkflowComponent implements OnInit {

    loading: boolean = false;

    visaUsersSearchVal: string = '';
    visaUsersList: any = [];
    showVisaUsersList: boolean = false;
    customPopoverOptions = {
        header: 'Roles'
    };
    roles: any[] = [
        {
            id: 'rgs',
            type: 'sign',
            label: 'Signataire (RGS**)',
            color: '#cb4335'
        },
        {
            id: 'stamp',
            type: 'sign',
            label: 'Signataire (Griffe)',
            color: '#f39c12'
        },
        {
            id: 'visa',
            type: 'visa',
            label: 'Viseur',
            color: '#27ae60'
        },
    ];

    @Input() editMode: boolean = false;
    @Input() visaWorkflow: any = [];

    constructor(
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public authService: AuthService,
        public notificationService: NotificationService,
        public popoverController: PopoverController
    ) { }

    ngOnInit(): void {
        this.visaWorkflow.forEach((element: any) => {
            if (element.userPicture === undefined && element.userDisplay !== '') {
                this.http.get('../rest/users/' + element.userId + '/picture')
                    .subscribe((data: any) => {
                        element.userPicture = data.picture;
                    });
            }
        });
    }

    getVisaUsers(ev: any) {
        this.showVisaUsersList = true;
        if (ev.detail.value === '') {
            this.resetVisaUsersList();
        } else if (ev.detail.value.length >= 3) {
            this.http.get('../rest/autocomplete/users?search=' + ev.detail.value).pipe(
                tap((res: any) => {
                    this.visaUsersList = res;
                }),
                catchError(err => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    addUser(user: any, searchInput: any) {
        this.resetVisaUsersList();
        const userObj: any = {
            'userId': user.id,
            'userDisplay': `${user.firstname} ${user.lastname}`,
            'mode': 'visa',
            'processDate': null,
            'current': false,
            'modes': [
                'visa',
                'stamp',
                'rgs'
            ]
        };
        this.visaWorkflow.push(userObj);
        this.getAvatarUser(this.visaWorkflow.length - 1);
        this.visaUsersSearchVal = '';
        searchInput.setFocus();
    }

    removeUser(index: number) {
        this.visaWorkflow.splice(index, 1);
    }

    getAvatarUser(index: number) {
        if (this.visaWorkflow[index].userPicture === undefined && this.visaWorkflow[index].userDisplay !== '') {
            this.http.get('../rest/users/' + this.visaWorkflow[index].userId + '/picture').pipe(
                tap((data: any) => {
                    this.visaWorkflow[index].userPicture = data.picture;
                }),
                catchError(err => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    resetVisaUsersList() {
        this.visaUsersList = [];
    }

    async openVisaWorkflowModels(ev: any) {
        const popover = await this.popoverController.create({
            component: VisaWorkflowModelsComponent,
            componentProps: { currentWorkflow: this.visaWorkflow },
            event: ev,
        });
        await popover.present();

        popover.onDidDismiss()
            .then((result: any) => {
                if (result.role !== 'backdrop') {
                    console.log(result.data);
                    this.visaWorkflow = this.visaWorkflow.concat(result.data);
                    this.visaWorkflow.forEach((element: any, index: number) => {
                        this.getAvatarUser(index);
                    });
                }
            });
    }

    getCurrentWorkflow() {
        return this.visaWorkflow;
    }

    getRole(id: string) {
        return this.roles.filter((mode: any) => mode.id === id)[0];
    }
}
