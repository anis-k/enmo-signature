import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { HammerGestureConfig, HAMMER_GESTURE_CONFIG } from '@angular/platform-browser';
import { PdfViewerModule } from 'ng2-pdf-viewer';
import { ScrollEventModule } from 'ngx-scroll-event';
import { AngularDraggableModule } from 'angular2-draggable';
import { CookieService } from 'ngx-cookie-service';


import { AppMaterialModule } from './app-material.module';

// COMPONENTS
import { AppComponent } from './app.component';
import { LoginComponent } from './login/login.component';
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
    LoginComponent,
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
    FormsModule,
    ReactiveFormsModule,
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
      { path: 'document', component: DocumentComponent},
      { path: 'login', component: LoginComponent},
      { path: '**',   redirectTo: 'document', pathMatch: 'full' },
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
    },
    CookieService],
    exports: [
        RouterModule
    ],
  bootstrap: [AppComponent]
})
export class AppModule { }
