import { NgModule }                         from '@angular/core';
import { RouterModule }                     from '@angular/router';
import { AuthGuard }                        from './service/auth.guard';

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

@NgModule({
    imports: [
        RouterModule.forRoot([
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
            { path: 'documents/:id', canActivate: [AuthGuard], component: DocumentComponent },
            { path: 'documents', canActivate: [AuthGuard], component: DocumentComponent },
            { path: 'login', component: LoginComponent },
            { path: 'forgot-password', component: ForgotPasswordComponent },
            { path: 'update-password', component: UpdatePasswordComponent },
            { path: '**', redirectTo: 'login', pathMatch: 'full' },
          ]),
    ],
    exports: [
        RouterModule
    ]
})
export class AppRoutingModule {}
