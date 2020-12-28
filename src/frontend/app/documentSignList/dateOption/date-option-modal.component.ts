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

    constructor(
        private translate: TranslateService,
        public modalController: ModalController
    ) { }

    ngOnInit(): void {
        console.log(this.date);
        
    }

    dismissModal() {
        this.modalController.dismiss('cancel');
    }

    getFontLabel(label: string) {
        return label.replace(' ', '_');
    }

    test() {
        
    }

}
