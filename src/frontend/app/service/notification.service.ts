import { MatSnackBar } from '@angular/material';
import { Injectable, Component, Inject } from '@angular/core';
import { MAT_SNACK_BAR_DATA } from '@angular/material';
import { Router } from '@angular/router';
import { empty } from 'rxjs';

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
        if (err.status === 401 && this.router.url !== '/login') {
            this.router.navigate(['/login']);
            this.error('Veuillez vous reconnecter');
        } else {
            if (err.error.errors !== undefined) {
                this.error(err.error.errors);
            } else {
                this.error(err.error.exception[0].message);
                console.log(err.error.exception[0]);
            }
        }
    }
}
