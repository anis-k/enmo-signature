import { Component, OnInit, ViewChild, ElementRef, EventEmitter, Output, Input } from '@angular/core';
import { SignaturesContentService } from '../service/signatures.service';
import { NotificationService } from '../service/notification.service';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';
import { ModalController } from '@ionic/angular';
import { DragScrollComponent } from 'ngx-drag-scroll';

@Component({
    selector: 'app-document-note-pad',
    templateUrl: 'document-note-pad.component.html',
    styleUrls: ['document-note-pad.component.scss'],
})
export class DocumentNotePadComponent implements OnInit {

    @Input() content: string;
    @Input() precentScrollTop: any;
    @Input() precentScrollLeft: any;

    penColors = [{ id: '#000000' }, { id: '#1a75ff' }, { id: '#FF0000' }];

    areaWidth = 0;
    areaHeight = 0;
    editMode: boolean = true;
    originalSize: boolean = true;
    loading = true;

    @Output() triggerEvent = new EventEmitter<string>();
    @ViewChild('mainContent') mainContent: any;
    @ViewChild('canvas') canvas: ElementRef;
    @ViewChild('img') img: any;
    @ViewChild('nav', { read: DragScrollComponent }) ds: DragScrollComponent;

    constructor(
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public authService: AuthService,
        private localStorage: LocalStorageService,
        public modalController: ModalController
    ) { }

    ngOnInit(): void {
        setTimeout(() => {
            this.loading = false;
        }, 100);
    }

    imageLoaded(ev: any) {
        this.getImageDimensions(false);
    }

    getImageDimensions(originalsize: boolean = false): void {
        this.originalSize = originalsize;
        const img = new Image();
        img.onload = (data: any) => {
            this.areaWidth = data.target.naturalWidth;
            this.areaHeight = data.target.naturalHeight;
            if (!originalsize) {
                this.getAreaDimension();
            }
            if (this.editMode) {
                setTimeout(() => {
                    const offset = $('#myBounds').offset();
                    let y: number;
                    let x: number;
                    let clientX: any;
                    let clientY: any;

                    if (Math.sign(offset.top) === 1 || this.precentScrollTop <= Math.abs(offset.top)) {
                        y = this.precentScrollTop - offset.top;
                    } else if (Math.sign(offset.top) === -1 && this.precentScrollTop <= -Math.sign(offset.top)) {
                        y = (this.precentScrollTop - offset.top) * 2;
                    } else {
                        y = (this.precentScrollTop - offset.top) * 100;
                    }
                    x = this.precentScrollLeft - offset.left;
                    clientX = this.precentScrollLeft - document.documentElement.offsetLeft;
                    clientY = this.precentScrollTop - document.documentElement.offsetTop;
                    clientX = clientX / this.areaWidth * 100;
                    clientY = clientY / this.areaHeight * 100;
                    document.getElementsByClassName('drag-scroll-content')[1].scrollTo(x, y);
                    img.style.transform = 'translate(-' + clientX + '%,-' + clientY + '%) scale(2)';
                    this.initPad();
                }, 200);
            }
        };
        img.src = this.content;
    }

    getAreaDimension() {
        const percent = (this.mainContent.el.offsetWidth * 100) / this.areaWidth;

        this.areaWidth = (percent * this.areaWidth) / 100;
        this.areaHeight = (percent * this.areaHeight) / 100;
    }

    dismissModal() {
        this.modalController.dismiss('cancel');
    }

    initPad() {
        ($('#myCanvas') as any).sign({
            mode: this.authService.user.preferences.writingMode, // direct or stylus
            lineWidth: this.authService.user.preferences.writingSize,
            changeColor: $('.radio'),
            undo: $('.undo'),
            height: this.areaHeight,
            width: this.areaWidth,
            fixHeight: 56,
            fixWidth: 0,
            mobileMode: this.signaturesService.mobileMode
        });
        $('input[value=\'' + this.authService.user.preferences.writingColor + '\']').trigger('click');
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
        this.localStorage.save(this.signaturesService.mainDocumentId.toString(), JSON.stringify({ 'date': this.signaturesService.datesContent, 'sign': this.signaturesService.signaturesContent, 'note': this.signaturesService.notesContent }));
        this.modalController.dismiss('');
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
