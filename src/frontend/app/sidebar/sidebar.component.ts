import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { ScrollEvent } from 'ngx-scroll-event';
import { MatSidenav } from '@angular/material';
import * as $ from 'jquery';

interface AppState {
  sidebar: boolean;
}
@Component({
  selector: 'app-sidebar',
  templateUrl: 'sidebar.component.html',
  styleUrls: ['sidebar.component.styl']
})
export class SidebarComponent implements OnInit {
  sidebar$: Observable<boolean>;
  documentsList: any[] = [];
  countDocumentsList = 0;
  loadingList = false;
  offset = 0;
  limit = 25;

  @ViewChild('listContent') listContent: ElementRef;

  constructor(public http: HttpClient, private sidenav: MatSidenav, private router: Router) { }

  handleScroll(event: ScrollEvent) {
    if (event.isReachingBottom && !this.loadingList && this.documentsList.length < this.countDocumentsList) {

      this.loadingList = true;
      this.listContent.nativeElement.style.overflowY = 'hidden';
      console.log(`the user is reaching the bottom`);
      this.offset = this.offset + this.limit;

      this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset)
      .subscribe((data: any) => {
        this.documentsList = this.documentsList.concat(data.documents);
        this.loadingList = false;
        this.listContent.nativeElement.style.overflowY = 'auto';
      });
    }
  }

  ngOnInit() {
    this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset)
      .subscribe((data: any) => {
        this.documentsList = data.documents;
        this.countDocumentsList = data.fullCount;
      });
  }

  gotTo(documentId: Number) {
    this.router.navigate(['/document/' + documentId]);
    this.sidenav.close();
  }

}
