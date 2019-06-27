import { Component, OnInit, Input } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../../service/signatures.service';
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

    // tslint:disable-next-line:no-input-rename
    @Input('snavRightComponent') snavRightComponent: MatSidenav;
    // tslint:disable-next-line:no-input-rename
    @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

    loading: boolean = true;
    privileges: Privilege[] = [];

    constructor(public signaturesService: SignaturesContentService, private route: ActivatedRoute, private router: Router, public authService: AuthService) {
    }

    ngOnInit() {
        $('.avatar').css({'background': 'url(data:image/png;base64,' + this.authService.user.picture + ') no-repeat #135F7F'}).css({'background-size': 'cover'}).css({'background-position': 'center'});
    }

    openHome() {
        this.router.navigate(['/documents/']);
        if (this.signaturesService.mobileMode) {
            this.snavLeftComponent.close();
        }
    }

    checkClose() {
        if ((this.route.routeConfig.path.indexOf('administration') !== -1 || this.signaturesService.mainDocumentId > 0) && this.signaturesService.mobileMode) {
            return true;
        } else {
            return false;
        }
    }
}
