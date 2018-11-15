import { Component, OnInit, ElementRef, ViewChild, Input } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { ScrollEvent } from 'ngx-scroll-event';
import { MatSidenav, MatSnackBar } from '@angular/material';
import * as $ from 'jquery';
import { SignaturesContentService } from '../service/signatures.service';
import { OutputType } from '@angular/core/src/view';

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
  loadingList = false;
  offset = 0;
  limit = 25;

  @ViewChild('listContent') listContent: ElementRef;
  @Input('snavRightComponent') snavRightComponent: MatSidenav;
  @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

  constructor(public http: HttpClient, public signaturesService: SignaturesContentService, private sidenav: MatSidenav, private router: Router, public snackBar: MatSnackBar) { }

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
          this.snackBar.open('Liste des documents actualisée', null,
            {
              duration: 3000,
              panelClass: 'center-snackbar',
              verticalPosition: 'top'
            }
          );
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
        if (err.status === 401) {
          this.router.navigate(['/login']);
          this.snackBar.open(err.error.errors, null,
            {
              duration: 3000,
              panelClass: 'center-snackbar',
              verticalPosition: 'top'
            }
          );
        }
      });
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
}
