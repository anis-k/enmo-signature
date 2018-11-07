import { Component, ViewChild, ElementRef, TemplateRef, Input, ViewContainerRef, Renderer2 } from '@angular/core';
import { SignaturePad } from 'angular2-signaturepad/signature-pad';
import * as interact from 'interactjs';
import { Store, select } from '@ngrx/store';
import { Observable } from 'rxjs';
// import { HIDE_DRAWER, SHOW_DRAWER  } from '../store/drawer';
import { HIDE_ANNOTATION, SHOW_ANNOTATION} from '../store/annotation';

interface AppState {
  annotation: boolean;
}
interface AfterViewInit {
  ngAfterViewInit(): void;
}

@Component({
  selector: 'app-annotation',
  templateUrl: 'annotation.component.html',
  styleUrls: ['annotation.component.styl']
})
export class AnnotationComponent implements AfterViewInit {
  @Input() x: number;
  @Input() y: number;
  mouseX;
  mouseY;
  penColors = [{ id: 'black' }, { id: 'orange' }, { id: '#FF0000'}];
  selectedColor;
  selectedWidthPenSize;
  haveAnnoted;
  dragger;
  elArticle;
  // elAnnotation;
  canvasWrapper;
  event: MouseEvent;
  annotation$: Observable<boolean>;

  @ViewChild(SignaturePad) signaturePad: SignaturePad;
  @ViewChild('annotation') elDraggerContent: ElementRef;
  @ViewChild('canvasWrapper') eLCanvasWrapper: ElementRef;
  // @ViewChild('pdfpage') pdfpageEl: TemplateRef<any>;
  // @ViewChild('pdfpage', {read: ViewContainerRef}) pdfpageEl: ViewContainerRef;

  private annotationPadOptions: Object = {
    backgroundColor: 'rgba(255, 255, 255, 0.5)',
    canvasWidth: 380,
    canvasHeight: 270
  };

  constructor(private store: Store<AppState>, private renderer: Renderer2) {
    this.annotation$ = store.pipe(select('annotation'));
    this.canvasWrapper = document.querySelector('.canvas-wrapper');
    this.elArticle = document.querySelector('.article');
    // this.elAnnotation = document.querySelector('.annotation');
  }

  ngAfterViewInit() {
    console.log('elArticle', this.elArticle);
    this.initDraggableContent();
    this.initDraggable();
    this.initDraggablePosition();
  }

  initDraggableContent () {
    this.renderer.appendChild(this.canvasWrapper, this.elDraggerContent.nativeElement );
  }

  initDraggablePosition() {
    console.log('initDraggablePosition', this.x, this.y);
    this.elDraggerContent.nativeElement.style.left = this.x + 'px';
    this.elDraggerContent.nativeElement.style.top = this.y + 'px';
    this.elDraggerContent.nativeElement.style.transform = 'unset';
  }

  initDraggable() {
    interact('.annotation')
    .draggable({
      inertia: true,
      restrict: {
        restriction: 'parent',
        endOnly: true,
        elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
    },
    autoScroll: {
      container: '.article',
      margin: 50,
      distance: 5,
      interval: 10
    },
      onmove: this.dragMoveListener,
      allowFrom: '.annotation-drag',
      ignoreFrom: 'canvas',
      onend: function (event) {}
    });
  }

  private dragMoveListener(event) {
    const target = event.target;
    const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
    const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
    target.style.webkitTransform = target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
    target.setAttribute('data-x', x);
    target.setAttribute('data-y', y);
  }

  closeAnnotation(e) {
      const elAnnotation = document.querySelector('.annotation');
      this.store.dispatch({ type: HIDE_ANNOTATION });
      // console.log('A', this.elAnnotation);
      // console.log('B', document.querySelector('.annotation'));
      elAnnotation.remove();
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
    console.log(this.signaturePad.toDataURL('image/svg+xml'));
    this.haveAnnoted = true;
  }

  drawStart() {
    console.log('begin drawing', this.selectedWidthPenSize);
  }

  drawClear() {
    this.signaturePad.clear();
    this.haveAnnoted = false;
  }

  validateAnnotation() {
    this.haveAnnoted = true;
    // localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL()));
    // localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL('image/png')));
    localStorage.setItem('signature', JSON.stringify(this.signaturePad.toDataURL('image/svg+xml')));
    this.store.dispatch({ type: SHOW_ANNOTATION });
    this.store.dispatch({ type: HIDE_ANNOTATION });
  }

}
