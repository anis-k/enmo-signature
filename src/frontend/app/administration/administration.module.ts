import { NgModule }                             from '@angular/core';

import { SharedModule }                         from '../app-common.module';

import { SortPipe }                             from '../../plugins/sorting.pipe';

//import { MenuComponent }                        from '../menu/menu.component';
//import { MenuNavComponent }                     from '../menu/menu-nav.component';
//import { MenuTopComponent }                     from '../menu/menu-top.component';

import { AdministrationRoutingModule }          from './administration-routing.module';

import { AdministrationComponent }                      from './administration.component';
import { UsersAdministrationComponent, UsersAdministrationRedirectModalComponent }      from './user/users-administration.component';
import { GroupsAdministrationComponent, GroupsAdministrationRedirectModalComponent }    from './group/groups-administration.component';
import { UserAdministrationComponent, UserAdministrationRedirectModalComponent }                  from './user/user-administration.component';
import { GroupAdministrationComponent }                 from './group/group-administration.component';
import { BasketsAdministrationComponent }               from './basket/baskets-administration.component';
import { BasketAdministrationComponent, BasketAdministrationSettingsModalComponent, BasketAdministrationGroupListModalComponent }                from './basket/basket-administration.component';
import { EntitiesAdministrationComponent, EntitiesAdministrationRedirectModalComponent} from './entity/entities-administration.component';
import { DiffusionModelsAdministrationComponent }       from './diffusionModel/diffusionModels-administration.component';
import { DiffusionModelAdministrationComponent }        from './diffusionModel/diffusionModel-administration.component';
import { DoctypesAdministrationComponent, DoctypesAdministrationRedirectModalComponent }              from './doctype/doctypes-administration.component';
import { StatusesAdministrationComponent }              from './status/statuses-administration.component';
import { StatusAdministrationComponent }                from './status/status-administration.component';
import { ActionsAdministrationComponent }               from './action/actions-administration.component';
import { ActionAdministrationComponent }                from './action/action-administration.component';
import { ParametersAdministrationComponent }            from './parameter/parameters-administration.component';
import { ParameterAdministrationComponent }             from './parameter/parameter-administration.component';
import { PrioritiesAdministrationComponent }            from './priority/priorities-administration.component';
import { PriorityAdministrationComponent }              from './priority/priority-administration.component';
import { ReportsAdministrationComponent }               from './report/reports-administration.component';
import { HistoryAdministrationComponent }               from './history/history-administration.component';
import { UpdateStatusAdministrationComponent }          from './updateStatus/update-status-administration.component';
import { NotificationsAdministrationComponent }         from './notification/notifications-administration.component';
import { NotificationAdministrationComponent }          from './notification/notification-administration.component';
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
    imports:      [
        SharedModule,
        AdministrationRoutingModule
    ],
    declarations: [
        //MenuComponent,
        //MenuNavComponent,
        //MenuTopComponent,
        AdministrationComponent,
        UsersAdministrationComponent,
        UserAdministrationComponent,
        GroupsAdministrationComponent,
        GroupAdministrationComponent,
        BasketsAdministrationComponent,
        BasketAdministrationComponent,
        DoctypesAdministrationComponent,
        EntitiesAdministrationComponent,
        StatusesAdministrationComponent,
        StatusAdministrationComponent,
        ActionsAdministrationComponent,
        ActionAdministrationComponent,
        ParametersAdministrationComponent,
        ParameterAdministrationComponent,
        PrioritiesAdministrationComponent,
        PriorityAdministrationComponent,
        ReportsAdministrationComponent,
        HistoryAdministrationComponent,
        UpdateStatusAdministrationComponent,
        ContactsGroupsAdministrationComponent,
        ContactsGroupAdministrationComponent,
        ContactsFillingAdministrationComponent,
        NotificationsAdministrationComponent,
        NotificationAdministrationComponent,
        UsersAdministrationRedirectModalComponent,
        UserAdministrationRedirectModalComponent,
        EntitiesAdministrationRedirectModalComponent,
        GroupsAdministrationRedirectModalComponent,
        BasketAdministrationSettingsModalComponent,
        BasketAdministrationGroupListModalComponent,
        DoctypesAdministrationRedirectModalComponent,
        DiffusionModelsAdministrationComponent,
        DiffusionModelAdministrationComponent,
        SortPipe,
        VersionsUpdateAdministrationComponent,
        DocserversAdministrationComponent,
        DocserverAdministrationComponent,
        TemplatesAdministrationComponent,
        TemplateAdministrationComponent,
        SecuritiesAdministrationComponent,
    ],
    entryComponents: [
        UsersAdministrationRedirectModalComponent,
        UserAdministrationRedirectModalComponent,
        EntitiesAdministrationRedirectModalComponent,
        GroupsAdministrationRedirectModalComponent,
        BasketAdministrationSettingsModalComponent,
        BasketAdministrationGroupListModalComponent,
        DoctypesAdministrationRedirectModalComponent
    ],
})
export class AdministrationModule {}