import {MatSnackBar} from '@angular/material';
import {Injectable,Component,Inject} from '@angular/core';
import {MAT_SNACK_BAR_DATA} from '@angular/material';

@Component({
    selector: 'custom-snackbar',
    template: '<mat-grid-list cols="4" rowHeight="1:1"><mat-grid-tile colspan="1"><mat-icon class="fa fa-{{data.icon}} fa-2x"></mat-icon></mat-grid-tile><mat-grid-tile colspan="3">{{data.message}}</mat-grid-tile></mat-grid-list>' // You may also use a HTML file
})
export class CustomSnackbarComponent {
    constructor(@Inject(MAT_SNACK_BAR_DATA) public data: any) { }
}

@Injectable()
export class NotificationService {
    constructor(public snackBar: MatSnackBar) {
    }
    success(message:string) {
        this.snackBar.openFromComponent(CustomSnackbarComponent,{
            duration: 2000,
            data: {message: message,icon : 'info-circle'}
          });
    }
         
    error(message:string) {
        this.snackBar.openFromComponent(CustomSnackbarComponent,{
            duration: 2000,
            data: {message: message,icon : 'exclamation-triangle'}
          });
    }
}