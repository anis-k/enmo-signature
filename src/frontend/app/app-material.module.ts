import { NgModule } from '@angular/core';

import {
  MatSidenavModule,
  MatListModule,
  MatDialogModule,
  MatBottomSheetModule,
  MatRippleModule,
  MatSnackBarModule,
  MatButtonModule,
  MatIconModule,
  MatProgressSpinnerModule,
  MatCardModule,
  MatInputModule,
  MatExpansionModule,
  MatTabsModule,
  MatSliderModule,
  MatSelectModule
} from '@angular/material';

import { MatMenuModule } from '@angular/material/menu';

import {DragDropModule} from '@angular/cdk/drag-drop';

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
      MatSelectModule
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
      MatSelectModule
  ]
})
export class AppMaterialModule { }
