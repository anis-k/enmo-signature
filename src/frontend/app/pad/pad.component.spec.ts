import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SignaturePadPageComponent } from './pad.component';

describe('PadComponent', () => {
  let component: SignaturePadPageComponent;
  let fixture: ComponentFixture<SignaturePadPageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SignaturePadPageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SignaturePadPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
