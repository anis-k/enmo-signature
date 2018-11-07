import { Action } from '@ngrx/store';

export const SHOW_SIDEBAR = 'SHOW_SIDEBAR';
export const HIDE_SIDEBAR = 'HIDE_SIDEBAR';

export const initialState = false;

export function sidebarReducer(state = initialState, action: Action) {
  switch (action.type) {

    case SHOW_SIDEBAR: {
      return true;
    }

    case HIDE_SIDEBAR: {
      return false;
    }

    default: {
      return state;
    }
  }
}
