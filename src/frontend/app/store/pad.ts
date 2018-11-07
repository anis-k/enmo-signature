import { Action } from '@ngrx/store';

export const SHOW_PAD = 'SHOW_PAD';
export const HIDE_PAD = 'HIDE_PAD';

export const initialState = false;

export function padReducer(state = initialState, action: Action) {
  switch (action.type) {

    case SHOW_PAD: {
      return true;
    }

    case HIDE_PAD: {
      return false;
    }

    default: {
      return state;
    }
  }
}
