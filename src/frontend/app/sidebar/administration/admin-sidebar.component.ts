import { Component, OnInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../../service/signatures.service';
import { NotificationService } from '../../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { tap, map, finalize } from 'rxjs/operators';
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

    constructor(public http: HttpClient, public signaturesService: SignaturesContentService, private route: ActivatedRoute, private router: Router, public notificationService: NotificationService, public authService: AuthService) {
    }

    ngOnInit() {
        $('.avatar').css({'background': 'url(data:image/png;base64,' + this.signaturesService.userLogged.picture + ') no-repeat #135F7F'}).css({'background-size': 'cover'}).css({'background-position': 'center'});
        this.http.get('../rest/administrativePrivileges')
        .pipe(
            map((data: any) => data.privileges),
            finalize(() => this.loading = false)
        )
        .subscribe({
            next: data => this.privileges = data,
        });
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
