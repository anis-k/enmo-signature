import { Injectable } from '@angular/core';

@Injectable()
export class SignaturesContentService {
    userLogged: any = {};
    mainDocumentId: Number = 0;
    signaturesContent: any[] = [];
    notesContent: any[] = [];
    signaturesList: any[] = [];
    currentPage = 1;
    totalPage = 1;
    isTaggable = true;
    signWidth = 200;
    annotationMode = false;
    lockNote = false;
    showSign = false;
    showPad = false;
    showDrawer = false;
    showProfile = false;
    currentAction = 0;
    loadingSign = true;
    indexDocumentsList = 0;
    documentsList: any[] = [];
    documentsListCount = 0;
    newSign: any = {};
    workingAreaWidth = 0;
    workingAreaHeight = 0;
    renderingDoc = true;
    scale = 1;

    reset () {
        this.userLogged = {};
        this.mainDocumentId = 0;
        this.signaturesContent = [];
        this.notesContent = [];
        this.signaturesList = [];
        this.currentPage = 1;
        this.totalPage = 1;
        this.isTaggable = true;
        this.signWidth = 200;
        this.annotationMode = false;
        this.lockNote = false;
        this.showSign = false;
        this.showPad = false;
        this.showDrawer = false;
        this.showProfile = false;
        this.currentAction = 0;
        this.loadingSign = true;
        this.indexDocumentsList = 0;
        this.documentsList = [];
        this.documentsListCount = 0;
        this.newSign = {};
        this.workingAreaWidth = 0;
        this.workingAreaHeight = 0;
        this.renderingDoc = true;
        this.scale = 1;
    }
}
