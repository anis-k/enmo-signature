import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CanvasDragComponent } from './canvas-drag.component';

describe('CanvasDragComponent', () => {
  let component: CanvasDragComponent;
  let fixture: ComponentFixture<CanvasDragComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CanvasDragComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CanvasDragComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
