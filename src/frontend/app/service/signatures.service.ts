import { Injectable } from '@angular/core';

@Injectable()
export class SignaturesContentService {

    userLogged          : any       = {};
    mainDocumentId      : number    = 0;
    signaturesContent   : any[]     = [];
    notesContent        : any[]     = [];
    signaturesList      : any[]     = [];
    currentPage         : number    = 1;
    totalPage           : number    = 1;
    isTaggable          : boolean   = true;
    signWidth           : number    = 200;
    annotationMode      : boolean   = false;
    showSign            : boolean   = false;
    showPad             : boolean   = false;
    showDrawer          : boolean   = false;
    showProfile         : boolean   = false;
    currentAction       : number    = 0;
    loadingSign         : boolean   = true;
    indexDocumentsList  : number    = 0;
    documentsList       : any[]     = [];
    documentsListCount  : any       = {};
    newSign             : any       = {};
    workingAreaWidth    : number    = 0;
    workingAreaHeight   : number    = 0;
    renderingDoc        : boolean   = true;
    scale               : number    = 1;

    reset () {
        this.userLogged         = {};
        this.mainDocumentId     = 0;
        this.signaturesContent  = [];
        this.notesContent       = [];
        this.signaturesList     = [];
        this.currentPage        = 1;
        this.totalPage          = 1;
        this.isTaggable         = true;
        this.signWidth          = 200;
        this.annotationMode     = false;
        this.showSign           = false;
        this.showPad            = false;
        this.showDrawer         = false;
        this.showProfile        = false;
        this.currentAction      = 0;
        this.loadingSign        = true;
        this.indexDocumentsList = 0;
        this.documentsList      = [];
        this.documentsListCount = {};
        this.newSign            = {};
        this.workingAreaWidth   = 0;
        this.workingAreaHeight  = 0;
        this.renderingDoc       = true;
        this.scale              = 1;
    }
}
