import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { MenuController } from '@ionic/angular';
import { AuthService } from '../../service/auth.service';

export interface Privilege {
    id: string;
    icon: string;
    route: string;
}

@Component({
    selector: 'app-admin-sidebar',
    templateUrl: 'admin-sidebar.component.html',
    styleUrls: ['admin-sidebar.component.scss']
})
export class AdminSidebarComponent implements OnInit {

    loading: boolean = true;
    privileges: Privilege[] = [];

    constructor(
        private route: ActivatedRoute,
        private router: Router,
        public authService: AuthService,
        public menu: MenuController
    ) {
    }

    ngOnInit() { }

    openHome() {
        this.router.navigate(['/documents/']);
    }

    isActiveRoute(route: string) {
        return this.router.url.split('/').indexOf(route.replace('/administration/', '')) > -1;
    }
}
