import { Component, OnInit, ViewChild, Input, ElementRef, EventEmitter, Output } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';


@Component({
    selector: 'app-document-note-pad',
    templateUrl: 'document-note-pad.component.html',
    styleUrls: ['document-note-pad.component.scss'],
})
export class DocumentNotePadComponent implements OnInit {

    penColors = [{ id: '#000000' }, { id: '#1a75ff' }, { id: '#FF0000' }];

    @Output() triggerEvent = new EventEmitter<string>();
    @ViewChild('canvas') canvas: ElementRef;

    constructor(private sanitizer: DomSanitizer, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    ngOnInit(): void { }

    initPad() {
        setTimeout(() => {
            $('#myCanvas').sign({
                mode: 'stylus',
                lineWidth: 1,
                changeColor: $('.radio'),
                undo: $('.undo'),
                height: this.signaturesService.workingAreaHeight * 2,
                width: this.signaturesService.workingAreaWidth * 2,
                fixHeight: this.signaturesService.y,
                fixWidth: this.signaturesService.x
            });
            $('input[value=\'' + this.penColors[0].id + '\']').prop('checked', true);
        }, 200);
    }

    cancelAnnotation() {
        setTimeout(() => {
            this.triggerEvent.emit();
            this.signaturesService.x = 0;
            this.signaturesService.y = 85;
            this.signaturesService.annotationMode = false;
        }, 200);
    }

    validateAnnotation() {
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
        localStorage.setItem(this.signaturesService.mainDocumentId.toString(), JSON.stringify({'sign' : this.signaturesService.signaturesContent, 'note' : this.signaturesService.notesContent}));
        this.triggerEvent.emit();
        this.signaturesService.x = 0;
        this.signaturesService.y = 85;
        if (this.signaturesService.scale > 1) {
            this.signaturesService.renderingDoc = true;
        }
        this.signaturesService.annotationMode = false;
        this.notificationService.success('Annotation ajoutée');
    }

    undo() {
        /*const data = this.signaturePad.toData();
        if (data) {
            data.pop(); // remove the last dot or line
            this.signaturePad.fromData(data);
        }*/
    }

}
