import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { FormControl } from '@angular/forms';
import { debounceTime, switchMap, distinctUntilChanged, tap, finalize } from 'rxjs/operators';
import { AuthService } from '../service/auth.service';
import { MenuController, ModalController } from '@ionic/angular';
import { ProfileComponent } from '../profile/profile.component';
import { FunctionsService } from '../service/functions.service';


@Component({
    selector: 'app-sidebar',
    templateUrl: 'sidebar.component.html',
    styleUrls: ['sidebar.component.scss']
})
export class SidebarComponent implements OnInit {

    loadingList: boolean = false;
    offset: number = 0;
    limit: number = 10;
    searchMode: boolean = false;

    @ViewChild('listContent') listContent: ElementRef;
    @ViewChild('searchInput') searchInput: ElementRef;

    searchTerm: FormControl = new FormControl();

    constructor(
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        private route: ActivatedRoute,
        public router: Router,
        private menu: MenuController,
        public notificationService: NotificationService,
        public authService: AuthService,
        public modalController: ModalController,
        public functionsService: FunctionsService,
    ) {
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
        $('.avatar').css({ 'background': 'url(data:image/png;base64,' + this.authService.user.picture + ') no-repeat #135F7F' }).css({ 'background-size': 'cover' }).css({ 'background-position': 'center' });
        this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.signaturesService.mode)
            .subscribe((data: any) => {
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount = data.count;
            });
    }

    ngAfterViewInit(): void {
        this.filter('');
    }

    async openProfile() {
        const modal = await this.modalController.create({
            component: ProfileComponent,
            cssClass: 'my-custom-class'
        });
        await modal.present();
    }

    openAdmin() {
        this.menu.close('left-menu');
        this.router.navigate(['/administration/']);
    }

    openHome() {
        this.router.navigate(['/home']);
    }

    openIndexation() {
        this.menu.close('left-menu');
        this.router.navigate(['/indexation']);
    }

    openSearch() {
        this.menu.close('left-menu');
        this.router.navigate(['/search']);
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
        this.signaturesService.documentsList = [];
        if (mode !== '') {
            this.searchMode = false;
        }

        this.loadingList = true;
        this.signaturesService.mode === mode ? this.signaturesService.mode = '' : this.signaturesService.mode = mode;
        this.offset = 0;
        this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.signaturesService.mode)
            .pipe(
                finalize(() => {
                    this.loadingList = false;
                })
            )
            .subscribe((data: any) => {
                this.signaturesService.documentsList = data.documents;
                this.signaturesService.documentsListCount = data.count;
                this.loadingList = false;
            });
    }

    checkClose() {
        if ((this.route.routeConfig.path.indexOf('administration') !== -1 || this.signaturesService.mainDocumentId > 0) && this.signaturesService.mobileMode) {
            return true;
        } else {
            return false;
        }
    }

    loadData(event: any) {
        this.offset = this.offset + this.limit;

        this.http.get('../rest/documents?limit=' + this.limit + '&offset=' + this.offset + '&mode=' + this.signaturesService.mode).pipe(
            tap((data: any) => {
                this.signaturesService.documentsList = this.signaturesService.documentsList.concat(data.documents);
                event.target.complete();
                if (this.signaturesService.documentsList.length === this.signaturesService.documentsListCount.current) {
                    event.target.disabled = true;
                }
            })
        ).subscribe();
    }

    isAdminRoute() {
        return this.router.url.split('/').indexOf('administration') > -1;
    }

    canIndex() {
        return this.authService.user.appPrivileges.map((item: any) => item.id).indexOf('indexation') > -1;
    }
}
