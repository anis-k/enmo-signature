import { Component, Input, OnInit } from '@angular/core';
import { ModalController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';

@Component({
    templateUrl: 'date-option-modal.component.html',
    styleUrls: ['date-option-modal.component.scss'],
})
export class DateOptionModalComponent implements OnInit {

    @Input() date: any;

    today: Date = new Date();

    dateformats: any[] = [
        'dd/MM/y',
        'dd-MM-y',
        'dd.MM.y',
        'd MMM y',
        'd MMMM y',
    ];

    datefonts: any[] = [
        'Arial',
        'Verdana',
        'Helvetica',
        'Tahoma',
        'Times New Roman',
        'Courier New',
    ];

    size = {
        'Arial': 15,
        'Verdana': 13,
        'Helvetica': 13,
        'Tahoma': 13,
        'Times New Roman': 15,
        'Courier New': 13
    };

    constructor(
        private translate: TranslateService,
        public modalController: ModalController
    ) { }

    ngOnInit(): void {
        console.log(this.date);
    }

    dismissModal() {
        this.modalController.dismiss();
    }

    getFontLabel(label: string) {
        return label.replace(' ', '_');
    }

    onSubmit() {
        this.modalController.dismiss(this.date);
    }

    select(font: string) {
        this.date.size = this.size[font];
    }
}
