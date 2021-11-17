import { DomPortalHost, DomPortalOutlet, TemplatePortal } from '@angular/cdk/portal';
import { ApplicationRef, ComponentFactoryResolver, Injectable, Injector, TemplateRef, ViewContainerRef } from '@angular/core';

@Injectable()
export class SignaturesContentService {

    mainDocumentId = 0;

    signaturesContent: any[] = [];
    datesContent: any[] = [];
    notesContent: any[] = [];

    signaturesList: any[] = [];
    signaturesListSubstituted: any[] = [];

    currentPage = 1;
    totalPage = 1;

    stampLock = false;

    currentToobal = 'mainDocumentDetail';

    currentAction = 0;
    indexDocumentsList = 0;
    documentsList: any[] = [];
    documentsListCount: any = {};
    workingAreaWidth = 0;
    workingAreaHeight = 0;
    mobileMode = true;
    smartphoneMode = true;
    mode = '';
    scale = 1;
    x = 0;
    y = 90;
    dragging = false;

    appSession: any;

    private portalHost: DomPortalOutlet;

    constructor(
        private componentFactoryResolver: ComponentFactoryResolver,
        private injector: Injector,
        private appRef: ApplicationRef,
    ) {
        if (window.screen.width <= 360) {
            this.smartphoneMode = true;
        } else {
            this.smartphoneMode = false;
        }
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            this.mobileMode = true;
        } else {
            this.mobileMode = false;
        }
    }

    reset() {
        this.mainDocumentId = 0;
        this.signaturesContent = [];
        this.datesContent = [];
        this.notesContent = [];
        this.signaturesList = [];
        this.signaturesListSubstituted = [];
        this.currentPage = 1;
        this.totalPage = 1;
        this.stampLock = false;
        this.currentAction = 0;
        this.indexDocumentsList = 0;
        this.documentsList = [];
        this.documentsListCount = {};
        this.workingAreaWidth = 0;
        this.workingAreaHeight = 0;
        this.currentToobal = 'mainDocumentDetail';

        if (window.screen.width <= 360) {
            this.smartphoneMode = true;
        } else {
            this.smartphoneMode = false;
        }
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            this.mobileMode = true;
        } else {
            this.mobileMode = false;
        }
        this.mode = '';
        this.scale = 1;
        this.x = 0;
        this.y = 90;
    }

    getAppSession() {
        this.appSession = 'AD098AD9ADA0D9IAXKJ90AKS099S';
    }

    initTemplate(template: TemplateRef<any>, viewContainerRef: ViewContainerRef, id: string = 'adminMenu', mode: string = '') {
        document.getElementById(`${id}`).innerHTML = '';
        // Create a portalHost from a DOM element
        this.portalHost = new DomPortalOutlet(
            document.querySelector(`#${id}`),
            this.componentFactoryResolver,
            this.appRef,
            this.injector
        );
        // Create a template portal
        const templatePortal = new TemplatePortal(
            template,
            viewContainerRef
        );

        // Attach portal to host
        this.portalHost.attach(templatePortal);
    }

    detachTemplate(id: string = 'adminMenu') {
        this.portalHost.detach();
    }
}
