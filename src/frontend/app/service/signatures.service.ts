import { Injectable } from '@angular/core';

@Injectable()
export class SignaturesContentService {

    userLogged: any = {};
    mainDocumentId = 0;
    signaturesContent: any[] = [];
    notesContent: any[] = [];
    signaturesList: any[] = [];
    currentPage = 1;
    totalPage = 1;
    isTaggable = true;
    documentFreeze = false;
    signWidth = 200;
    annotationMode = false;
    showSign = false;
    showPad = false;
    showDrawer = false;
    showProfile = false;
    currentAction = 0;
    loadingSign = true;
    indexDocumentsList = 0;
    documentsList: any[] = [];
    documentsListCount: any = {};
    newSign: any = {};
    workingAreaWidth = 0;
    workingAreaHeight = 0;
    renderingDoc = true;
    mobileMode = true;
    mode = 'SIGN';
    scale = 1;
    x = 0;
    y = 0;

    constructor() {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            this.mobileMode = true;
        } else {
            this.mobileMode = false;
        }
    }

    reset() {
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
        this.showSign = false;
        this.showPad = false;
        this.showDrawer = false;
        this.showProfile = false;
        this.currentAction = 0;
        this.loadingSign = true;
        this.indexDocumentsList = 0;
        this.documentsList = [];
        this.documentsListCount = {};
        this.newSign = {};
        this.workingAreaWidth = 0;
        this.workingAreaHeight = 0;
        this.renderingDoc = true;
        this.mobileMode = true;
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            this.mobileMode = true;
        } else {
            this.mobileMode = false;
        }
        this.mode = 'SIGN';
        this.scale = 1;
        this.x = 0;
        this.y = 85;
    }
}
