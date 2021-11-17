import { Component, Inject, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../notification.service';
import { TranslateService } from '@ngx-translate/core';
import { ModalController } from '@ionic/angular';


@Component({
    templateUrl: 'dev-lang.component.html',
    styleUrls: ['dev-lang.component.scss'],
})
export class DevLangComponent implements OnInit {
    allLang: any;

    missingLang: any =  {};

    currentLang = 'en';

    data: any = {};


    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        private translate: TranslateService,
        public modalController: ModalController
    ) {
    }

    ngOnInit(): void {
        this.getLangs();
    }

    dismissModal() {
        this.modalController.dismiss('cancel');
    }

    getLangs() {
        this.http.get('../rest/languages').pipe(
            tap((data: any) => {
                this.allLang = data.languages;
                Object.keys(this.allLang).forEach(langName => {
                    this.missingLang[langName] = Object.keys(this.allLang.fr.lang).filter((keyLang: any) => Object.keys(this.allLang[langName].lang).indexOf(keyLang) === -1).map((keyLang: any) => ({
                        id: keyLang,
                        value: this.allLang.fr.lang[keyLang] + '__TO_TRANSLATE'
                    }));
                });
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    openTranslation(text: string) {
        window.open('https://translate.google.fr/?hl=fr#view=home&op=translate&sl=fr&tl=' + this.currentLang + '&text=' + text.replace('__TO_TRANSLATE', ''), '_blank');
    }

    setActiveLang(ev: any) {
        this.currentLang = ev.detail.value;
    }

    generateMissingLang(ignoreToTranslate: boolean) {
        const newLang = {};
        const mergedLang = this.allLang[this.currentLang];
        const regex = /__TO_TRANSLATE$/g;
        this.missingLang[this.currentLang].forEach((element: any) => {
            if (element.value.match(regex) === null && ignoreToTranslate) {
                newLang[element.id] = element.value;
            } else if (!ignoreToTranslate) {
                newLang[element.id] = element.value;
            }
        });
        mergedLang.lang = {...mergedLang.lang, ...newLang};
        this.http.put('../rest/languages', { langId: this.currentLang, jsonContent: mergedLang }).pipe(
            tap((data: any) => {
                Object.keys(newLang).forEach(keyLang => {
                    delete this.allLang[this.currentLang][keyLang];

                    this.missingLang[this.currentLang] = this.missingLang[this.currentLang].filter((missLang: any) => missLang.id !== keyLang);
                    this.data.countMissingLang--;
                });
                this.modalController.dismiss('cancel');
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
