import {Component, Inject} from '@angular/core';
import {MatDialog, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { LANG } from './translate.component';


@Component({
    selector: 'dialog-result-example-dialog',
    template: `<p mat-dialog-title>Confirmation</p>
                <div class="alert alert-warning" role="alert" *ngIf="data.warn" [innerHTML]="data.warn">
                </div>
               <p mat-dialog-content class="text-center">{{data.msg}}</p>
               <mat-dialog-actions>
                  <button mat-button color="default" (click)="dialogRef.close('cancel')">{{lang.cancel}}</button>
                  <span style="flex: 1 1 auto;"></span>
                  <button mat-button color="primary" (click)="dialogRef.close('ok')">{{lang.validate}}</button>
              </mat-dialog-actions>
            `,
})
export class ConfirmModalComponent {
    lang: any = LANG;
    constructor(@Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<ConfirmModalComponent>) { }
}