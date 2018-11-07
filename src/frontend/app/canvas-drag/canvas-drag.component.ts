import { Component, OnInit, Input, ViewChild, ElementRef } from '@angular/core';
import * as interact from 'interactjs';

@Component({
  selector: 'app-canvas-drag',
  templateUrl: 'canvas-drag.component.html',
  styleUrls: ['canvas-drag.component.styl']
})
export class CanvasDragComponent implements OnInit {
  @Input() x: number;
  @Input() y: number;

  @ViewChild('annotation') elDraggerContent: ElementRef;

  constructor() { }

  ngOnInit() {
    this.initDraggable();
    this.initDraggablePosition();
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

}
