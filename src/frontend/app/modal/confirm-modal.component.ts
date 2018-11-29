import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { HttpClient } from '@angular/common/http';

@Component({
    templateUrl: 'confirm-modal.component.html',
    styleUrls: ['confirm-modal.component.scss']
})
export class ConfirmModalComponent {
    disableState = false;

    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public http: HttpClient, public dialogRef: MatDialogRef<ConfirmModalComponent>, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    confirmDoc () {
        const signatures: any[] = [];
        if (this.signaturesService.currentAction > 0) {
            for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                if (this.signaturesService.signaturesContent[index]) {
                    this.signaturesService.signaturesContent[index].forEach((signature: any) => {
                        signatures.push(
                            {
                                'encodedImage'  : signature.encodedSignature,
                                'width'         : (this.signaturesService.signWidth * 100) / signature.pdfAreaX,
                                'positionX'     : (signature.positionX * 100) / signature.pdfAreaX,
                                'positionY'     : (signature.positionY * 100) / signature.pdfAreaY,
                                'type'          : 'PNG',
                                'page'          : index,
                            }
                        );
                    });
                }
                if (this.signaturesService.notesContent[index]) {
                    this.signaturesService.notesContent[index].forEach((note: any) => {
                        signatures.push(
                            {
                                'encodedImage'  : note.fullPath,
                                'width'         : note.width,
                                'positionX'     : note.positionX,
                                'positionY'     : note.positionY,
                                'type'          : 'SVG',
                                'page'          : index,
                            }
                        );
                    });
                }
            }
            this.disableState = true;
            this.http.put('../rest/documents/' + this.signaturesService.mainDocumentId + '/actions/' + this.signaturesService.currentAction, {'signatures': signatures})
                .subscribe(() => {
                    this.disableState = false;
                    this.dialogRef.close('sucess');
                    this.signaturesService.documentsList.splice(this.signaturesService.indexDocumentsList, 1);
                    if (this.signaturesService.documentsListCount > 0) {
                        this.signaturesService.documentsListCount--;
                    }
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                    this.disableState = false;
                });
        } else {
            this.dialogRef.close('sucess');
        }
    }
}
