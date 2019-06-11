import { Component, OnInit, ElementRef, ViewChild, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { ScrollEvent } from 'ngx-scroll-event';
import { MatSidenav, MatInput } from '@angular/material';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { FormControl } from '@angular/forms';
import { debounceTime, switchMap, distinctUntilChanged, filter, tap } from 'rxjs/operators';


@Component({
  selector: 'app-sidebar',
  templateUrl: 'sidebar.component.html',
  styleUrls: ['sidebar.component.scss']
})
export class SidebarComponent implements OnInit {

    loadingList: boolean   = false;
    offset: number    = 0;
    limit: number    = 25;
    searchMode: boolean = false;

    @ViewChild('listContent') listContent: ElementRef;
    @ViewChild('searchInput') searchInput: ElementRef;
    // tslint:disable-next-line:no-input-rename
    @Input('snavRightComponent') snavRightComponent: MatSidenav;
    // tslint:disable-next-line:no-input-rename
    @Input('snavLeftComponent') snavLeftComponent: MatSidenav;

    searchTerm: FormControl = new FormControl();

    constructor(private translate: TranslateService, public http: HttpClient, public signaturesService: SignaturesContentService, private sidenav: MatSidenav, private route: ActivatedRoute, private router: Router, public notificationService: NotificationService) {
        this.searchTerm.valueChanges.pipe(
            debounceTime(500),
            distinctUntilChanged(),
            tap((value) => this.loadingList = true),
            switchMap(data => this.http.get('../rest/documents?limit=' + this.limit + '&search=' + data))
        ).subscribe((response: any) => {
            this.signaturesService.documentsList = response.documents;
            this.signaturesService.documentsListCount = response.count;
            this.loadingList = false;
        });
    }

    ngOnInit() {
        $('.avatar').css({'background': 'url(data:image/png;base64,' + this.signaturesService.userLogged.picture + ') no-repeat #135F7F'}).css({'background-size': 'cover'}).css({'background-position': 'center'});
        this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.signaturesService.mode)
            .subscribe((data: any) => {
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount = data.count;
            }, (err: any) => {
                this.notificationService.handleErrors(err);
            });
    }

    handleScroll(event: ScrollEvent) {
        if (event.isReachingBottom && !this.loadingList && this.signaturesService.documentsList.length < this.signaturesService.documentsListCount) {

            this.loadingList = true;
            this.listContent.nativeElement.style.overflowY = 'hidden';
            this.offset = this.offset + this.limit;

            this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.signaturesService.mode)
                .subscribe((data: any) => {
                    this.signaturesService.documentsList = this.signaturesService.documentsList.concat(data.documents);
                    this.loadingList = false;
                    this.listContent.nativeElement.style.overflowY = 'auto';
                    this.notificationService.success('lang.updatedListDocument');
                }, (err: any) => {
                    this.notificationService.handleErrors(err);
                });
            }
    }

    gotTo(documentId: number, i: any) {
        this.router.navigate(['/documents/' + documentId]);
        this.signaturesService.mainDocumentId = documentId;
        this.signaturesService.indexDocumentsList = i;
        this.signaturesService.sideNavRigtDatas = {
            mode : 'mainDocumentDetail',
            width : '450px',
            locked : false,
        };
        if (this.signaturesService.mobileMode) {
            this.sidenav.close();
        }
    }

    openProfile() {
        this.signaturesService.sideNavRigtDatas = {
            mode : 'profile',
            width : '650px',
            locked : true,
        };
        if (this.signaturesService.mobileMode) {
            this.snavLeftComponent.close();
            this.snavRightComponent.open();
        }
    }

    openAdmin() {
        this.router.navigate(['/administration/']);
        if (this.signaturesService.mobileMode) {
            this.snavLeftComponent.close();
        }
    }

    logout() {
        this.http.get('../rest/logout')
            .subscribe(() => {
                this.router.navigate(['/login']);
            }, (err: any) => {
                this.notificationService.handleErrors(err);
            });
    }

    search() {
        this.searchMode = true;
        this.signaturesService.mode = '';
        this.filter('');
        setTimeout(() => {
            this.searchInput.nativeElement.value = '';
            this.searchInput.nativeElement.focus();
            this.searchInput.nativeElement.click();
        }, 0);
    }

    filter(mode: string) {
        if (mode !== '') {
            this.searchMode = false;
        }

        this.loadingList = true;
        this.signaturesService.mode === mode ? this.signaturesService.mode = '' : this.signaturesService.mode = mode;

        this.offset = 0;
        this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.signaturesService.mode)
            .subscribe((data: any) => {
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount = data.count;
                this.loadingList = false;
            }, (err: any) => {
                this.notificationService.handleErrors(err);
                this.loadingList = false;
            });
    }

    checkClose() {
        if ((this.route.routeConfig.path === 'administration' || this.signaturesService.mainDocumentId > 0) && this.signaturesService.mobileMode) {
            return true;
        } else {
            return false;
        }
    }
}
