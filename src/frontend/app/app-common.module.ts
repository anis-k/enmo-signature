import { CommonModule }                         from '@angular/common';

import { NgModule }                             from '@angular/core';

/*CORE IMPORTS*/
import { BrowserModule }                        from '@angular/platform-browser';
import { BrowserAnimationsModule }              from '@angular/platform-browser/animations';
import { FormsModule, ReactiveFormsModule }     from '@angular/forms';
import { HttpClientModule }                     from '@angular/common/http';
import { RouterModule }                         from '@angular/router';

/*PLUGINS IMPORTS*/
import { TimeAgoPipe }                          from '../plugins/timeAgo.pipe';
import { TimeLimitPipe }                        from '../plugins/timeLimit.pipe';
import { FilterListPipe }                       from '../plugins/filterList.pipe';

/*FRONT IMPORTS*/
import { AppMaterialModule }                    from './app-material.module';

import { SpeedDialFabComponent }                from '../plugins/mat-speed-dial-fab/speed-dial-fab.component';

/*MENU COMPONENT*/
import { MainHeaderComponent }                  from './menu/main-header.component';
import { MenuComponent }                        from './menu/menu.component';
import { MenuNavComponent }                     from './menu/menu-nav.component';
import { MenuShortcutComponent, IndexingGroupModalComponent }                from './menu/menu-shortcut.component';

/*SEARCH*/
import { SearchHomeComponent }                        from './search/search-home.component';

/*SEARCH*/
import { BasketHomeComponent }                        from './basket/basket-home.component';

@NgModule({
    imports: [
        CommonModule,
        BrowserModule,
        BrowserAnimationsModule,
        FormsModule,
        HttpClientModule,
        RouterModule,
        AppMaterialModule
    ],
    declarations: [
        MainHeaderComponent,
        MenuComponent,
        MenuNavComponent,
        MenuShortcutComponent,
        SearchHomeComponent,
        BasketHomeComponent,
        TimeAgoPipe,
        TimeLimitPipe,
        FilterListPipe,
        IndexingGroupModalComponent,
        SpeedDialFabComponent
    ],
    exports: [
        CommonModule,
        MainHeaderComponent,
        MenuComponent,
        MenuNavComponent,
        MenuShortcutComponent,
        SearchHomeComponent,
        BasketHomeComponent,
        BrowserModule,
        BrowserAnimationsModule,
        FormsModule,
        ReactiveFormsModule,
        HttpClientModule,
        RouterModule,
        AppMaterialModule,
        TimeAgoPipe,
        TimeLimitPipe,
        FilterListPipe,
        SpeedDialFabComponent
    ],
    entryComponents: [
        IndexingGroupModalComponent
    ],
})
export class SharedModule { }
