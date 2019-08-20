import { Component, ViewChild, Output, EventEmitter } from '@angular/core';
import { SignaturePad } from 'angular2-signaturepad/signature-pad';
import { Observable } from 'rxjs';
import { SignaturesContentService } from '../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../service/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { finalize } from 'rxjs/operators';
import { AuthService } from '../service/auth.service';
import { LocalStorageService } from '../service/local-storage.service';

interface AfterViewInit {
    ngAfterViewInit(): void;
}
@Component({
    selector: 'app-pad',
    templateUrl: 'pad.component.html',
    styleUrls: ['pad.component.scss']
})
export class SignaturePadPageComponent implements AfterViewInit {

    penColors = [{ id: 'black' }, { id: '#1a75ff' }, { id: '#FF0000'}];
    selectedColor: any;
    haveSigned: any;
    disableState = false;

    pad$: Observable<boolean>;

    @ViewChild(SignaturePad)
    signaturePad: SignaturePad;

    @Output()
    reloaded = new EventEmitter<string>();

    private signaturePadOptions: Object = {
        // passed through to szimek/signature_pad constructor
        minWidth: 1,
        maxWidth: 2.5,
        // dotSize: 0,
        backgroundColor: 'rgba(255, 255, 255, 0)',
        canvasWidth: 1200,
        canvasHeight: 630
    };

    constructor(private translate: TranslateService,
        public http: HttpClient,
        public signaturesService: SignaturesContentService,
        public notificationService: NotificationService,
        public authService: AuthService,
        private localStorage: LocalStorageService) { }

    ngAfterViewInit() {
        const signPointsData = this.localStorage.get('signature');
        if (signPointsData) {
            // this.signaturePad.fromData( signPointsData );
        }
    }

    onColorChange(entry: any) {
        this.selectedColor = Object.assign({}, this.selectedColor, entry);
        this.signaturePad.set('penColor', this.selectedColor.id );
    }

    onDotChange(entry: any) {
        this.signaturePad.set('minWidth', parseFloat(entry) );
        this.signaturePad.set('maxWidth', parseFloat(entry) + 2 );
    }

    drawComplete() {
        this.localStorage.save('signature', JSON.stringify(this.signaturePad.toData()));
        this.haveSigned = true;
    }

    drawClear() {
        this.signaturePad.clear();
        this.haveSigned = false;
    }

    closePad() {
        this.signaturesService.showPad = false;
        this.signaturesService.showSign = true;
    }

    saveSignature() {
        this.disableState = true;
        this.haveSigned = true;
        const newEncodedSign = this.signaturePad.toDataURL('image/png').replace('data:image/png;base64,', '');
        this.localStorage.save('signature', JSON.stringify(newEncodedSign));

        // Save signature in BDD
        const newSign = {
            'id': 0,
            'encodedSignature': newEncodedSign,
            'format': 'png'
        };

        this.http.post('../rest/users/' + this.authService.user.id + '/signatures', newSign)
            .pipe(
                finalize(() => {
                    this.disableState = false;
                })
            )
            .subscribe((data: any) => {
                newSign.id = data.signatureId;
                this.signaturesService.newSign = newSign;
                this.closePad();
                this.reloaded.emit('reload');
                this.signaturePad.clear();
                this.notificationService.success('lang.signatureRegistered');
            });

        // BUG IMAGE CROPPED
        // this.localStorage.save('signature', JSON.stringify(this.signaturePad.toDataURL('image/svg+xml')));
    }

}
