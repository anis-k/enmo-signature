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
}
