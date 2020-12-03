import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { MatDialog } from '@angular/material/dialog';
import { MatMenuTrigger } from '@angular/material/menu';
import { NotificationService } from '../service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { LocalStorageService } from '../service/local-storage.service';
import { ConfirmComponent } from '../plugins/confirm.component';
import { PopoverController } from '@ionic/angular';

@Component({
    selector: 'app-document-sign-list',
    templateUrl: 'document-sign-list.component.html',
    styleUrls: ['document-sign-list.component.scss'],
})
export class DocumentSignListComponent implements OnInit {

    @Input() bounds: any;
    @ViewChild('menuTrigger') menuSign: MatMenuTrigger;
    @ViewChild('test') test: any;

    fix = 'auto';

    constructor(private translate: TranslateService,
        private sanitization: DomSanitizer,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        private localStorage: LocalStorageService,
        public dialog: MatDialog,
        public popoverController: PopoverController
    ) { }

    ngOnInit(): void { }

    select(ev: any, index: number) {
        this[ev.detail.value](index);
    }

    onDragBegin(event: any) {
        this.signaturesService.documentFreeze = true;
    }

    moveSign(event: any, i: number) {
        const percentx = (event.x * 100) / this.signaturesService.workingAreaWidth;
        const percenty = (event.y * 100) / this.signaturesService.workingAreaHeight;

        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionX = percentx;
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionY = percenty;
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'date': this.signaturesService.datesContent, 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
        this.signaturesService.dragging = false;
    }

    moveDate(event: any, i: number) {
        const percentx = (event.x * 100) / this.signaturesService.workingAreaWidth;
        const percenty = (event.y * 100) / this.signaturesService.workingAreaHeight;

        this.signaturesService.datesContent[this.signaturesService.currentPage][i].positionX = percentx;
        this.signaturesService.datesContent[this.signaturesService.currentPage][i].positionY = percenty;
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'date': this.signaturesService.datesContent, 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
        this.signaturesService.dragging = false;
    }

    onResizing(event: any, index: number) {
        this.test.nativeElement.style.height = 'auto';
    }

    onResizeDateStop(event: any, index: number) {
        this.signaturesService.datesContent[this.signaturesService.currentPage][index].height = (event.size.height * 100) / this.signaturesService.workingAreaHeight;
        this.signaturesService.datesContent[this.signaturesService.currentPage][index].width = (event.size.width * 100) / this.signaturesService.workingAreaWidth;
    }

    onResizeStop(event: any, index: number) {
        this.test.nativeElement.style.height = 'auto';
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][index].width = (event.size.width * 100) / this.signaturesService.workingAreaWidth;
    }

    cloneSign(i: number) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, width: '450px', data: { title: 'lang.wantSignOtherPage', msg: '' } });

        dialogRef.afterClosed().subscribe(result => {
            if (result === 'yes') {
                this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].inAllPage = true;
                this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].token = Math.random().toString(36).substr(2, 9);

                for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                    if (!this.signaturesService.signaturesContent[index]) {
                        this.signaturesService.signaturesContent[index] = [];
                    }
                    if (index !== this.signaturesService.currentPage) {
                        this.signaturesService.signaturesContent[index].push(JSON.parse(JSON.stringify(this.signaturesService.signaturesContent[this.signaturesService.currentPage][i])));
                    }
                }
                this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
            }
        });

        this.menuSign.closeMenu();
    }

    deleteSignature(i: number) {

        if (this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].inAllPage === true) {
            const token = this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].token;
            const r = confirm(this.translate.instant('lang.wantDeleteSignatureOtherPage'));

            if (r) {

                for (let index = 1; index <= this.signaturesService.totalPage; index++) {
                    if (!this.signaturesService.signaturesContent[index]) {
                        this.signaturesService.signaturesContent[index] = [];
                    }
                    for (let index2 = 0; index2 <= this.signaturesService.signaturesContent[index].length; index2++) {
                        if (this.signaturesService.signaturesContent[index][index2]) {
                            if (token === this.signaturesService.signaturesContent[index][index2].token) {
                                this.signaturesService.signaturesContent[index].splice(index2, 1);
                            }
                        }
                    }
                }
            } else {
                this.signaturesService.signaturesContent[this.signaturesService.currentPage].splice(i, 1);
            }
        } else {
            this.signaturesService.signaturesContent[this.signaturesService.currentPage].splice(i, 1);
        }
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
    }
}
