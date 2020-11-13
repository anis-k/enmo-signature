import { HttpClient } from '@angular/common/http';
import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { Router } from '@angular/router';
import { LoadingController, MenuController } from '@ionic/angular';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { AuthService } from '../service/auth.service';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'indexation.component.html',
    styleUrls: ['indexation.component.scss'],
})
export class IndexationComponent implements OnInit {

    loading: boolean = false;
    filesToUpload: any[] = [];
    errors: any[] = [];

    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

    constructor(
        public http: HttpClient,
        public router: Router,
        private menu: MenuController,
        public signaturesService: SignaturesContentService,
        public viewContainerRef: ViewContainerRef,
        public notificationService: NotificationService,
        public authService: AuthService,
        public loadingController: LoadingController,
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
            this.loadingController.create({
                message: 'Enregistrement ...',
                spinner: 'dots'
            }).then(async (load: HTMLIonLoadingElement) => {
                load.present();
                const objTosend = this.formatData();
                for (let index = 0; index < objTosend.length; index++) {
                    await this.saveDocument(objTosend[index], index);
                }
                load.dismiss();
                if (this.errors.length === 0) {
                    this.notificationService.success('Document(s) importé(s)');
                    this.router.navigate(['/home']);
                }
            });
        }
    }

    saveDocument(data: any, index: number) {
        return new Promise((resolve) => {
            this.http.post('../rest/documents', data).pipe(
                tap(() => {
                }),
                finalize(() => resolve(true)),
                catchError((err: any) => {
                    this.errors.push(data.title);
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    formatData() {
        const formattedObj: any[] = [];
        const signedFiles = this.filesToUpload.filter((item: any) => item.mainDocument);
        const attachFiles = this.filesToUpload.filter((item: any) => !item.mainDocument);

        signedFiles.forEach((file: any) => {
            formattedObj.push({
                title: file.title,
                encodedDocument: file.content,
                isZipped: false,
                sender: `${this.authService.user.firstname} ${this.authService.user.lastname}`,
                attachments: attachFiles.map((item: any) => {
                    return {
                        title: item.title,
                        encodedDocument: item.content
                    };
                }),
                workflow: this.appVisaWorkflow.getCurrentWorkflow().map((item: any) => {
                    return {
                        userId: item.userId,
                        mode: item.mode
                    };
                })
            });
        });

        return formattedObj;
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
        if (fileInput.target.files && fileInput.target.files[0] && this.isExtensionAllowed(fileInput.target.files)) {
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

    isExtensionAllowed(files: any[]) {
        for (let index = 0; index < files.length; index++) {
            if (files[index].name.toLowerCase().split('.').pop() !== 'pdf') {
                this.notificationService.error('Seul des fichiers pdf sont autorisés');
                return false;
            }
        }
        return true;
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
