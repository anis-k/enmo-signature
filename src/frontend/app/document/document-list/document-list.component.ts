import { Component, Input, OnInit, Output, EventEmitter, ViewChild, AfterViewInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { IonSlides, MenuController } from '@ionic/angular';

@Component({
    selector: 'app-document-list',
    templateUrl: 'document-list.component.html',
    styleUrls: ['document-list.component.scss'],
})
export class DocumentListComponent implements OnInit, AfterViewInit {

    @Input() docList: any;
    @Input() currentDocId: any;

    @Output() triggerEvent = new EventEmitter<string>();

    @ViewChild('slides', { static: false }) slides: IonSlides;

    loading: boolean = true;

    scrolling: boolean = false;
    slideOpts = {
        initialSlide: 0,
        speed: 400,
        direction: 'vertical'
    };

    constructor(
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        private sanitizer: DomSanitizer,
        private menu: MenuController
    ) { }

    ngOnInit(): void {
        this.docList.forEach((element: any, index: number) => {
            if (element.imgContent[1] === undefined && index > 0) {
                this.http.get('../rest/attachments/' + element.id + '/thumbnails/1')
                    .subscribe((data: any) => {
                        element.imgContent[1] = 'data:image/png;base64,' + data.fileContent;
                    });
            }
        });
    }

    ngAfterViewInit(): void {
        this.loading = false;
    }

    loadDoc(id: string) {
        this.triggerEvent.emit(id);
        this.menu.close('right-menu');
    }

    scroll(ev: any) {
        if (!this.scrolling) {
            this.scrolling = true;
            if (ev.deltaY < 0) {
                this.slides.slidePrev();
            } else {
                this.slides.slideNext();
            }
            setTimeout(() => {
                this.scrolling = false;
            }, 500);
        }
    }
}
