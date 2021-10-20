import { Component, OnInit, ElementRef, ViewChild, AfterViewInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { FormControl } from '@angular/forms';
import { debounceTime, switchMap, distinctUntilChanged, tap, finalize } from 'rxjs/operators';
import { AuthService } from '../service/auth.service';
import { IonSearchbar, MenuController, ModalController } from '@ionic/angular';
import { ProfileComponent } from '../profile/profile.component';
import { FunctionsService } from '../service/functions.service';
import { FiltersService } from '../service/filters.service';


@Component({
    selector: 'app-sidebar',
    templateUrl: 'sidebar.component.html',
    styleUrls: ['sidebar.component.scss']
})
export class SidebarComponent implements OnInit, AfterViewInit {

    @ViewChild('listContent') listContent: ElementRef;
    @ViewChild('searchInput') searchInput: IonSearchbar;

    loadingList: boolean = false;
    searchMode: boolean = false;

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
        public filterService: FiltersService
    ) {
        this.searchTerm.valueChanges.pipe(
            debounceTime(500),
            distinctUntilChanged(),
            tap((value) => this.loadingList = true),
            switchMap(data => this.http.get('../rest/documents?limit=' + this.filterService.limit + '&search=' + data))
        ).subscribe((response: any) => {
            this.signaturesService.documentsList = response.documents;
            this.signaturesService.documentsListCount = response.count;
            this.loadingList = false;
        });
    }

    ngOnInit() {
        $('.avatar').css({ 'background': 'url(data:image/png;base64,' + this.authService.user.picture + ') no-repeat #280d43' }).css({ 'background-size': 'cover' }).css({ 'background-position': 'center' });
        this.http.get('../rest/documents?limit=' + this.filterService.limit + '&offset=' + this.filterService.offset + '&mode=' + this.signaturesService.mode)
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

    search(event: any) {
        this.searchMode = true;
        this.signaturesService.mode = '';
        this.filter('');
        setTimeout(() => {
            if (event.type !== 'ionCancel') {
                this.searchInput.value = '';
                this.searchInput.setFocus();
            }
        }, 0);
    }

    filter(mode: string) {
        this.signaturesService.documentsList = [];
        if (mode !== '') {
            this.searchMode = false;
        }

        this.loadingList = true;
        this.signaturesService.mode === mode ? this.signaturesService.mode = '' : this.signaturesService.mode = mode;
        this.filterService.offset = 0;
        this.http.get('../rest/documents?limit=' + this.filterService.limit + '&offset=' + this.filterService.offset + '&mode=' + this.signaturesService.mode)
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
        this.filterService.offset = this.filterService.offset + this.filterService.limit;

        this.http.get('../rest/documents?limit=' + this.filterService.limit + '&offset=' + this.filterService.offset + '&mode=' + this.signaturesService.mode).pipe(
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
