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
  MatInputModule
} from '@angular/material';

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
      MatInputModule
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
      MatInputModule
  ]
})
export class AppMaterialModule { }
