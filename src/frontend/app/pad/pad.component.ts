import { Component, ViewChild, Input, Host, Output, EventEmitter } from '@angular/core';
import { SignaturePad } from 'angular2-signaturepad/signature-pad';
import { Observable } from 'rxjs';
import { SignaturesContentService } from '../service/signatures.service';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../service/notification.service';
import { CookieService } from 'ngx-cookie-service';
import { TranslateService } from '@ngx-translate/core';
import { finalize } from 'rxjs/operators';

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
    canvasWidth: 600,
    canvasHeight: 315
  };

  constructor(private translate: TranslateService, public http: HttpClient, public signaturesService: SignaturesContentService, public notificationService: NotificationService, private cookieService: CookieService) { }

  ngAfterViewInit() {
    // this.signaturePad.clear();
    // this.signaturePad.resizeCanvas();
    const signPointsData = localStorage.getItem('signature');
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
    // console.log(this.signaturePad.toDataURL('image/svg+xml'));
    localStorage.setItem('signature', JSON.stringify(this.signaturePad.toData()));
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

  enregistrerSignature() {
    this.disableState = true;
    this.haveSigned = true;
    const newEncodedSign = this.signaturePad.toDataURL('image/png').replace('data:image/png;base64,', '');
    localStorage.setItem('signature', JSON.stringify(newEncodedSign));

    // Save signature in BDD
    const newSign = {
      'id': 0,
      'encodedSignature': newEncodedSign,
      'format': 'png'
    };
    const cookieInfo = JSON.parse(atob(this.cookieService.get('maarchParapheurAuth')));

    this.http.post('../rest/users/' + cookieInfo.id + '/signatures', newSign)
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
        // this.store.dispatch({ type: HIDE_DRAWER });
        this.signaturePad.clear();
        this.notificationService.success('lang.signatureRegistered');
      });

    // BUG IMAGE CROPPED
    // localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL('image/svg+xml')));
  }

}
