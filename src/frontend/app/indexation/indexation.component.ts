import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, ElementRef, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { Router } from '@angular/router';
import { AlertController, LoadingController, MenuController, ModalController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { AuthService } from '../service/auth.service';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';
import { SignaturePositionComponent } from './signature-position/signature-position.component';

@Component({
    templateUrl: 'indexation.component.html',
    styleUrls: ['indexation.component.scss'],
    providers: [DatePipe]
})
export class IndexationComponent implements OnInit {

    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;
    @ViewChild('docToUpload') fileImport: ElementRef;

    loading: boolean = false;
    filesToUpload: any[] = [];
    errors: any[] = [];
    fromDocument: number = null;

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
        public modalController: ModalController
    ) { }

    ngOnInit(): void { }

    ionViewWillEnter() {
        this.menu.enable(true, 'left-menu');
        this.menu.enable(true, 'right-menu');

        this.filesToUpload = [];
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');

        if (window.history.state.documentId !== undefined) {
            this.fromDocument = window.history.state.documentId;
            this.getDocumentData(this.fromDocument);
        }
    }

    getDocumentData(resId: number) {
        return new Promise((resolve) => {
            this.http.get(`../rest/documents/${resId}`).pipe(
                tap((data: any) => {
                    let ref = '';
                    let arrRef = data.document.reference.split('/');
                    arrRef = arrRef.slice(3, arrRef.length);

                    if (arrRef.length > 0) {
                        ref = arrRef.join('/');
                    }

                    this.filesToUpload.push({
                        title: data.document.title,
                        reference: ref,
                        mainDocument: true,
                        content: '',
                        linkId: data.document.linkId,
                        metadata: data.document.metadata,
                    });
                    this.getDocumentContent(resId);
                    this.appVisaWorkflow.loadWorkflow(data.document.workflow.map((item: any) => {
                        item.userSignatureModes.unshift('visa');
                        return {
                            ...item,
                            'processDate': null,
                            'current': false,
                            'role': item.mode === 'visa' ? 'visa' : item.signatureMode,
                            'modes': item.userSignatureModes
                        };
                    }));
                    for (let index = 0; index < data.document.attachments.length; index++) {
                        this.getAttachment(data.document.attachments[index].id);
                    }
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getDocumentContent(resId: number) {
        return new Promise((resolve) => {
            this.http.get(`../rest/documents/${resId}/content?type=original`).pipe(
                tap((data: any) => {
                    this.filesToUpload[0].content = data.encodedDocument;
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getAttachment(attachId: number) {
        return new Promise((resolve) => {
            this.http.get(`../rest/attachments/${attachId}`).pipe(
                tap((data: any) => {
                    this.filesToUpload.push({
                        title: data.attachment.title,
                        mainDocument: false,
                        content: data.attachment.encodedDocument
                    });
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notificationService.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
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
                            message: this.translate.instant('lang.processing'),
                            spinner: 'dots'
                        }).then(async (load: HTMLIonLoadingElement) => {
                            load.present();
                            const objTosend = this.formatData(data.note);
                            for (let index = 0; index < objTosend.length; index++) {
                                await this.saveDocument(objTosend[index], index);
                            }
                            load.dismiss();
                            if (this.errors.length === 0) {
                                this.notificationService.success('lang.documentsImported');
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
                    this.errors = [];
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
            noteObj = {
                value: note,
                creator: `${this.authService.user.firstname} ${this.authService.user.lastname}`,
                creationDate: this.datePipe.transform(today, 'dd-MM-y')
            };
        }

        const formattedObj: any[] = [];
        const signedFiles = this.filesToUpload.filter((item: any) => item.mainDocument);
        const attachFiles = this.filesToUpload.filter((item: any) => !item.mainDocument);

        if (signedFiles.length > 1) {
            linkId = this.datePipe.transform(today, 'ddMMYhmmss') + '_' + Math.random().toString(36).substr(2, 9);
        }

        signedFiles.forEach((file: any) => {
            const metadata = {};
            if (this.fromDocument !== null) {
                file.metadata.forEach((element: any) => {
                    metadata[element.label] = element.value;
                });
            }
            let formattedReference = '';
            if (file.reference !== '') {
                formattedReference = this.datePipe.transform(today, 'y/MM/dd') + '/' + file.reference;
            }
            formattedObj.push({
                title: file.title,
                reference: formattedReference,
                encodedDocument: file.content,
                isZipped: false,
                linkId: this.fromDocument !== null ? file.linkId : linkId,
                sender: `${this.authService.user.firstname} ${this.authService.user.lastname}`,
                notes: noteObj,
                attachments: attachFiles.map((item: any) => ({
                    title: item.title,
                    encodedDocument: item.content
                })),
                workflow: this.appVisaWorkflow.getCurrentWorkflow().map((item: any, index: number) => ({
                    userId: item.userId,
                    mode: this.authService.getWorkflowMode(item.role),
                    signatureMode: this.authService.getSignatureMode(item.role),
                    signaturePositions: item.signaturePositions !== undefined ? this.formatPositions(item.signaturePositions.filter((pos: any) => pos.mainDocument === file.mainDocument && file.signPos !== undefined)).map((itemFile: any) => ({
                        page: itemFile.page,
                        positionX: itemFile.position.positionX,
                        positionY: itemFile.position.positionY,
                    })) : []
                })),
                metadata: metadata
            });
        });

        return formattedObj;
    }

    formatPositions(position: any) {
        delete position.mainDocument;
        return position;
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
                const filename = fileInput.target.files[index].name;
                const file = {
                    title: filename.substr(0, filename.lastIndexOf('.')),
                    reference: filename.substr(0, filename.lastIndexOf('.')).substr(0, 53),
                    mainDocument: true,
                    content: ''
                };
                const reader = new FileReader();
                reader.readAsArrayBuffer(fileInput.target.files[index]);
                reader.onload = (value: any) => {
                    file.mainDocument = this.filesToUpload.length === 0;
                    file.reference = this.filesToUpload.length === 0 ? file.reference : '';
                    file.content = this.getBase64Document(value.target.result);
                    this.filesToUpload.push(file);
                    if (this.filesToUpload.length === 1) {
                        setTimeout(() => {
                            this.menu.open('right-menu');
                        }, 500);
                    }
                };
            }
            this.fileImport.nativeElement.value = '';
        } else {
            this.loading = false;
        }
    }

    isExtensionAllowed(files: any[]) {
        for (let index = 0; index < files.length; index++) {
            if (files[index].name.toLowerCase().split('.').pop() !== 'pdf') {
                this.notificationService.error('lang.onlyPdfAuthorized');
                return false;
            }
        }
        return true;
    }

    getBase64Document(buffer: ArrayBuffer) {
        const TYPED_ARRAY = new Uint8Array(buffer);
        const STRING_CHAR = TYPED_ARRAY.reduce((data, byte) => data + String.fromCharCode(byte), '');

        return btoa(STRING_CHAR);
    }

    deleteFile(index: number) {
        this.filesToUpload.splice(index, 1);
    }

    async signPos(index: number) {
        if (this.appVisaWorkflow.getCurrentWorkflow().length > 0) {
            this.appVisaWorkflow.getCurrentWorkflow().forEach((user: any, indexUser: number) => {
                if (user.signaturePositions === undefined) {
                    this.appVisaWorkflow.visaWorkflow[indexUser].signaturePositions = [];
                }
            });
            const modal = await this.modalController.create({
                component: SignaturePositionComponent,
                cssClass: 'custom-alert-fullscreen',
                componentProps: {
                    'workflow': this.appVisaWorkflow.getCurrentWorkflow(),
                    'resource': this.filesToUpload[index],
                    'pdfContent': 'data:application/pdf;base64,' + this.filesToUpload[index].content,
                }
            });
            await modal.present();
            const { data } = await modal.onWillDismiss();
            if (data !== undefined) {
                this.filesToUpload[index].signPos = data;
                this.appVisaWorkflow.setPositionsWorkfow(this.filesToUpload[index], data);
            }
        } else {
            this.notificationService.error('lang.mustSetWorkflowBeforeSignPositions');
        }
    }

    isValid() {
        if (this.filesToUpload.filter((item: any) => item.title === '').length > 0) {
            this.notificationService.error('lang.subjectMandatory');
            return false;
        } else if (this.filesToUpload.filter((item: any) => item.mainDocument).length === 0) {
            this.notificationService.error('lang.mainDocumentMandatory');
            return false;
        } else if (this.appVisaWorkflow.getCurrentWorkflow().length === 0) {
            this.notificationService.error('lang.workflowUserstMandatory');
            this.menu.open('right-menu');
            return false;
        } else {
            return true;
        }
    }
}
