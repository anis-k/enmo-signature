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
    isTaggable = true;
    documentFreeze = false;
    annotationMode = false;
    showSign = false;
    showPad = false;
    showDrawer = false;
    sideNavRigtDatas = {
        mode : '',
        width : '650px',
        locked : true,
    };
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
    smartphoneMode = true;
    mode = '';
    scale = 1;
    x = 0;
    y = 90;
    mainLoading = true;
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
            this.sideNavRigtDatas = {
                mode : 'mainDocumentDetail',
                width : '450px',
                locked : true,
            };
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
        this.isTaggable = true;
        this.annotationMode = false;
        this.showSign = false;
        this.showPad = false;
        this.showDrawer = false;
        this.sideNavRigtDatas = {
            mode : '',
            width : '650px',
            locked : true,
        };
        this.currentAction = 0;
        this.loadingSign = true;
        this.indexDocumentsList = 0;
        this.documentsList = [];
        this.documentsListCount = {};
        this.newSign = {};
        this.workingAreaWidth = 0;
        this.workingAreaHeight = 0;
        this.renderingDoc = true;

        if (window.screen.width <= 360) {
            this.smartphoneMode = true;
        } else {
            this.smartphoneMode = false;
        }
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            this.mobileMode = true;
        } else {
            this.mobileMode = false;
            this.sideNavRigtDatas = {
                mode : 'mainDocumentDetail',
                width : '450px',
                locked : true,
            };
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

    empty(value: any) {
        if (value === null || value === undefined) {
            return true;

        } else if (Array.isArray(value)) {
            if (value.length > 0) {
                return false;
            } else {
                return true;
            }
        } else if (String(value) !== '') {
            return false;
        } else {
            return true;
        }
    }

    detachTemplate(id: string = 'adminMenu') {
        this.portalHost.detach();
    }
}
