import { LANG_EN } from '../lang/lang-en';
import { LANG_FR } from '../lang/lang-fr';

declare var angularGlobals : any;

var dictionnary = {};
if (angularGlobals.lang == "en") {
    dictionnary = LANG_EN;
} else if (angularGlobals.lang == "fr") {
    dictionnary = LANG_FR;
}

export const LANG = dictionnary;