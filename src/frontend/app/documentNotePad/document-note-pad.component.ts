import { Component, OnInit, ViewChild, Input, ElementRef, EventEmitter, Output } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';

@Component({
    selector: 'app-document-note-pad',
    templateUrl: 'document-note-pad.component.html',
    styleUrls: ['document-note-pad.component.scss'],
})
export class DocumentNotePadComponent implements OnInit {

    penColors = [{ id: '#000000' }, { id: '#1a75ff' }, { id: '#FF0000' }];

    @Output() triggerEvent = new EventEmitter<string>();
    @ViewChild('canvas') canvas: ElementRef;

    constructor(private translate: TranslateService,
        private sanitizer: DomSanitizer,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public authService: AuthService,
        private localStorage: LocalStorageService) { }

    ngOnInit(): void { }

    initPad() {
        setTimeout(() => {
            ($('#myCanvas') as any).sign({
                mode: this.authService.user.preferences.writingMode, // direct or stylus
                lineWidth: this.authService.user.preferences.writingSize,
                changeColor: $('.radio'),
                undo: $('.undo'),
                height: this.signaturesService.workingAreaHeight,
                width: this.signaturesService.workingAreaWidth,
                fixHeight: this.signaturesService.y,
                fixWidth: this.signaturesService.x,
                mobileMode: this.signaturesService.mobileMode
            });
            $('input[value=\'' + this.authService.user.preferences.writingColor + '\']').trigger('click');
        }, 200);
    }

    cancelAnnotation() {
        this.signaturesService.mainLoading = true;
        setTimeout(() => {
            this.triggerEvent.emit();
            this.signaturesService.x = 0;
            this.signaturesService.y = 90;
            this.signaturesService.annotationMode = false;
            this.signaturesService.mainLoading = false;
        }, 200);
    }

    validateAnnotation() {
        this.signaturesService.mainLoading = true;
        if (!this.signaturesService.notesContent[this.signaturesService.currentPage]) {
            this.signaturesService.notesContent[this.signaturesService.currentPage] = [];
        }

        this.signaturesService.notesContent[this.signaturesService.currentPage].push(
            {
                'fullPath': <HTMLCanvasElement>this.canvas.nativeElement.toDataURL('image/png'),
                'positionX': 0,
                'positionY': 0,
                'height': this.signaturesService.workingAreaHeight,
                'width': this.signaturesService.workingAreaWidth,
            }
        );
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({'sign' : this.signaturesService.signaturesContent, 'note' : this.signaturesService.notesContent}));
        this.triggerEvent.emit();
        this.signaturesService.x = 0;
        this.signaturesService.y = 90;
        if (this.signaturesService.scale > 1) {
            this.signaturesService.renderingDoc = true;
        }
        this.signaturesService.annotationMode = false;
        this.notificationService.success('lang.annotationAdded');
    }

    undo() {
        /*const data = this.signaturePad.toData();
        if (data) {
            data.pop(); // remove the last dot or line
            this.signaturePad.fromData(data);
        }*/
    }

}
