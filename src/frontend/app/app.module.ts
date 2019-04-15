import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule, HttpClient } from '@angular/common/http';
import { RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { HammerGestureConfig, HAMMER_GESTURE_CONFIG } from '@angular/platform-browser';

// import ngx-translate and the http loader
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';

export class CustomHammerConfig extends HammerGestureConfig {
  overrides = <any>{
    'pinch': { enable: false },
    'rotate': { enable: false }
  };
}

import { ScrollEventModule } from 'ngx-scroll-event';
import { AngularDraggableModule } from 'angular2-draggable';
import { CookieService } from 'ngx-cookie-service';
import { SimplePdfViewerModule } from 'simple-pdf-viewer';


import { AppMaterialModule } from './app-material.module';

// COMPONENTS
import { AppComponent } from './app.component';
import { LoginComponent } from './login/login.component';
import { ForgotPasswordComponent } from './login/forgotPassword/forgotPassword.component';
import { SignaturesComponent } from './signatures/signatures.component';
import { SignaturePadPageComponent } from './pad/pad.component';
import { SignaturePadModule } from 'angular2-signaturepad';
import { DrawerComponent } from './drawer/drawer.component';
import { DocumentComponent } from './document/document.component';
import { DocumentSignListComponent } from './documentSignList/document-sign-list.component';
import { DocumentNoteListComponent } from './documentNoteList/document-note-list.component';
import { DocumentNotePadComponent } from './documentNotePad/document-note-pad.component';
import { SidebarComponent } from './sidebar/sidebar.component';
import { ProfileComponent } from './profile/profile.component';
import { ConfirmModalComponent } from './modal/confirm-modal.component';
import { WarnModalComponent } from './modal/warn-modal.component';
import { SuccessInfoValidBottomSheetComponent } from './modal/success-info-valid.component';
import { RejectInfoBottomSheetComponent } from './modal/reject-info.component';

// SERVICES
import { NotificationService, CustomSnackbarComponent } from './service/notification.service';
import { SignaturesContentService } from './service/signatures.service';



@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    ForgotPasswordComponent,
    SignaturesComponent,
    SignaturePadPageComponent,
    DrawerComponent,
    DocumentComponent,
    DocumentSignListComponent,
    DocumentNoteListComponent,
    DocumentNotePadComponent,
    SidebarComponent,
    WarnModalComponent,
    ConfirmModalComponent,
    SuccessInfoValidBottomSheetComponent,
    RejectInfoBottomSheetComponent,
    ProfileComponent,
    CustomSnackbarComponent
  ],
  imports: [
    FormsModule,
    ReactiveFormsModule,
    BrowserModule,
    BrowserAnimationsModule,
    HttpClientModule,
    RouterModule,
    HttpClientModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient]
      }
    }),
    SignaturePadModule,
    ScrollEventModule,
    AngularDraggableModule,
    SimplePdfViewerModule,
    AppMaterialModule,
    RouterModule.forRoot([
      { path: 'documents/:id', component: DocumentComponent },
      { path: 'documents', component: DocumentComponent },
      { path: 'login', component: LoginComponent },
      { path: 'forgotPassword', component: ForgotPasswordComponent },
      { path: '**', redirectTo: 'login', pathMatch: 'full' },
    ], { useHash: true }),
  ],
  entryComponents: [
    CustomSnackbarComponent,
    WarnModalComponent,
    ConfirmModalComponent,
    SuccessInfoValidBottomSheetComponent,
    RejectInfoBottomSheetComponent,
    SignaturesComponent
  ],
  providers: [SignaturesContentService,
    NotificationService,
    {
      provide: HAMMER_GESTURE_CONFIG,
      useClass: CustomHammerConfig
    },
    CookieService],
  exports: [
    RouterModule
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

// For traductions
export function HttpLoaderFactory(http: HttpClient) {
  return new TranslateHttpLoader(http, './frontend/assets/i18n/', '.json');
}
