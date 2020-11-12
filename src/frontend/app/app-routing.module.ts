import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { AuthGuard } from './service/auth.guard';

import { AdministrationComponent } from './administration/home/administration.component';
import { UsersListComponent } from './administration/user/users-list.component';
import { UserComponent } from './administration/user/user.component';
import { GroupsListComponent } from './administration/group/groups-list.component';
import { GroupComponent } from './administration/group/group.component';
import { ConnectionComponent } from './administration/connection/connection.component';
import { LdapListComponent } from './administration/connection/ldap/ldap-list.component';
import { LdapComponent } from './administration/connection/ldap/ldap.component';
import { SendmailComponent } from './administration/sendmail/sendmail.component';
import { DocumentComponent } from './document/document.component';
import { LoginComponent } from './login/login.component';
import { ForgotPasswordComponent } from './login/forgotPassword/forgotPassword.component';
import { UpdatePasswordComponent } from './login/updatePassword/updatePassword.component';
import { SecuritiesAdministrationComponent } from './administration/security/securities-administration.component';
import { PasswordModificationComponent } from './login/passwordModification/password-modification.component';
import { ProfileComponent } from './profile/profile.component';
import { HomeComponent } from './home/home.component';
import { IndexationComponent } from './indexation/indexation.component';

@NgModule({
    imports: [
        RouterModule.forRoot([
    { path: 'home', canActivate: [AuthGuard], component: HomeComponent },
    { path: 'indexation', canActivate: [AuthGuard], component: IndexationComponent },
    { path: 'profile', canActivate: [AuthGuard], component: ProfileComponent },
    { path: 'administration', canActivate: [AuthGuard], component: AdministrationComponent },
    { path: 'administration/users', canActivate: [AuthGuard], component: UsersListComponent },
    { path: 'administration/users/new', canActivate: [AuthGuard], component: UserComponent },
    { path: 'administration/users/:id', canActivate: [AuthGuard], component: UserComponent },
    { path: 'administration/groups', canActivate: [AuthGuard], component: GroupsListComponent },
    { path: 'administration/groups/new', canActivate: [AuthGuard], component: GroupComponent },
    { path: 'administration/groups/:id', canActivate: [AuthGuard], component: GroupComponent },
    { path: 'administration/connections', canActivate: [AuthGuard], component: ConnectionComponent },
    { path: 'administration/connections/ldaps', canActivate: [AuthGuard], component: LdapListComponent },
    { path: 'administration/connections/ldaps/new', canActivate: [AuthGuard], component: LdapComponent },
    { path: 'administration/connections/ldaps/:id', canActivate: [AuthGuard], component: LdapComponent },
    { path: 'administration/emailConfiguration', canActivate: [AuthGuard], component: SendmailComponent },
    { path: 'administration/passwordRules', canActivate: [AuthGuard], component: SecuritiesAdministrationComponent },
    { path: 'documents/:id', canActivate: [AuthGuard], component: DocumentComponent },
    { path: 'login', canActivate: [AuthGuard], component: LoginComponent },
    { path: 'forgot-password', component: ForgotPasswordComponent },
    { path: 'update-password', component: UpdatePasswordComponent },
    { path: 'password-modification', component: PasswordModificationComponent },
    { path: '**', redirectTo: 'login', pathMatch: 'full' },
], { relativeLinkResolution: 'legacy' }),
    ],
    exports: [
        RouterModule
    ]
})
export class AppRoutingModule { }
