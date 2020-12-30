import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { MatDialog } from '@angular/material/dialog';
import { MatMenuTrigger } from '@angular/material/menu';
import { NotificationService } from '../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { LocalStorageService } from '../service/local-storage.service';
import { ModalController, PopoverController } from '@ionic/angular';
import { DateOptionModalComponent } from './dateOption/date-option-modal.component';

@Component({
    selector: 'app-document-date-list',
    templateUrl: 'document-date-list.component.html',
    styleUrls: ['document-date-list.component.scss'],
})
export class DocumentDateListComponent implements OnInit {

    @Input() bounds: any;
    @ViewChild('menuTrigger') menuSign: MatMenuTrigger;
    @ViewChild('test2') test2: any;

    fix = 'auto';
    today: Date = new Date();

    constructor(private translate: TranslateService,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        private localStorage: LocalStorageService,
        public dialog: MatDialog,
        public popoverController: PopoverController,
        public modalController: ModalController,
    ) { }

    ngOnInit(): void { }

    moveDate(event: any, i: number) {
        const percentx = (event.x * 100) / this.signaturesService.workingAreaWidth;
        const percenty = (event.y * 100) / this.signaturesService.workingAreaHeight;

        this.signaturesService.datesContent[this.signaturesService.currentPage][i].positionX = percentx;
        this.signaturesService.datesContent[this.signaturesService.currentPage][i].positionY = percenty;
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'date': this.signaturesService.datesContent, 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
        this.signaturesService.dragging = false;
    }

    onResizingDate(event: any, index: number) {
        this.test2.nativeElement.style.height = 'auto';
    }

    onResizeDateStop(event: any, index: number) {
        this.test2.nativeElement.style.height = 'auto';
        this.signaturesService.datesContent[this.signaturesService.currentPage][index].height = (event.size.height * 100) / this.signaturesService.workingAreaHeight;
        this.signaturesService.datesContent[this.signaturesService.currentPage][index].width = (event.size.width * 100) / this.signaturesService.workingAreaWidth;
    }

    deleteDate(i: number) {
        this.signaturesService.datesContent[this.signaturesService.currentPage].splice(i, 1);
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'date': this.signaturesService.datesContent, 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
    }

    async openDateSettings(index: number) {
        const modal = await this.modalController.create({
            component: DateOptionModalComponent,
            componentProps: {
                'currentDate': this.signaturesService.datesContent[this.signaturesService.currentPage][index],
            }
        });
        await modal.present();

        const { data } = await modal.onWillDismiss();
        if (data !== undefined) {
            this.signaturesService.datesContent[this.signaturesService.currentPage][index] = data;
        }
    }
}
