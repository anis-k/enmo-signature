import { MatSnackBar } from '@angular/material';
import { Injectable, Component, Inject } from '@angular/core';
import { MAT_SNACK_BAR_DATA } from '@angular/material';
import { Router } from '@angular/router';

@Component({
    selector: 'app-custom-snackbar',
    template: '{{data.message}}' // You may also use a HTML file
})
export class CustomSnackbarComponent {
    constructor(@Inject(MAT_SNACK_BAR_DATA) public data: any) { }
}

@Injectable()
export class NotificationService {
    constructor(private router: Router, public snackBar: MatSnackBar) {
    }
    success(message: string) {
        this.snackBar.openFromComponent(CustomSnackbarComponent, {
            duration: 3000,
            panelClass: 'success-snackbar',
            verticalPosition: 'top',
            data: { message: message }
        });
    }

    error(message: string) {
        this.snackBar.openFromComponent(CustomSnackbarComponent, {
            duration: 3000,
            panelClass: 'error-snackbar',
            verticalPosition: 'top',
            data: { message: message }
        });
    }

    handleErrors(err: any) {
        if (err.status === 401) {
            this.router.navigate(['/login']);
            this.error('Veuillez vous reconnecter');
        } else {
            this.error(err.error.errors);
        }
    }
}
