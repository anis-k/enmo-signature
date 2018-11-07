import { Action } from '@ngrx/store';

export const SHOW_SIGNATURES = 'SHOW_SIGNATURES';
export const HIDE_SIGNATURES = 'HIDE_SIGNATURES';

export const initialState = true;

export function signaturesReducer(state = initialState, action: Action) {
  switch (action.type) {

    case SHOW_SIGNATURES: {
      return true;
    }

    case HIDE_SIGNATURES: {
      return false;
    }

    default: {
      return state;
    }
  }
}
