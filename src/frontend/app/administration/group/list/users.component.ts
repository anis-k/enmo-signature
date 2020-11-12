import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { AlertController, ModalController } from '@ionic/angular';
import { map } from 'rxjs/operators';

@Component({
    selector: 'app-users',
    templateUrl: 'users.component.html',
})

export class UsersComponent implements OnInit {

    @Input() users: any;

    usersList: any[] = [];

    constructor(
        public http: HttpClient,
        public modalController: ModalController
    ) { }

    ngOnInit(): void {
        this.http.get('../rest/users?mode=all')
            .pipe(
                map((data: any) => data.users)
            )
            .subscribe({
                next: data => {
                    this.usersList = data.filter((user: any) => this.users.findIndex((userG: any) => userG.id === user.id) === -1);
                }
            });
    }

    selectUser(user: any) {
        this.modalController.dismiss(user);
    }
}
