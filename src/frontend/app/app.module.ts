import { BrowserModule, HammerModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule, HttpClient, HTTP_INTERCEPTORS } from '@angular/common/http';
import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { RouteReuseStrategy } from '@angular/router';

import { IonicModule, IonicRouteStrategy } from '@ionic/angular';

import localeFr from '@angular/common/locales/fr';
import { registerLocaleData } from '@angular/common';

import { AuthInterceptor } from './service/auth-interceptor.service';

// import ngx-translate and the http loader
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';

import { NgxExtendedPdfViewerModule } from 'ngx-extended-pdf-viewer';
import { DragScrollModule } from 'ngx-drag-scroll';
import { AngularDraggableModule } from 'angular2-draggable';
import { CookieService } from 'ngx-cookie-service';
import { NgPipesModule } from 'ngx-pipes';
import { LatinisePipe } from 'ngx-pipes';

import { AppMaterialModule } from './app-material.module';
import { AppRoutingModule } from './app-routing.module';
import { DragDropDirective } from './plugins/upload-file-dnd.directive';


// COMPONENTS
import { AppComponent } from './app.component';
import { LoginComponent } from './login/login.component';
import { HomeComponent } from './home/home.component';
import { IndexationComponent } from './indexation/indexation.component';
import { ForgotPasswordComponent } from './login/forgotPassword/forgotPassword.component';
import { SignaturesComponent } from './signatures/signatures.component';
import { SignaturePadPageComponent } from './pad/pad.component';
import { SignaturePadModule } from 'angular2-signaturepad';
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
import { VisaWorkflowComponent } from './document/visa-workflow/visa-workflow.component';
import { DocumentListComponent } from './document/document-list/document-list.component';
import { MainDocumentDetailComponent } from './document/main-document-detail/main-document-detail.component';
import { AssociatedDocumentsComponent } from './document/associated-documents/associated-documents.component';
import { UpdatePasswordComponent } from './login/updatePassword/updatePassword.component';
import { PasswordModificationComponent } from './login/passwordModification/password-modification.component';
import { VisaWorkflowModelsComponent } from './document/visa-workflow/models/visa-workflow-models.component';
import { SearchComponent } from './search/search.component';
import { SignaturePositionComponent } from './indexation/signature-position/signature-position.component';
import { DevToolComponent } from './service/debug/dev-tool.component';
import { DevLangComponent } from './service/debug/dev-lang.component';
import { DocumentDateListComponent } from './documentDateList/document-date-list.component';
import { DateOptionModalComponent } from './documentDateList/dateOption/date-option-modal.component';


// ADMINISTRATION
import { AdminSidebarComponent } from './sidebar/administration/admin-sidebar.component';

import { AdministrationComponent } from './administration/home/administration.component';
import { UsersListComponent } from './administration/user/users-list.component';
import { UserComponent } from './administration/user/user.component';
import { ConnectionComponent } from './administration/connection/connection.component';
import { LdapListComponent } from './administration/connection/ldap/ldap-list.component';
import { LdapComponent } from './administration/connection/ldap/ldap.component';
import { CheckConnectionComponent } from './administration/connection/ldap/check-connection.component';
import { SendmailComponent } from './administration/sendmail/sendmail.component';
import { CheckEmailConnectionComponent } from './administration/sendmail/check-email-connection.component';

import { GroupsListComponent } from './administration/group/groups-list.component';
import { GroupComponent } from './administration/group/group.component';
import { UsersComponent } from './administration/group/list/users.component';
import { SecuritiesAdministrationComponent } from './administration/security/securities-administration.component';
import { HistoryListComponent } from './administration/history/history-list.component';
import { CustomizationComponent } from './administration/customization/customization.component';


// SERVICES
import { NotificationService } from './service/notification.service';
import { SignaturesContentService } from './service/signatures.service';
import { FiltersService } from './service/filters.service';
import { SignatureMethodModalComponent } from './service/signature-method/signature-method-modal.component';

// OTHERS
import { ConfirmComponent } from './plugins/confirm.component';
import { AlertComponent } from './plugins/alert.component';
import { PluginAutocompleteComponent } from './plugins/autocomplete/autocomplete.component';
import { SortPipe } from './plugins/sorting.pipe';

registerLocaleData(localeFr, 'fr-FR');

@NgModule({
    declarations: [
        AppComponent,
        DragDropDirective,
        LoginComponent,
        HomeComponent,
        ForgotPasswordComponent,
        UpdatePasswordComponent,
        SignaturesComponent,
        SignaturePadPageComponent,
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
        VisaWorkflowComponent,
        DocumentListComponent,
        MainDocumentDetailComponent,
        AssociatedDocumentsComponent,
        AdministrationComponent,
        UsersListComponent,
        UserComponent,
        ConfirmComponent,
        AlertComponent,
        AdminSidebarComponent,
        ConnectionComponent,
        LdapListComponent,
        LdapComponent,
        SendmailComponent,
        GroupsListComponent,
        GroupComponent,
        PluginAutocompleteComponent,
        SortPipe,
        SecuritiesAdministrationComponent,
        PasswordModificationComponent,
        UsersComponent,
        CheckConnectionComponent,
        CheckEmailConnectionComponent,
        IndexationComponent,
        VisaWorkflowModelsComponent,
        SearchComponent,
        SignaturePositionComponent,
        DevToolComponent,
        DevLangComponent,
        SignatureMethodModalComponent,
        HistoryListComponent,
        DocumentDateListComponent,
        DateOptionModalComponent,
        CustomizationComponent,
    ],
    imports: [
        FormsModule,
        ReactiveFormsModule,
        BrowserModule,
        BrowserAnimationsModule,
        HttpClientModule,
        IonicModule.forRoot({ mode: 'md' }),
        TranslateModule.forRoot({
            loader: {
                provide: TranslateLoader,
                useFactory: HttpLoaderFactory,
                deps: [HttpClient]
            }
        }),
        SignaturePadModule,
        NgxExtendedPdfViewerModule,
        DragScrollModule,
        NgPipesModule,
        AngularDraggableModule,
        AppMaterialModule,
        AppRoutingModule,
        HammerModule,
    ],
    entryComponents: [
        WarnModalComponent,
        ConfirmModalComponent,
        SuccessInfoValidBottomSheetComponent,
        RejectInfoBottomSheetComponent,
        SignaturesComponent,
        ConfirmComponent,
        AlertComponent
    ],
    providers: [
        { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true },
        SignaturesContentService,
        FiltersService,
        NotificationService,
        { provide: RouteReuseStrategy, useClass: IonicRouteStrategy },
        CookieService,
        LatinisePipe
    ],
    bootstrap: [AppComponent],
    schemas: [ CUSTOM_ELEMENTS_SCHEMA ]
})
export class AppModule { }

export class TranslateBackendHttpLoader implements TranslateLoader {

    constructor(private http: HttpClient) { }

    /**
   * Gets the translations from the server
   * @param lang
   * @returns {any}
   */
    public getTranslation(lang: string): any {

        return this.http.get('../rest/languages/' + lang);
    }
}

// For traductions
export function HttpLoaderFactory(http: HttpClient) {
    return new TranslateBackendHttpLoader(http);
}
