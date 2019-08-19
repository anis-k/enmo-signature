import { Component, OnInit, ViewChild, Input, ElementRef } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import {
    MatMenuTrigger, MatDialog,
} from '@angular/material';
import { NotificationService } from '../service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { LocalStorageService } from '../service/local-storage.service';
import { ConfirmComponent } from '../plugins/confirm.component';


@Component({
    selector: 'app-document-sign-list',
    templateUrl: 'document-sign-list.component.html',
})
export class DocumentSignListComponent implements OnInit {

    @ViewChild('menuTrigger') menuSign: MatMenuTrigger;

    constructor(private translate: TranslateService,
        private sanitization: DomSanitizer,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        private localStorage: LocalStorageService,
        public dialog: MatDialog
        ) { }

    ngOnInit(): void { }

    onDragBegin(event: any) {
        this.signaturesService.documentFreeze = true;
    }
    moveSign(event: any, i: number) {
        const percentx = (event.x * 100) / this.signaturesService.workingAreaWidth;
        const percenty = (event.y * 100) / this.signaturesService.workingAreaHeight;

        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionX = percentx;
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionY = percenty;
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({'sign' : this.signaturesService.signaturesContent, 'note' : this.signaturesService.notesContent}));
        this.signaturesService.documentFreeze = false;
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
                this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({'sign' : this.signaturesService.signaturesContent, 'note' : this.signaturesService.notesContent}));
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
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({'sign' : this.signaturesService.signaturesContent, 'note' : this.signaturesService.notesContent}));
    }

    // USE TO PREVENT ISSUE IN MOBILE
    openMenu(menu: MatMenuTrigger) {
        menu.openMenu();
    }
}
