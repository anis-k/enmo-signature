import { Component, OnInit, ElementRef, ViewChild, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { ScrollEvent } from 'ngx-scroll-event';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';


@Component({
  selector: 'app-sidebar',
  templateUrl: 'sidebar.component.html',
  styleUrls: ['sidebar.component.scss']
})
export class SidebarComponent implements OnInit {

    loadingList : boolean   = false;
    mode        : string    = 'SIGN';
    offset      : number    = 0;
    limit       : number    = 25;

    @ViewChild('listContent') listContent: ElementRef;
    @Input('snavRightComponent') snavRightComponent: MatSidenav;
    @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

    constructor(public http: HttpClient, public signaturesService: SignaturesContentService, private sidenav: MatSidenav, private router: Router, public notificationService: NotificationService) {
    }

    ngOnInit() {
        $('.avatar').css({'background': 'url(data:image/png;base64,' + this.signaturesService.userLogged.picture + ') no-repeat #135F7F'}).css({'background-size': 'cover'}).css({'background-position': 'center'});
        this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.mode)
            .subscribe((data: any) => {
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount["SIGN"] = data["count"]["SIGN"];
                this.signaturesService.documentsListCount["NOTE"] = data["count"]["NOTE"];
            }, (err: any) => {
                this.notificationService.handleErrors(err);
            });
    }

    handleScroll(event: ScrollEvent) {
        if (event.isReachingBottom && !this.loadingList && this.signaturesService.documentsList.length < this.signaturesService.documentsListCount[this.mode]) {

            this.loadingList = true;
            this.listContent.nativeElement.style.overflowY = 'hidden';
            this.offset = this.offset + this.limit;

            this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.mode)
                .subscribe((data: any) => {
                    this.signaturesService.documentsList = this.signaturesService.documentsList.concat(data.documents);
                    this.loadingList = false;
                    this.listContent.nativeElement.style.overflowY = 'auto';
                    this.notificationService.success('Liste des documents actualisée');
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                });
            }
    }

    gotTo(documentId: Number, i: any) {
        this.router.navigate(['/documents/' + documentId]);
        this.signaturesService.indexDocumentsList = i;
        this.sidenav.close();
    }

    openProfile() {
        this.signaturesService.showProfile = true;
        this.snavLeftComponent.close();
        this.snavRightComponent.open();
    }

    logout() {
        this.http.get('../rest/logout')
            .subscribe(() => {
                this.router.navigate(['/login']);
            }, (err: any) => {
                this.notificationService.handleErrors(err);
            });
    }

    filter(mode: string) {
        this.mode = mode;
        this.offset = 0;
        this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.mode)
            .subscribe((data: any) => {
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount["SIGN"] = data["count"]["SIGN"];
                this.signaturesService.documentsListCount["NOTE"] = data["count"]["NOTE"];
            }, (err: any) => {
                this.notificationService.handleErrors(err);
            });
    }
}
