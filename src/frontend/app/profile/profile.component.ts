import { Component, AfterViewInit, OnInit, Input } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { MatIconRegistry, MatSidenav } from '@angular/material';
import { HttpClient } from '@angular/common/http';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';

@Component({
    selector: 'app-my-profile',
    templateUrl: 'profile.component.html',
    styleUrls: ['profile.component.styl'],
})

export class ProfileComponent implements OnInit, AfterViewInit {

    @Input('snavRightComponent') snavRightComponent: MatSidenav;
    @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

    profileInfo: any = {};
    constructor(public http: HttpClient, iconReg: MatIconRegistry, sanitizer: DomSanitizer, public notificationService: NotificationService, public signaturesService: SignaturesContentService) {
        iconReg.addSvgIcon('maarchLogo', sanitizer.bypassSecurityTrustResourceUrl('../src/assets/logo_white.svg'));
    }

    ngOnInit(): void {
        this.profileInfo.email = this.signaturesService.userLogged.email;
        this.profileInfo.firstname = this.signaturesService.userLogged.firstname;
        this.profileInfo.lastname = this.signaturesService.userLogged.lastname;
    }

    ngAfterViewInit(): void {

    }

    closeProfile() {
        this.snavLeftComponent.open();
        this.snavRightComponent.close();
    }
}
