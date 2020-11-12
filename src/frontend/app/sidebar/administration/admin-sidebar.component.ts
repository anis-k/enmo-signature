import { Component, Input, OnInit } from '@angular/core';
import { MatSidenav } from '@angular/material/sidenav';
import { Router, ActivatedRoute } from '@angular/router';
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

    @Input() snavRightComponent: MatSidenav;
    @Input() snavLeftComponent: MatSidenav;

    constructor(
        private route: ActivatedRoute,
        private router: Router,
        public authService: AuthService
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
