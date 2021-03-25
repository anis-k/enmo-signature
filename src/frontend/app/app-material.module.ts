import { NgModule } from '@angular/core';

import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { MatBadgeModule } from '@angular/material/badge';
import { MatBottomSheetModule } from '@angular/material/bottom-sheet';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatRippleModule } from '@angular/material/core';
import { MatDialogModule } from '@angular/material/dialog';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatListModule } from '@angular/material/list';
import { MatPaginatorModule, MatPaginatorIntl } from '@angular/material/paginator';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSliderModule } from '@angular/material/slider';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { MatSortModule } from '@angular/material/sort';
import { MatTableModule } from '@angular/material/table';
import { MatTabsModule } from '@angular/material/tabs';

import { MatMenuModule } from '@angular/material/menu';

import { DragDropModule } from '@angular/cdk/drag-drop';

import { getFrenchPaginatorIntl } from './plugins/paginator-fr-intl';

@NgModule({
    imports: [
        MatSidenavModule,
        MatListModule,
        MatDialogModule,
        MatBottomSheetModule,
        MatRippleModule,
        DragDropModule,
        MatSnackBarModule,
        MatButtonModule,
        MatIconModule,
        MatProgressSpinnerModule,
        MatCardModule,
        MatInputModule,
        MatExpansionModule,
        MatMenuModule,
        MatTabsModule,
        MatSliderModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatBadgeModule,
        MatTableModule,
        MatPaginatorModule,
        MatSortModule,
        MatAutocompleteModule,
        MatCheckboxModule
    ],
    exports: [
        MatSidenavModule,
        MatListModule,
        MatDialogModule,
        MatBottomSheetModule,
        MatRippleModule,
        DragDropModule,
        MatSnackBarModule,
        MatButtonModule,
        MatIconModule,
        MatProgressSpinnerModule,
        MatCardModule,
        MatInputModule,
        MatExpansionModule,
        MatMenuModule,
        MatTabsModule,
        MatSliderModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatBadgeModule,
        MatTableModule,
        MatPaginatorModule,
        MatSortModule,
        MatAutocompleteModule,
        MatCheckboxModule
    ],
    providers: [
        { provide: MatPaginatorIntl, useValue: getFrenchPaginatorIntl() },
    ]
})
export class AppMaterialModule { }
