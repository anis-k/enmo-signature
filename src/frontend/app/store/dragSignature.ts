import { Action } from '@ngrx/store';

export const SHOW_SIGNATURE_DRAG = 'SHOW_SIGNATURE_DRAG';
export const HIDE_SIGNATURE_DRAG = 'HIDE_SIGNATURE_DRAG';

export const initialState = false;

export function signatureDragReducer(state = initialState, action: Action) {
  switch (action.type) {

    case SHOW_SIGNATURE_DRAG: {
      return true;
    }

    case HIDE_SIGNATURE_DRAG: {
      return false;
    }

    default: {
      return state;
    }
  }
}
