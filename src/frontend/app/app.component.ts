import { Component, ViewEncapsulation } from '@angular/core';
import { MatIconRegistry } from '@angular/material';
import { DomSanitizer } from '@angular/platform-browser';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  encapsulation: ViewEncapsulation.None,
  styleUrls: ['app.component.styl']
})

export class AppComponent {

  constructor() { }
}
