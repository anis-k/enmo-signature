import { Component, ViewChild, Input, Host, Output, EventEmitter } from '@angular/core';
import { SignaturePad } from 'angular2-signaturepad/signature-pad';
import { Store, select } from '@ngrx/store';
import { Observable } from 'rxjs';
import { HIDE_DRAWER, SHOW_DRAWER  } from '../store/drawer';
import { HIDE_PAD, SHOW_PAD  } from '../store/pad';
import { HIDE_SIGNATURES, SHOW_SIGNATURES} from '../store/signatures';
import { MatSnackBar } from '@angular/material';

interface AppState {
  pad: boolean;
}
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
  selectedColor;
  selectedWidthPenSize;
  haveSigned;

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

  constructor(public snackBar: MatSnackBar, private store: Store<AppState>) {
    this.pad$ = store.pipe(select('pad'));
  }

  ngAfterViewInit() {
    console.log('v0.6.0');
    // this.signaturePad.clear();
    // this.signaturePad.resizeCanvas();
    const signPointsData = localStorage.getItem('signature');
    if (signPointsData) {
      // this.signaturePad.fromData( signPointsData );
    }
  }

  onColorChange(entry) {
    this.selectedColor = Object.assign({}, this.selectedColor, entry);
    this.signaturePad.set('penColor', this.selectedColor.id );
  }

  onDotChange(entry) {
    this.selectedWidthPenSize = parseFloat(entry);
    this.signaturePad.set('minWidth', this.selectedWidthPenSize );
    this.signaturePad.set('maxWidth', this.selectedWidthPenSize + 2 );
  }

  drawComplete() {
    // console.log(this.signaturePad.toDataURL('image/svg+xml'));
    localStorage.setItem('signature', JSON.stringify(this.signaturePad.toData()));
    this.haveSigned = true;
  }

  drawStart() {
    console.log('begin drawing', this.selectedWidthPenSize);
  }

  drawClear() {
    this.signaturePad.clear();
    this.haveSigned = false;
  }

  closePad() {
    this.store.dispatch({ type: HIDE_PAD });
    this.store.dispatch({ type: SHOW_SIGNATURES });
  }

  enregistrerSignature() {
    this.haveSigned = true;
    localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL('image/npg')));

    // Save signature in BDD
    /*this.http.post("rest/signatures")
      .subscribe((data: any) => {
        localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL('image/npg')));
        this.closePad();
        this.reloaded.emit('reload');
        this.signaturePad.clear();
      });*/

    // BUG IMAGE CROPPED
    // localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL('image/svg+xml')));

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
  }

}
