import { Action } from '@ngrx/store';

export const SHOW_DRAWER = 'SHOW_DRAWER';
export const HIDE_DRAWER = 'HIDE_DRAWER';

export const initialState = false;

export function drawerReducer(state = initialState, action: Action) {
  switch (action.type) {
    case SHOW_DRAWER: {
      return true;
    }

    case HIDE_DRAWER: {
      return false;
    }

    default: {
      return state;
    }
  }
}
