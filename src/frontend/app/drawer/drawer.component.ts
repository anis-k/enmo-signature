import { Component } from '@angular/core';
import { Store, select } from '@ngrx/store';
import { Observable } from 'rxjs';
import { SHOW_DRAWER, HIDE_DRAWER } from '../store/drawer';
import { HIDE_PAD, SHOW_PAD} from '../store/pad';
import { SHOW_SIGNATURES, HIDE_SIGNATURES } from '../store/signatures';

interface AppState {
  drawer: boolean;
}

@Component({
  selector: 'app-drawer',
  templateUrl: 'drawer.component.html',
  styleUrls: ['drawer.component.styl']
})
export class DrawerComponent {
  drawer$: Observable<boolean>;

  constructor(private store: Store<AppState>) {
    this.drawer$ = store.pipe(select('drawer'));
  }

  openDrawer() {
    this.store.dispatch({ type: SHOW_DRAWER });
    this.store.dispatch({ type: SHOW_SIGNATURES });
  }

  closeDrawer() {
    this.store.dispatch({ type: HIDE_DRAWER });
    this.store.dispatch({ type: HIDE_PAD });
    this.store.dispatch({ type: HIDE_SIGNATURES });
  }

}
