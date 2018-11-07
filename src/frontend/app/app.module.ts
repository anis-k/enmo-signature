import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientModule } from '@angular/common/http';
import { RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { HammerGestureConfig, HAMMER_GESTURE_CONFIG } from '@angular/platform-browser';
import { PdfViewerModule } from 'ng2-pdf-viewer';
import { ScrollEventModule } from 'ngx-scroll-event';
import { AngularDraggableModule } from 'angular2-draggable';


import { AppMaterialModule } from './app-material.module';

// COMPONENTS
import {
  AppComponent,
  WarnModalComponent,
  ConfirmModalComponent,
  SuccessInfoValidBottomSheetComponent,
  RejectInfoBottomSheetComponent } from './app.component';
import { SignaturesComponent } from './signatures/signatures.component';
import { SignaturePadPageComponent } from './pad/pad.component';
import { SignaturePadModule } from 'angular2-signaturepad';
import { DrawerComponent } from './drawer/drawer.component';
import { DocumentComponent } from './document/document.component';
import { AnnotationComponent } from './annotation/annotation.component';
import { SidebarComponent } from './sidebar/sidebar.component';

// REDUX / NGRX
import { StoreModule } from '@ngrx/store';
import { StoreDevtoolsModule } from '@ngrx/store-devtools';
import { drawerReducer } from './store/drawer';
import { signaturesReducer } from './store/signatures';
import { signatureDragReducer } from './store/dragSignature';
import { padReducer } from './store/pad';
import { annotationReducer } from './store/annotation';
import { sidebarReducer } from './store/sidebar';
import { CanvasDragComponent } from './canvas-drag/canvas-drag.component';

// SERVICES
import { SignaturesContentService } from './service/signatures.service';



@NgModule({
  declarations: [
    AppComponent,
    SignaturesComponent,
    SignaturePadPageComponent,
    DrawerComponent,
    DocumentComponent,
    AnnotationComponent,
    SidebarComponent,
    WarnModalComponent,
    ConfirmModalComponent,
    SuccessInfoValidBottomSheetComponent,
    RejectInfoBottomSheetComponent,
    CanvasDragComponent
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    HttpClientModule,
    RouterModule,
    SignaturePadModule,
    PdfViewerModule,
    ScrollEventModule,
    AngularDraggableModule,
    AppMaterialModule,
    StoreModule.forRoot({ drawer : drawerReducer,
                          signatures : signaturesReducer,
                          pad : padReducer,
                          dragSignature : signatureDragReducer,
                          annotation : annotationReducer,
                          sidebar : sidebarReducer }),
    // StoreModule.forRoot(reducers)
    StoreDevtoolsModule.instrument({
      maxAge: 25 // Retains last 25 states
    }),
  ],
  entryComponents: [
    WarnModalComponent,
    ConfirmModalComponent,
    SuccessInfoValidBottomSheetComponent,
    RejectInfoBottomSheetComponent,
    SignaturesComponent
  ],
  providers: [SignaturesContentService,
    {
    provide: HAMMER_GESTURE_CONFIG,
    useClass: HammerGestureConfig
    }],
  bootstrap: [AppComponent]
})
export class AppModule { }
