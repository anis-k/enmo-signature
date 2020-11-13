import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { MenuController } from '@ionic/angular';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'indexation.component.html',
    styleUrls: ['indexation.component.scss'],
})
export class IndexationComponent implements OnInit {

    loading: boolean = false;
    filesToUpload: any[] = [];
    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

    constructor(
        private menu: MenuController,
        public signaturesService: SignaturesContentService,
        public viewContainerRef: ViewContainerRef,
        public notificationService: NotificationService,
    ) { }

    ngOnInit(): void {
        this.menu.enable(true, 'left-menu');
        // this.menu.open('left-menu');
        this.menu.enable(true, 'right-menu');
        // this.menu.open('right-menu');
    }

    ionViewWillEnter() {
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }

    onSubmit() {
        if (this.isValid()) {
            console.log('ok');
        }
    }

    dndUploadFile(event: any) {
        const fileInput = {
            target: {
                files: [
                    event[0]
                ]
            }
        };
        this.uploadTrigger(fileInput);
    }

    uploadTrigger(fileInput: any) {
        if (fileInput.target.files && fileInput.target.files[0] /*&& this.isExtensionAllowed(fileInput.target.files[0])*/) {
            for (let index = 0; index < fileInput.target.files.length; index++) {
                let file = {
                    title: fileInput.target.files[index].name,
                    mainDocument: true,
                    content: ''
                };
                const reader = new FileReader();
                reader.readAsArrayBuffer(fileInput.target.files[index]);
                reader.onload = (value: any) => {
                    file.mainDocument = this.filesToUpload.length === 0;
                    file.content = this.getBase64Document(value.target.result);
                    this.filesToUpload.push(file);
                };
            }
        } else {
            this.loading = false;
        }
    }

    getBase64Document(buffer: ArrayBuffer) {
        const TYPED_ARRAY = new Uint8Array(buffer);
        const STRING_CHAR = TYPED_ARRAY.reduce((data, byte) => {
            return data + String.fromCharCode(byte);
        }, '');

        return btoa(STRING_CHAR);
    }

    deleteFile(index: number) {
        this.filesToUpload.splice(index, 1);
    }

    isValid() {
        if (this.filesToUpload.filter((item: any) => item.mainDocument).length === 0) {
            this.notificationService.error('Veuillez choisir un document à signer');
            return false;
        } else if (this.appVisaWorkflow.getCurrentWorkflow().length === 0) {
            this.notificationService.error('Veuillez choisir des utilisateurs pour le circuit');
            this.menu.open('right-menu');
            return false;
        } else {
            return true;
        }
    }
}
