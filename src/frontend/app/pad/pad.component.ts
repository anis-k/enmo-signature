import { Component, ViewChild, Input, Host, Output, EventEmitter } from '@angular/core';
import { SignaturePad } from 'angular2-signaturepad/signature-pad';
import { Observable } from 'rxjs';
import { MatSnackBar } from '@angular/material';
import { SignaturesContentService } from '../service/signatures.service';
import { HttpClient } from '@angular/common/http';

interface AfterViewInit {
  ngAfterViewInit(): void;
}
@Component({
  selector: 'app-pad',
  templateUrl: 'pad.component.html',
  styleUrls: ['pad.component.styl']
})
export class SignaturePadPageComponent implements AfterViewInit {
  penColors = [{ id: 'black' }, { id: 'orange' }, { id: '#FF0000'}, { id: '#FF000000'}];
  selectedColor: any;
  selectedWidthPenSize: any;
  haveSigned: any;

  pad$: Observable<boolean>;

  @ViewChild(SignaturePad)
  signaturePad: SignaturePad;

  @Output()
  reloaded = new EventEmitter<string>();

  private signaturePadOptions: Object = {
    // passed through to szimek/signature_pad constructor
    // minWidth: 0.5,
    // maxWidth: 2.5,
    // dotSize: 0,
    backgroundColor: 'rgba(255, 255, 255, 0)',
    canvasWidth: 600,
    canvasHeight: 315
  };

  constructor(public http: HttpClient, public signaturesService: SignaturesContentService, public snackBar: MatSnackBar) { }

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
    this.selectedWidthPenSize = parseFloat(entry);
    this.signaturePad.set('minWidth', this.selectedWidthPenSize );
    this.signaturePad.set('maxWidth', this.selectedWidthPenSize + 2 );
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
    this.haveSigned = true;
    const newEncodedSign = this.signaturePad.toDataURL('image/npg').replace('data:image/png;base64,', '');
    localStorage.setItem('signature', JSON.stringify(newEncodedSign));

    // Save signature in BDD
    const newSign = {
      'encodedSignature': newEncodedSign,
      'format': 'png'
    };
    this.http.post('../rest/users/' + '1' + '/signatures', newSign)
      .subscribe(() => {
        this.closePad();
        this.reloaded.emit('reload');
        // this.store.dispatch({ type: HIDE_DRAWER });
        this.signaturePad.clear();
        this.snackBar.open('Signature enregistré', null,
            {
              duration: 3000,
              panelClass : 'center-snackbar',
              verticalPosition: 'top'
            }
          );
      }, (err: any) => {
          console.log(err);
      });

    // BUG IMAGE CROPPED
    // localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL('image/svg+xml')));
  }

}
