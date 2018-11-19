import { Component, OnInit, ElementRef, ViewChild, Input, AfterViewInit } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { ScrollEvent } from 'ngx-scroll-event';
import { MatSidenav } from '@angular/material';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';

interface AppState {
  sidebar: boolean;
}
@Component({
  selector: 'app-sidebar',
  templateUrl: 'sidebar.component.html',
  styleUrls: ['sidebar.component.styl']
})
export class SidebarComponent implements OnInit, AfterViewInit {
  sidebar$: Observable<boolean>;
  loadingList = false;
  offset = 0;
  limit = 25;

  @ViewChild('listContent') listContent: ElementRef;
  @Input('snavRightComponent') snavRightComponent: MatSidenav;
  @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

  constructor(public http: HttpClient, public signaturesService: SignaturesContentService, private sidenav: MatSidenav, private router: Router, public notificationService: NotificationService) { }

  handleScroll(event: ScrollEvent) {
    if (event.isReachingBottom && !this.loadingList && this.signaturesService.documentsList.length < this.signaturesService.documentsListCount) {

      this.loadingList = true;
      this.listContent.nativeElement.style.overflowY = 'hidden';
      console.log(`the user is reaching the bottom`);
      this.offset = this.offset + this.limit;

      this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset)
        .subscribe(
          (data: any) => {
            this.signaturesService.documentsList = this.signaturesService.documentsList.concat(data.documents);
            this.loadingList = false;
            this.listContent.nativeElement.style.overflowY = 'auto';
            this.notificationService.success('Liste des documents actualisée');
          },
          () => {
            console.log('error !');
          });
    }
  }

  ngOnInit() {
    this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset)
      .subscribe((data: any) => {
        this.signaturesService.documentsList = data.documents;
        this.signaturesService.documentsListCount = data.fullCount;
      },
        (err: any) => {
          this.notificationService.handleErrors(err);
        });
  }

  ngAfterViewInit(): void {
    if (this.signaturesService.mainDocumentId > 0 && this.signaturesService.documentsList.length > 0) {
      this.router.navigate(['/document/' + this.signaturesService.documentsList[0].id]);
    }
  }



  gotTo(documentId: Number, i: any) {
    this.router.navigate(['/document/' + documentId]);
    this.signaturesService.indexDocumentsList = i;
    this.sidenav.close();
  }

  openProfile() {
    this.snavLeftComponent.close();
    this.snavRightComponent.open();
  }

  logout() {
    this.http.get('../rest/logout')
      .subscribe((data: any) => {
        this.router.navigate(['/login']);
      }, (err: any) => {
          this.notificationService.handleErrors(err);
      });
  }
}
