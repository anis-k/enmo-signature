import { Component, OnInit, ViewChild, Input, ElementRef } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { DomSanitizer } from '@angular/platform-browser';
import { SignaturePad } from 'angular2-signaturepad/signature-pad';


@Component({
    selector: 'app-document-note-pad',
    templateUrl: 'document-note-pad.component.html',
    styleUrls: ['document-note-pad.component.scss'],
})
export class DocumentNotePadComponent implements OnInit {

    lockSignaturePad = false;
    penColors = [{ id: 'black' }, { id: '#1a75ff' }, { id: '#FF0000' }];
    annotationPadOptions = {
        throttle: 0,
        minWidth: 1,
        maxWidth: 1,
        minDistance: 0,
        backgroundColor: 'rgba(255, 255, 255, 0)',
        canvasWidth: 768,
        canvasHeight: 270
    };

    @ViewChild(SignaturePad) signaturePad: SignaturePad;
    @Input() pageNum: number;


    constructor(private sanitization: DomSanitizer, public signaturesService: SignaturesContentService, public notificationService: NotificationService) { }

    ngOnInit(): void { }

    onDotChange(entry: any) {
        this.signaturePad.set('minWidth', parseFloat(entry));
        this.signaturePad.set('maxWidth', parseFloat(entry) + 2);
    }

    onColorChange(entry: any) {
        this.signaturePad.set('penColor', entry.id);
    }

    resizePad() {
        this.annotationPadOptions.canvasWidth = this.signaturesService.workingAreaWidth;
        this.annotationPadOptions.canvasHeight = this.signaturesService.workingAreaHeight;

        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        this.annotationPadOptions.canvasHeight = this.annotationPadOptions.canvasHeight * ratio;
        this.annotationPadOptions.canvasWidth = this.annotationPadOptions.canvasWidth * ratio;

        if (this.signaturePad !== undefined) {
            this.signaturePad.set('canvasWidth', this.annotationPadOptions.canvasWidth);
            this.signaturePad.set('canvasHeight', this.annotationPadOptions.canvasHeight);
        }
    }

    cancelAnnotation() {
        setTimeout(() => {
            this.signaturesService.annotationMode = false;
            this.signaturePad.clear();
            this.signaturesService.scale = 1;
        }, 200);
    }

    togglePad() {
        if (this.lockSignaturePad) {
            this.lockSignaturePad = false;
            this.signaturePad.on();
        } else {
            this.lockSignaturePad = true;
            this.signaturePad.off();
        }
    }

    zoomPlus() {
        this.signaturesService.renderingDoc = true;
        this.lockSignaturePad = true;
        this.signaturesService.scale = 2;
    }

    zoomMinus() {
        this.signaturesService.renderingDoc = true;
        this.signaturePad.clear();
        this.lockSignaturePad = true;
        this.signaturesService.scale = 1;
    }

    validateAnnotation() {
        if (!this.signaturesService.notesContent[this.signaturesService.currentPage]) {
            this.signaturesService.notesContent[this.signaturesService.currentPage] = [];
        }
        this.signaturesService.notesContent[this.signaturesService.currentPage].push(
            {
                'fullPath': this.signaturePad.toDataURL('image/svg+xml'),
                'positionX': 0,
                'positionY': 0,
                'height': this.annotationPadOptions.canvasHeight,
                'width': 768,
            }
        );
        this.signaturePad.clear();
        if (this.signaturesService.scale > 1) {
            this.signaturesService.renderingDoc = true;
        }
        this.signaturesService.scale = 1;
        this.signaturesService.annotationMode = false;
        this.notificationService.success('Annotation ajoutée');
    }

    undo() {
        const data = this.signaturePad.toData();
        if (data) {
            data.pop(); // remove the last dot or line
            this.signaturePad.fromData(data);
        }
    }

}
