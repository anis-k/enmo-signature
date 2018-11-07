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
import { AppComponent } from './app.component';
import { SignaturesComponent } from './signatures/signatures.component';
import { SignaturePadPageComponent } from './pad/pad.component';
import { SignaturePadModule } from 'angular2-signaturepad';
import { DrawerComponent } from './drawer/drawer.component';
import { DocumentComponent,
  WarnModalComponent,
  ConfirmModalComponent,
  SuccessInfoValidBottomSheetComponent,
  RejectInfoBottomSheetComponent } from './document/document.component';
import { SidebarComponent } from './sidebar/sidebar.component';

// SERVICES
import { SignaturesContentService } from './service/signatures.service';



@NgModule({
  declarations: [
    AppComponent,
    SignaturesComponent,
    SignaturePadPageComponent,
    DrawerComponent,
    DocumentComponent,
    SidebarComponent,
    WarnModalComponent,
    ConfirmModalComponent,
    SuccessInfoValidBottomSheetComponent,
    RejectInfoBottomSheetComponent,
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
    RouterModule.forRoot([
        { path: 'document/:id', component: DocumentComponent},
    ], { useHash: true }),
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
    exports: [
        RouterModule
    ],
  bootstrap: [AppComponent]
})
export class AppModule { }
