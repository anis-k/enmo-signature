import { Component, OnInit } from '@angular/core';
import { DevLangComponent } from '../debug/dev-lang.component';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../notification.service';
import { of } from 'rxjs';
import { FunctionsService } from '../functions.service';
import { catchError, filter, tap } from 'rxjs/operators';
import { IonSlides, ModalController } from '@ionic/angular';

@Component({
    selector: 'app-dev-tool',
    templateUrl: 'dev-tool.component.html',
    styleUrls: ['dev-tool.component.scss'],
})
export class DevToolComponent implements OnInit {

    allLang: any;
    countMissingLang: number = 0;

    constructor(
        private notify: NotificationService,
        public http: HttpClient,
        private functionsService: FunctionsService,
        public modalController: ModalController,
    ) { }

    ngOnInit(): void {
        this.getLangs();
    }

    async openLangTool() {
        const modal = await this.modalController.create({
            component: DevLangComponent,
            cssClass: 'my-custom-class'
        });
        await modal.present();
    }

    getLangs() {
        this.http.get('../rest/languages').pipe(
            tap((data: any) => {
                this.allLang = data.languages;
                Object.keys(this.allLang).forEach(langName => {
                    this.countMissingLang += Object.keys(this.allLang.fr.lang).filter((keyLang: any) => Object.keys(this.allLang[langName].lang).indexOf(keyLang) === -1).length;
                });
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

}
