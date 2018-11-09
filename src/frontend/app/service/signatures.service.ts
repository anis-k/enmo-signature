import { Injectable } from '@angular/core';

@Injectable()
export class SignaturesContentService {
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
}
