import { Action } from '@ngrx/store';

export const SHOW_ANNOTATION = 'SHOW_ANNOTATION';
export const HIDE_ANNOTATION = 'HIDE_ANNOTATION';

export const initialState = false;

export function annotationReducer(state = initialState, action: Action) {
  switch (action.type) {

    case SHOW_ANNOTATION: {
      return true;
    }

    case HIDE_ANNOTATION: {
      return false;
    }

    default: {
      return state;
    }
  }
}
