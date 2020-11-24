import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../service/auth.service';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../service/notification.service';
import { IonReorderGroup, PopoverController } from '@ionic/angular';
import { VisaWorkflowModelsComponent } from './models/visa-workflow-models.component';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { ItemReorderEventDetail } from '@ionic/core';

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
    roles: any[] = [];

    @Input() editMode: boolean = false;
    @Input() visaWorkflow: any = [];
    @ViewChild(IonReorderGroup) reorderGroup: IonReorderGroup;

    constructor(
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public authService: AuthService,
        public notificationService: NotificationService,
        public popoverController: PopoverController
    ) { }

    ngOnInit(): void {
        this.visaWorkflow.forEach((element: any, index: number) => {
            this.getAvatarUser(index);
        });
    }

    doReorder(ev: CustomEvent<ItemReorderEventDetail>) {
        this.visaWorkflow = ev.detail.complete(this.visaWorkflow);
    }

    drop(event: CdkDragDrop<string[]>) {
        moveItemInArray(this.visaWorkflow, event.previousIndex, event.currentIndex);
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

        user.signatureModes.unshift('visa');

        const userObj: any = {
            'userId': user.id,
            'userDisplay': `${user.firstname} ${user.lastname}`,
            'role': user.signatureModes[user.signatureModes.length - 1],
            'processDate': null,
            'current': false,
            'modes': user.signatureModes
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
        return this.authService.signatureRoles.filter((mode: any) => mode.id === id)[0];
    }

    loadWorkflow(workflow: any) {
        this.visaWorkflow = workflow;
    }
}
