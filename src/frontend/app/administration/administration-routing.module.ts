import { NgModule }     from '@angular/core';
import { RouterModule } from '@angular/router';

import { AdministrationComponent }                      from './administration.component';
import { UsersAdministrationComponent }                 from './user/users-administration.component';
import { UserAdministrationComponent }                  from './user/user-administration.component';
import { GroupsAdministrationComponent }                from './group/groups-administration.component';
import { GroupAdministrationComponent }                 from './group/group-administration.component';
import { BasketsAdministrationComponent }               from './basket/baskets-administration.component';
import { BasketAdministrationComponent }                from './basket/basket-administration.component';
import { DoctypesAdministrationComponent }              from './doctype/doctypes-administration.component';
import { DiffusionModelsAdministrationComponent }       from './diffusionModel/diffusionModels-administration.component';
import { DiffusionModelAdministrationComponent }        from './diffusionModel/diffusionModel-administration.component';
import { EntitiesAdministrationComponent }              from './entity/entities-administration.component';
import { StatusesAdministrationComponent }              from './status/statuses-administration.component';
import { StatusAdministrationComponent }                from './status/status-administration.component';
import { ActionsAdministrationComponent }               from './action/actions-administration.component';
import { ActionAdministrationComponent }                from './action/action-administration.component';
import { ParameterAdministrationComponent }             from './parameter/parameter-administration.component';
import { ParametersAdministrationComponent }            from './parameter/parameters-administration.component';
import { PrioritiesAdministrationComponent }            from './priority/priorities-administration.component';
import { PriorityAdministrationComponent }              from './priority/priority-administration.component';
import { ReportsAdministrationComponent }               from './report/reports-administration.component';
import { NotificationsAdministrationComponent }         from './notification/notifications-administration.component';
import { NotificationAdministrationComponent }          from './notification/notification-administration.component';
import { HistoryAdministrationComponent }               from './history/history-administration.component';
import { UpdateStatusAdministrationComponent }          from './updateStatus/update-status-administration.component';
import { ContactsGroupsAdministrationComponent }        from './contact/contacts-groups-administration.component';
import { ContactsGroupAdministrationComponent }         from './contact/contacts-group-administration.component';
import { ContactsFillingAdministrationComponent }       from './contact/contacts-filling-administration.component';
import { VersionsUpdateAdministrationComponent }        from './versionUpdate/versions-update-administration.component';
import { DocserversAdministrationComponent }            from './docserver/docservers-administration.component';
import { DocserverAdministrationComponent }             from './docserver/docserver-administration.component';
import { TemplatesAdministrationComponent }             from './template/templates-administration.component';
import { TemplateAdministrationComponent }              from './template/template-administration.component';
import { SecuritiesAdministrationComponent }            from './security/securities-administration.component';

@NgModule({
    imports: [
        RouterModule.forChild([
            { path: 'administration', component: AdministrationComponent },
            { path: 'administration/users', component: UsersAdministrationComponent },
            { path: 'administration/users/new', component: UserAdministrationComponent },
            { path: 'administration/users/:id', component: UserAdministrationComponent },
            { path: 'administration/groups', component: GroupsAdministrationComponent },
            { path: 'administration/groups/new', component: GroupAdministrationComponent },
            { path: 'administration/groups/:id', component: GroupAdministrationComponent },
            { path: 'administration/baskets', component: BasketsAdministrationComponent },
            { path: 'administration/baskets/new', component: BasketAdministrationComponent },
            { path: 'administration/baskets/:id', component: BasketAdministrationComponent },
            { path: 'administration/doctypes', component: DoctypesAdministrationComponent },
            { path: 'administration/diffusionModels', component: DiffusionModelsAdministrationComponent },
            { path: 'administration/diffusionModels/new', component: DiffusionModelAdministrationComponent },
            { path: 'administration/diffusionModels/:id', component: DiffusionModelAdministrationComponent },
            { path: 'administration/entities', component: EntitiesAdministrationComponent },
            { path: 'administration/statuses', component: StatusesAdministrationComponent },
            { path: 'administration/statuses/new', component: StatusAdministrationComponent },
            { path: 'administration/statuses/:identifier', component: StatusAdministrationComponent },
            { path: 'administration/parameters', component: ParametersAdministrationComponent },
            { path: 'administration/parameters/new', component: ParameterAdministrationComponent },
            { path: 'administration/parameters/:id', component: ParameterAdministrationComponent },
            { path: 'administration/reports', component : ReportsAdministrationComponent},
            { path: 'administration/priorities', component : PrioritiesAdministrationComponent },
            { path: 'administration/priorities/new', component : PriorityAdministrationComponent },
            { path: 'administration/priorities/:id', component : PriorityAdministrationComponent },
            { path: 'administration/actions', component: ActionsAdministrationComponent },
            { path: 'administration/actions/new', component: ActionAdministrationComponent },
            { path: 'administration/actions/:id', component: ActionAdministrationComponent },
            { path: 'administration/notifications', component: NotificationsAdministrationComponent },
            { path: 'administration/notifications/new', component: NotificationAdministrationComponent },
            { path: 'administration/notifications/:identifier', component: NotificationAdministrationComponent },
            { path: 'administration/history', component: HistoryAdministrationComponent },
            { path: 'administration/update-status', component: UpdateStatusAdministrationComponent },
            { path: 'administration/contacts-groups', component: ContactsGroupsAdministrationComponent },
            { path: 'administration/contacts-groups/new', component: ContactsGroupAdministrationComponent },
            { path: 'administration/contacts-groups/:id', component: ContactsGroupAdministrationComponent },
            { path: 'administration/contacts-filling', component: ContactsFillingAdministrationComponent },
            { path: 'administration/versions-update', component: VersionsUpdateAdministrationComponent },
            { path: 'administration/docservers', component: DocserversAdministrationComponent },
            { path: 'administration/docservers/new', component: DocserverAdministrationComponent },
            { path: 'administration/templates', component: TemplatesAdministrationComponent },
            { path: 'administration/templates/new', component: TemplateAdministrationComponent },
            { path: 'administration/templates/:id', component: TemplateAdministrationComponent },
            { path: 'administration/securities', component: SecuritiesAdministrationComponent },
        ]),
    ],
    exports: [
        RouterModule
    ]
})
export class AdministrationRoutingModule {}
