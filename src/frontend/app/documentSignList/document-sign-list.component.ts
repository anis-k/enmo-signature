import { Component, OnInit, ViewChild, Input, ElementRef } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import {
    MatMenuTrigger,
} from '@angular/material';
import { NotificationService } from '../service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';


@Component({
    selector: 'app-document-sign-list',
    templateUrl: 'document-sign-list.component.html',
})
export class DocumentSignListComponent implements OnInit {

    @Input('canvas') canvas: ElementRef;
    @ViewChild('menuTrigger') menuSign: MatMenuTrigger;

    constructor(private sanitization: DomSanitizer, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    ngOnInit(): void { }

    moveSign(event: any, i: number) {
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionX = event.x;
        this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].positionY = event.y;
        localStorage.setItem(this.signaturesService.mainDocumentId.toString(), JSON.stringify({"sign" : this.signaturesService.signaturesContent, "note" : this.signaturesService.notesContent}));
    }

    cloneSign(i: number) {
        let r = confirm('Voulez-vous apposer la signature sur les autres pages ?');

        if (r) {
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
            localStorage.setItem(this.signaturesService.mainDocumentId.toString(), JSON.stringify({"sign" : this.signaturesService.signaturesContent, "note" : this.signaturesService.notesContent}));
        }
        this.menuSign.closeMenu();
    }

    deleteSignature(i: number) {

        if (this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].inAllPage === true) {
            const token = this.signaturesService.signaturesContent[this.signaturesService.currentPage][i].token;
            const r = confirm('Voulez-vous supprimer la signature sur les autres pages ?');

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
        localStorage.setItem(this.signaturesService.mainDocumentId.toString(), JSON.stringify({"sign" : this.signaturesService.signaturesContent, "note" : this.signaturesService.notesContent}));
    }

    // USE TO PREVENT ISSUE IN MOBILE
    openMenu(menu: MatMenuTrigger) {
        menu.openMenu();
    }
}
