import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { Router } from '@angular/router';
import { AlertController, LoadingController, MenuController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { AuthService } from '../service/auth.service';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'indexation.component.html',
    styleUrls: ['indexation.component.scss'],
    providers: [DatePipe]
})
export class IndexationComponent implements OnInit {

    loading: boolean = false;
    filesToUpload: any[] = [];
    errors: any[] = [];

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
        public datePipe: DatePipe,
    ) { }

    ngOnInit(): void { }

    ionViewWillEnter() {
        this.menu.enable(true, 'left-menu');
        this.menu.enable(true, 'right-menu');

        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }

    onSubmit() {
        if (this.isValid()) {
            this.promptSaveDoc();
        }
    }

    async promptSaveDoc() {
        const alert = await this.alertController.create({
            cssClass: 'alert-info-no-msg',
            header: this.translate.instant('lang.areYouSure'),
            inputs: [
                {
                    name: 'note',
                    id: 'note',
                    type: 'textarea',
                    placeholder: this.translate.instant('lang.addNote')
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
                    handler: (data: any) => {
                        this.loadingController.create({
                            message: 'Enregistrement ...',
                            spinner: 'dots'
                        }).then(async (load: HTMLIonLoadingElement) => {
                            load.present();
                            const objTosend = this.formatData(data.note);
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
            ]
        });
        await alert.present();
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

    formatData(note: string) {
        const today: Date = new Date();
        let noteObj: any = null;
        let linkId: string = null;

        if (note !== '') {
            noteObj =  {
                value : note,
                creator : `${this.authService.user.firstname} ${this.authService.user.lastname}`,
                creationDate : this.datePipe.transform(today, 'dd-MM-y')
            };
        }

        const formattedObj: any[] = [];
        const signedFiles = this.filesToUpload.filter((item: any) => item.mainDocument);
        const attachFiles = this.filesToUpload.filter((item: any) => !item.mainDocument);

        if (signedFiles.length > 1) {
            linkId = this.datePipe.transform(today, 'ddMMYhmmss') + '_' + Math.random().toString(36).substr(2, 9);
        }

        signedFiles.forEach((file: any) => {
            formattedObj.push({
                title: file.title,
                reference : this.datePipe.transform(today, 'y/MM/dd') + '/' + file.reference,
                encodedDocument: file.content,
                isZipped: false,
                linkId: linkId,
                sender: `${this.authService.user.firstname} ${this.authService.user.lastname}`,
                notes: noteObj,
                attachments: attachFiles.map((item: any) => {
                    return {
                        title: item.title,
                        encodedDocument: item.content
                    };
                }),
                workflow: this.appVisaWorkflow.getCurrentWorkflow().map((item: any) => {
                    return {
                        userId: item.userId,
                        mode: this.authService.getWorkflowMode(item.role),
                        signatureMode: this.authService.getSignatureMode(item.role)
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
                    reference: fileInput.target.files[index].name,
                    mainDocument: true,
                    content: ''
                };
                const reader = new FileReader();
                reader.readAsArrayBuffer(fileInput.target.files[index]);
                reader.onload = (value: any) => {
                    file.mainDocument = this.filesToUpload.length === 0;
                    file.reference = this.filesToUpload.length === 0 ? file.reference : '',
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
