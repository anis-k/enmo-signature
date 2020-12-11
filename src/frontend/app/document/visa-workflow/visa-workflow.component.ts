import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../service/auth.service';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../service/notification.service';
import { IonReorderGroup, PopoverController } from '@ionic/angular';
import { VisaWorkflowModelsComponent } from './models/visa-workflow-models.component';
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
        if (this.canMoveUser(ev)) {
            this.visaWorkflow = ev.detail.complete(this.visaWorkflow);
        } else {
            this.notificationService.error('lang.errorUserSignType');
            ev.detail.complete(false);
        }
    }

    canMoveUser(ev: CustomEvent<ItemReorderEventDetail>) {
        const newWorkflow = this.array_move(this.visaWorkflow.slice(), ev.detail.from, ev.detail.to);
        const res = this.isValidWorkflow(newWorkflow);
        return res;
    }

    isValidWorkflow(workflow: any = this.visaWorkflow) {
        let res: boolean = true;
        workflow.forEach((item: any, indexUserRgs: number) => {
            if (['visa', 'stamp'].indexOf(item.role) === -1) {
                if (workflow.filter((itemUserStamp: any, indexUserStamp: number) => indexUserStamp > indexUserRgs && itemUserStamp.role === 'stamp').length > 0) {
                    console.log('false!');
                    res = false;
                }
            }
        });
        return res;
    }

    array_move(arr: any, old_index: number, new_index: number) {
        if (new_index >= arr.length) {
            let k = new_index - arr.length + 1;
            while (k--) {
                arr.push(undefined);
            }
        }
        arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
        return arr; // for testing
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
        if (!this.isValidWorkflow()) {
            this.visaWorkflow[this.visaWorkflow.length - 1].role = 'visa';
        }
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

    isValidRole(indexWorkflow: any, role: string, currentRole: string) {
        if (this.visaWorkflow.filter((item: any, index: any) => index > indexWorkflow && ['stamp'].indexOf(item.role) > -1).length > 0 && ['visa', 'stamp'].indexOf(currentRole) > -1 && ['visa', 'stamp'].indexOf(role) === -1) {
            return false;
        } else if (this.visaWorkflow.filter((item: any, index: any) => index < indexWorkflow && ['visa', 'stamp'].indexOf(item.role) === -1).length > 0 && role === 'stamp') {
            return false;
        } else {
            return true;
        }
    }
}
