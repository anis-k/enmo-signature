import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { ScrollEvent } from 'ngx-scroll-event';

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

  @ViewChild('listContent') listContent: ElementRef;

  constructor(public http: HttpClient) { }

  handleScroll(event: ScrollEvent) {
    if (event.isReachingBottom) {
      // this.listContent.nativeElement.scrollTo(0, 0);
      this.loadingList = true;
      this.listContent.nativeElement.style.overflowY = 'hidden';
      console.log(`the user is reaching the bottom`);
      const currentCountList = this.documentsList.length;
      for (let index = currentCountList + 1; index <= currentCountList + 20; index++) {
        this.documentsList.push(
          {
            'id' : index,
            'reference' : 'CAB/2018A/' + index,
            'subject' : 'AJOUT Document ' + index,
            'status' : 'A traiter',
          }
        );
      }
      setTimeout(() => {
        this.loadingList = false;
        this.listContent.nativeElement.style.overflowY = 'auto';
      }, 3000);
    }
    /*if (event.isReachingTop) {
      console.log(`the user is reaching the bottom`);
    }
    if (event.isWindowEvent) {
      console.log(`This event is fired on Window not on an element.`);
    }*/
  }

  ngOnInit() {
    this.http.get('../rest/documents')
      .subscribe((data: any) => {
        this.documentsList = data.documents;
        // this.countDocumentsList = data.fullCount;
        // TO DO REMOVE AFTER INIT IN BACK
        this.countDocumentsList = this.documentsList.length;
      });
  }

}
