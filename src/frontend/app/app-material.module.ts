import { NativeDateAdapter, DateAdapter, MAT_DATE_FORMATS, MAT_DATE_LOCALE } from "@angular/material";

export class AppDateAdapter extends NativeDateAdapter {
    parse(value: any): Date | null {
        if ((typeof value === 'string') && (value.indexOf('/') > -1)) {
            const str = value.split('/');
            const year = Number(str[2]);
            const month = Number(str[1]) - 1;
            const date = Number(str[0]);
            return new Date(year, month, date);
        }
        const timestamp = typeof value === 'number' ? value : Date.parse(value);
        return isNaN(timestamp) ? null : new Date(timestamp);
    }

    format(date: Date, displayFormat: Object): string {
        if (displayFormat == "input") {
            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();
            return this._to2digit(day) + '/' + this._to2digit(month) + '/' + year;
        } else {
            return date.toDateString();
        }
    }

    private _to2digit(n: number) {
        return ('00' + n).slice(-2);
    }
}

export const APP_DATE_FORMATS =
    {
        parse: {
            dateInput: { month: 'short', year: 'numeric', day: 'numeric' }
        },
        display: {
            // dateInput: { month: 'short', year: 'numeric', day: 'numeric' },
            dateInput: 'input',
            monthYearLabel: { month: 'short', year: 'numeric', day: 'numeric' },
            dateA11yLabel: { year: 'numeric', month: 'long', day: 'numeric' },
            monthYearA11yLabel: { year: 'numeric', month: 'long' },
        }
    };


import { NgModule } from '@angular/core';
import { DndModule } from 'ng2-dnd';
import {
    MatSelectModule,
    MatCheckboxModule,
    MatSlideToggleModule,
    MatInputModule,
    MatTooltipModule,
    MatTabsModule,
    MatSidenavModule,
    MatButtonModule,
    MatCardModule,
    MatButtonToggleModule,
    MatProgressSpinnerModule,
    MatProgressBarModule,
    MatToolbarModule,
    MatMenuModule,
    MatGridListModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatPaginatorIntl,
    MatDatepickerModule,
    MatNativeDateModule,
    MatExpansionModule,
    MatAutocompleteModule,
    MatSnackBar,
    MatSnackBarModule,
    MatIcon,
    MatIconModule,
    MatDialogActions,
    MatDialogModule,
    MatListModule,
    MatChipsModule,
    MatStepperModule,
    MatRadioModule,
    MatSliderModule,
    MatBadgeModule,
    MatBottomSheetModule
} from '@angular/material';

import { CdkTableModule } from '@angular/cdk/table';
import { getFrenchPaginatorIntl } from './french-paginator-intl';

@NgModule({
    imports: [
        MatCheckboxModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatInputModule,
        MatTooltipModule,
        MatTabsModule,
        MatSidenavModule,
        MatButtonModule,
        MatCardModule,
        MatButtonToggleModule,
        MatProgressSpinnerModule,
        MatProgressBarModule,
        MatToolbarModule,
        MatMenuModule,
        MatGridListModule,
        MatTableModule,
        MatPaginatorModule,
        MatSortModule,
        MatDatepickerModule,
        MatNativeDateModule,
        MatExpansionModule,
        MatAutocompleteModule,
        MatSnackBarModule,
        MatIconModule,
        MatDialogModule,
        MatListModule,
        MatChipsModule,
        MatStepperModule,
        MatRadioModule,
        MatSliderModule,
        MatBadgeModule,
        MatBottomSheetModule,
        DndModule.forRoot()
    ],
    exports: [
        MatCheckboxModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatInputModule,
        MatTooltipModule,
        MatTabsModule,
        MatSidenavModule,
        MatButtonModule,
        MatCardModule,
        MatButtonToggleModule,
        MatProgressSpinnerModule,
        MatProgressBarModule,
        MatToolbarModule,
        MatMenuModule,
        MatGridListModule,
        MatTableModule,
        MatPaginatorModule,
        MatSortModule,
        MatDatepickerModule,
        MatNativeDateModule,
        MatExpansionModule,
        MatAutocompleteModule,
        MatSnackBarModule,
        MatIconModule,
        MatDialogModule,
        MatListModule,
        MatChipsModule,
        MatStepperModule,
        MatRadioModule,
        MatSliderModule,
        MatBadgeModule,
        MatBottomSheetModule,
        DndModule
    ],
    providers: [
        { provide: MatPaginatorIntl, useValue: getFrenchPaginatorIntl() },
        { provide: DateAdapter, useClass: AppDateAdapter },
        { provide: MAT_DATE_FORMATS, useValue: APP_DATE_FORMATS },
        { provide: MAT_DATE_LOCALE, useValue: 'fr-FR' },
    ]
})
export class AppMaterialModule { }