import { Component, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { HttpClient } from '@angular/common/http';
import { MatDialog } from '@angular/material';
import { map, tap, finalize } from 'rxjs/operators';
import { ActivatedRoute, Router } from '@angular/router';
import { ConfirmComponent } from '../../plugins/confirm.component';
import { TranslateService } from '@ngx-translate/core';


export interface User {
    id: string;
    firstname: string;
    lastname: string;
    login: string;
    email: string;
    picture: string;
}

@Component({
    selector: 'app-administration-user',
    templateUrl: 'user.component.html',
    styleUrls: ['user.component.scss'],
})

export class UserComponent implements OnInit {

    creationMode: boolean = true;
    loading: boolean = true;
    user: User;
    userClone: User;
    title: string = '';

    constructor(public http: HttpClient, private translate: TranslateService, private route: ActivatedRoute, private router: Router, public signaturesService: SignaturesContentService, public notificationService: NotificationService, public dialog: MatDialog) {
    }

    ngOnInit(): void {
        this.route.params.subscribe((params: any) => {
            if (params['id'] === undefined) {
                this.creationMode = true;
                this.title = this.translate.instant('lang.userCreation');
                this.user = {
                    id: '',
                    firstname: '',
                    lastname: '',
                    login: '',
                    email: '',
                    picture: ''
                };
                this.loading = false;
            } else {
                this.creationMode = false;
                this.http.get('../rest/users/' + params['id'])
                    .pipe(
                        map((data: any) => data.user),
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: data => {
                            this.user = data;
                            this.userClone = JSON.parse(JSON.stringify(this.user));
                            this.title = this.user.firstname + ' ' + this.user.lastname;
                        },
                        error: err => this.notificationService.handleErrors(err)
                    });
            }
        });
    }

    canValidate() {
        if (JSON.stringify(this.user) === JSON.stringify(this.userClone)) {
            return false;
        } else {
            return true;
        }
    }

    onSubmit() {
        if (this.creationMode) {
            this.createUser();
        } else {
            this.modifyUser();
        }
    }

    modifyUser() {
        this.loading = true;
        this.http.put('../rest/users/' + this.user.id, this.user)
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    this.router.navigate(['/administration/users']);
                    this.notificationService.success('lang.userUpdated');
                },
                error: err => this.notificationService.handleErrors(err)
            });
    }

    createUser() {
        this.loading = true;
        this.http.post('../rest/users', this.user)
            .pipe(
                finalize(() => this.loading = false)
            )
            .subscribe({
                next: () => {
                    this.router.navigate(['/administration/users']);
                    this.notificationService.success('lang.userAdded');
                },
                error: err => this.notificationService.handleErrors(err)
            });
    }

    delete() {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.loading = true;
                this.http.delete('../rest/users/' + this.user.id)
                    .pipe(
                        finalize(() => this.loading = false)
                    )
                    .subscribe({
                        next: () => {
                            this.router.navigate(['/administration/users']);
                            this.notificationService.success('lang.userDeleted');
                        },
                        error: err => this.notificationService.handleErrors(err)
                    });
            }
        });
    }

    cancel() {
        if (!this.creationMode) {
            this.user = JSON.parse(JSON.stringify(this.userClone));
        } else {
            this.router.navigate(['/administration/users']);
        }
    }
}
