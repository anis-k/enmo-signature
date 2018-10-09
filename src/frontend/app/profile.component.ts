import { ChangeDetectorRef, Component, OnInit, NgZone, ViewChild, QueryList, ViewChildren } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { debounceTime, switchMap, distinctUntilChanged, filter } from 'rxjs/operators';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MatSidenav, MatExpansionPanel } from '@angular/material';

import { AutoCompletePlugin } from '../plugins/autocomplete.plugin';
import { SelectionModel } from '@angular/cdk/collections';
import { FormControl, FormGroup, Validators, AbstractControl, ValidationErrors, ValidatorFn, FormBuilder } from '@angular/forms';

declare function $j(selector: any): any;

declare var tinymce: any;
declare var angularGlobals: any;


@Component({
    templateUrl: "profile.component.html",
    styleUrls: ['profile.component.css'],
    providers: [NotificationService]
})
export class ProfileComponent extends AutoCompletePlugin implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    dialogRef: MatDialogRef<any>;
    mobileMode                      : boolean   = false;
    coreUrl: string;
    lang: any = LANG;

    user: any = {
        baskets: []
    };
    histories: any[] = [];
    passwordModel: any = {
        currentPassword: "",
        newPassword: "",
        reNewPassword: "",
    };
    firstFormGroup: FormGroup;
    ruleText: string = '';
    otherRuleText: string;
    validPassword: Boolean = false;
    matchPassword: Boolean = false;
    hidePassword: Boolean = true;
    passwordRules: any = {
        minLength: { enabled: false, value: 0 },
        complexityUpper: { enabled: false, value: 0 },
        complexityNumber: { enabled: false, value: 0 },
        complexitySpecial: { enabled: false, value: 0 },
        renewal: { enabled: false, value: 0 },
        historyLastUse: {enabled:false, value:0},
    };
    signatureModel: any = {
        base64: "",
        base64ForJs: "",
        name: "",
        type: "",
        size: 0,
        label: "",
    };
    mailSignatureModel: any = {
        selected: -1,
        htmlBody: "",
        title: "",
    };
    userAbsenceModel: any[] = [];
    basketsToRedirect: string[] = [];

    showPassword: boolean = false;
    selectedSignature: number = -1;
    selectedSignatureLabel: string = "";
    loading: boolean = false;
    selectedIndex: number = 0;
    selectedIndexContactsGrp: number = 0;

    @ViewChild('snav2') sidenavRight: MatSidenav;
    @ViewChild('snav') sidenavLeft: MatSidenav;

    //Redirect Baskets
    selectionBaskets = new SelectionModel<Element>(true, []);
    myBasketExpansionPanel: boolean = false;
    masterToggleBaskets(event: any) {
        if (event.checked) {  
            this.user.baskets.forEach((basket: any) => {
                this.selectionBaskets.select(basket);   
            });
        } else {
            this.selectionBaskets.clear();
        }
    }

    @ViewChildren(MatExpansionPanel) viewPanels: QueryList<MatExpansionPanel>;

    //Groups contacts
    contactsGroups: any[] = [];
    displayedColumnsGroupsList: string[] = ['label', 'description','nbContacts','public', 'actions'];
    dataSourceGroupsList: any;
    @ViewChild('paginatorGroupsList') paginatorGroupsList: MatPaginator;
    @ViewChild('tableGroupsListSort') sortGroupsList: MatSort;
    applyFilterGroupsList(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSourceGroupsList.filter = filterValue;
    }

    //Group contacts
    contactsGroup: any = { public: false };

    //Group contacts List Autocomplete
    contactTypeSearch: string = "all";
    initAutoCompleteContact = true;

    searchTerm: FormControl = new FormControl();
    searchResult: any = [];
    contactTypes: string[] = [];
    displayedColumnsContactsListAutocomplete: string[] = ['select', 'contact', 'address'];
    dataSourceContactsListAutocomplete: any;
    @ViewChild('paginatorGroupsListAutocomplete') paginatorGroupsListAutocomplete: MatPaginator;
    selection = new SelectionModel<Element>(true, []);
    masterToggle(event: any) {
        if (event.checked) {
            this.dataSourceContactsListAutocomplete.data.forEach((row: any) => {
                if (!$j("#check_" + row.addressId + '-input').is(":disabled")) {
                    this.selection.select(row.addressId);
                }
            });
        } else {
            this.selection.clear();
        }
    }


    //Group contacts List
    contactsListMode: boolean = false;
    contactsList: any[] = [];
    displayedColumnsContactsList: string[] = ['contact', 'address', 'actions'];
    dataSourceContactsList: any;
    @ViewChild('paginatorContactsList') paginatorContactsList: MatPaginator;
    @ViewChild('tableContactsListSort') sortContactsList: MatSort;
    applyFilterContactsList(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSourceContactsList.filter = filterValue;
    }



    //History
    displayedColumns = ['event_date', 'info'];
    dataSource: any;
    @ViewChild('paginatorHistory') paginatorHistory: MatPaginator;
    @ViewChild('tableHistorySort') sortHistory: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private zone: NgZone, private notify: NotificationService, public dialog: MatDialog, private _formBuilder: FormBuilder) {
        super(http, ['users']);
        this.mobileMode = angularGlobals.mobileMode;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
        window['angularProfileComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
        this.searchTerm.valueChanges.pipe(
            debounceTime(500),
            filter(value => value.length > 2),
            distinctUntilChanged(),
            switchMap(data => this.http.get(this.coreUrl + 'rest/autocomplete/contacts', { params: { "search": data, "type": this.contactTypeSearch } }))
        ).subscribe((response: any) => {
            this.searchResult = response;
            this.dataSourceContactsListAutocomplete = new MatTableDataSource(this.searchResult);
            this.dataSourceContactsListAutocomplete.paginator = this.paginatorGroupsListAutocomplete;
            //this.dataSource.sort      = this.sortContactList;
        });
    }

    initComponents(event: any) {
        this.selectedIndex = event.index;
        if (event.index == 2) {
            if (!this.mobileMode) {
                this.sidenavRight.open();
            } 
            //if (this.histories.length == 0) {
                this.http.get(this.coreUrl + 'rest/histories/users/' + this.user.id)
                    .subscribe((data: any) => {
                        this.histories = data.histories;
                        setTimeout(() => {
                            this.dataSource = new MatTableDataSource(this.histories);
                            this.dataSource.paginator = this.paginatorHistory;
                            this.dataSource.sort = this.sortHistory;
                        }, 0);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            //}
        } else if(event.index == 1) {
            this.sidenavRight.close();
        } else if(!this.mobileMode){
            this.sidenavRight.open();
        }
    }

    initMce() {
        tinymce.remove('textarea');
        //LOAD EDITOR TINYMCE for MAIL SIGN
        tinymce.baseURL = "../../node_modules/tinymce";
        tinymce.suffix = '.min';
        tinymce.init({
            selector: "textarea#emailSignature",
            statusbar: false,
            language: "fr_FR",
            language_url: "tools/tinymce/langs/fr_FR.js",
            height: "200",
            plugins: [
                "textcolor"
            ],
            external_plugins: {
                'bdesk_photo': "../../apps/maarch_entreprise/tools/tinymce/bdesk_photo/plugin.min.js"
            },
            menubar: false,
            toolbar: "undo | bold italic underline | alignleft aligncenter alignright | bdesk_photo | forecolor",
            theme_buttons1_add: "fontselect,fontsizeselect",
            theme_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
            theme_buttons2_add: "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
            theme_buttons3_add_before: "tablecontrols,separator",
            theme_buttons3_add: "separator,print,separator,ltr,rtl,separator,fullscreen,separator,insertlayer,moveforward,movebackward,absolut",
            theme_toolbar_align: "left",
            theme_advanced_toolbar_location: "top",
            theme_styles: "Header 1=header1;Header 2=header2;Header 3=header3;Table Row=tableRow1"

        });
    }

    initGroupsContact() {
        this.contactsListMode = false;
        this.selectedIndexContactsGrp = 0;
        this.http.get(this.coreUrl + 'rest/contactsGroups')
            .subscribe((data) => {
                this.contactsGroups = [];
                this.contactsGroup = { public: false, contacts:[] };
                let i = 0;
                data['contactsGroups'].forEach((ct: any) => {
                    if (ct.owner == angularGlobals.user.id) {
                        ct.position = i;
                        this.contactsGroups.push(ct);
                        i++;
                    }
                });
                setTimeout(() => {
                    this.dataSourceGroupsList = new MatTableDataSource(this.contactsGroups);
                    this.dataSourceGroupsList.paginator = this.paginatorGroupsList;
                    this.dataSourceGroupsList.sort = this.sortGroupsList;
                }, 0);
            }, (err) => {
                console.log(err);
                location.href = "index.php";
            });
    }

    contactsGroupSubmit() {
        this.http.post(this.coreUrl + 'rest/contactsGroups', this.contactsGroup)
            .subscribe((data: any) => {
                this.initGroupsContact();
                //this.toggleAddGrp();
                this.notify.success(this.lang.contactsGroupAdded);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateGroupSubmit() {
        this.http.put(this.coreUrl + 'rest/contactsGroups/' + this.contactsGroup.id, this.contactsGroup)
            .subscribe(() => {
                this.notify.success(this.lang.contactsGroupUpdated);
                this.initGroupsContact();    
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteContactsGroup(row: any) {
        var contactsGroup = this.contactsGroups[row];
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + contactsGroup.label + ' »');
        if (r) {
            this.http.delete(this.coreUrl + 'rest/contactsGroups/' + contactsGroup.id)
                .subscribe(() => {
                    this.contactsListMode = false;
                    var lastElement = this.contactsGroups.length - 1;
                    this.contactsGroups[row] = this.contactsGroups[lastElement];
                    this.contactsGroups[row].position = row;
                    this.contactsGroups.splice(lastElement, 1);

                    this.dataSourceGroupsList = new MatTableDataSource(this.contactsGroups);
                    this.dataSourceGroupsList.paginator = this.paginatorGroupsList;
                    this.notify.success(this.lang.contactsGroupDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    loadContactsGroup(contactsGroup: any) {
        this.contactsListMode = true;
        this.http.get(this.coreUrl + 'rest/contactsTypes')
            .subscribe((data: any) => {
                this.contactTypes = data.contactsTypes;
            });

        this.http.get(this.coreUrl + 'rest/contactsGroups/' + contactsGroup.id)
            .subscribe((data: any) => {
                this.contactsGroup = data.contactsGroup;
                setTimeout(() => {
                    this.dataSourceContactsList = new MatTableDataSource(this.contactsGroup.contacts);
                    this.dataSourceContactsList.paginator = this.paginatorContactsList;
                    this.dataSourceContactsList.sort = this.sortContactsList;
                    this.selectedIndexContactsGrp = 1;
                }, 0);
            });
    }

    saveContactsList(elem: any): void {
        elem.textContent = this.lang.loading + '...';
        elem.disabled = true;
        this.http.post(this.coreUrl + 'rest/contactsGroups/' + this.contactsGroup.id + '/contacts', { 'contacts': this.selection.selected })
            .subscribe((data: any) => {
                this.notify.success(this.lang.contactAdded);
                this.selection.clear();
                elem.textContent = this.lang.add;
                this.contactsGroup = data.contactsGroup;
                setTimeout(() => {
                    this.dataSourceContactsList = new MatTableDataSource(this.contactsGroup.contacts);
                    this.dataSourceContactsList.paginator = this.paginatorContactsList;
                    this.dataSourceContactsList.sort = this.sortContactsList;
                }, 0);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    preDelete(index: number) {
        let r = confirm(this.lang.reallyWantToDeleteContactFromGroup);

        if (r) {
            this.removeContact(this.contactsGroup.contacts[index], index);
        }
    }

    removeContact(contact: any, row: any) {
        this.http.delete(this.coreUrl + "rest/contactsGroups/" + this.contactsGroup.id + "/contacts/" + contact['addressId'])
            .subscribe(() => {
                var lastElement = this.contactsGroup.contacts.length - 1;
                this.contactsGroup.contacts[row] = this.contactsGroup.contacts[lastElement];
                this.contactsGroup.contacts[row].position = row;
                this.contactsGroup.contacts.splice(lastElement, 1);

                this.dataSourceContactsList = new MatTableDataSource(this.contactsGroup.contacts);
                this.dataSourceContactsList.paginator = this.paginatorContactsList;
                this.dataSourceContactsList.sort = this.sortContactsList;
                this.notify.success(this.lang.contactDeletedFromGroup);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    launchLoading() {
        if (this.searchTerm.value.length > 2) {
            this.dataSourceContactsListAutocomplete = null;
            this.initAutoCompleteContact = false;
        }
    }

    isInGrp(address: any): boolean {
        let isInGrp = false;
        this.contactsGroup.contacts.forEach((row: any) => {
            if (row.addressId == address.addressId) {
                isInGrp = true;
            }
        });
        return isInGrp;
    }

    selectAddress(addressId: any) {
        if (!$j("#check_" + addressId + '-input').is(":disabled")) {
            this.selection.toggle(addressId);
        }
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.myProfile);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(this.sidenavRight);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get('../../rest/currentUser/profile')
            .subscribe((data: any) => {
                this.user = data;

                this.user.baskets.forEach((value: any, index: number) => {
                    this.user.baskets[index]['disabled'] = false;
                    this.user.redirectedBaskets.forEach((value2: any) => {
                        if (value.basket_id == value2.basket_id && value.basket_owner == value2.basket_owner) {
                            this.user.baskets[index]['disabled'] = true;
                        }
                    });
                });
                this.loading = false;
            });
    }

    processAfterUpload(b64Content: any) {
        this.zone.run(() => this.resfreshUpload(b64Content));
    }

    resfreshUpload(b64Content: any) {
        if (this.signatureModel.size <= 2000000) {
            this.signatureModel.base64 = b64Content.replace(/^data:.*?;base64,/, "");
            this.signatureModel.base64ForJs = b64Content;
        } else {
            this.signatureModel.name = "";
            this.signatureModel.size = 0;
            this.signatureModel.type = "";
            this.signatureModel.base64 = "";
            this.signatureModel.base64ForJs = "";

            this.notify.error("Taille maximum de fichier dépassée (2 MB)");
        }
    }

    displayPassword() {
        this.showPassword = !this.showPassword;
    }

    clickOnUploader(id: string) {
        $j('#' + id).click();
    }

    uploadSignatureTrigger(fileInput: any) {
        if (fileInput.target.files && fileInput.target.files[0]) {
            var reader = new FileReader();

            this.signatureModel.name = fileInput.target.files[0].name;
            this.signatureModel.size = fileInput.target.files[0].size;
            this.signatureModel.type = fileInput.target.files[0].type;
            if (this.signatureModel.label == "") {
                this.signatureModel.label = this.signatureModel.name;
            }

            reader.readAsDataURL(fileInput.target.files[0]);

            reader.onload = (value: any) => {
                window['angularProfileComponent'].componentAfterUpload(value.target.result);
                this.submitSignature();
            };

        }
    }

    dndUploadSignature(event:any) {
        if (event.mouseEvent.dataTransfer.files && event.mouseEvent.dataTransfer.files[0]) {
            var reader = new FileReader();

            this.signatureModel.name = event.mouseEvent.dataTransfer.files[0].name;
            this.signatureModel.size = event.mouseEvent.dataTransfer.files[0].size;
            this.signatureModel.type = event.mouseEvent.dataTransfer.files[0].type;
            if (this.signatureModel.label == "") {
                this.signatureModel.label = this.signatureModel.name;
            }

            reader.readAsDataURL(event.mouseEvent.dataTransfer.files[0]);

            reader.onload = (value: any) => {
                window['angularProfileComponent'].componentAfterUpload(value.target.result);
                this.submitSignature();
            };
        }
    }

    displaySignatureEditionForm(index: number) {
        this.selectedSignature = index;
        this.selectedSignatureLabel = this.user.signatures[index].signature_label;
    }

    changeEmailSignature(i: any) {
        this.mailSignatureModel.selected = i;

        tinymce.get('emailSignature').setContent(this.user.emailSignatures[i].html_body);
        this.mailSignatureModel.title = this.user.emailSignatures[i].title;
    }

    resetEmailSignature() {
        this.mailSignatureModel.selected = -1;

        tinymce.get('emailSignature').setContent("");
        this.mailSignatureModel.title = "";

    }

    addBasketRedirection(newUser: any) {
        let basketsRedirect:any[] = [];
        this.user.baskets.forEach((elem: any) => {
            if (this.selectionBaskets.selected.map((e:any) => { return e.basket_id; }).indexOf(elem.basket_id) != -1 && this.selectionBaskets.selected.map((e:any) => { return e.group_id; }).indexOf(elem.group_id) != -1) {
                basketsRedirect.push({newUser: newUser,basketId:elem.basket_id,basketOwner:this.user.user_id,virtual:elem.is_virtual})
            }
        });
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.redirectBasket);

        if (r) {
            this.http.post(this.coreUrl + "rest/users/" + this.user.id + "/redirectedBaskets", basketsRedirect)
                .subscribe((data: any) => {
                    this.userCtrl.setValue('');
                    this.user.baskets = data["baskets"];
                    this.selectionBaskets.clear();
                    this.notify.success(this.lang.basketUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    delBasketRedirection(basket: any) {
        let r = confirm(this.lang.confirmAction);

        if (r) {
            this.http.request('DELETE', this.coreUrl + "rest/users/" + this.user.id + "/redirectedBaskets/" + basket.basket_id, { body: { "basketOwner": basket.basket_owner } })
                .subscribe((data: any) => {
                    this.userCtrl.setValue('');
                    this.user.baskets = data["baskets"];
                    this.notify.success(this.lang.basketUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    reassignBasketRedirection(newUser: any, basket: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.redirectBasket);

        if (r) {
            this.http.post(this.coreUrl + "rest/users/" + this.user.id + "/redirectedBaskets", [{ "newUser": newUser, "basketId": basket.basket_id, "basketOwner": basket.basket_owner, "virtual": basket.is_virtual }])
                .subscribe((data: any) => {
                    this.userCtrl.setValue('');
                    this.user.baskets = data["baskets"];
                    this.notify.success(this.lang.basketUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    updateBasketColor(i: number, y: number) {
        this.http.put(this.coreUrl + "rest/currentUser/groups/" + this.user.regroupedBaskets[i].groupSerialId + "/baskets/" + this.user.regroupedBaskets[i].baskets[y].basket_id, { "color": this.user.regroupedBaskets[i].baskets[y].color })
            .subscribe((data: any) => {
                this.user.regroupedBaskets = data.userBaskets;
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    activateAbsence() {
        let r = confirm(this.lang.confirmToBeAbsent);

        if (r) {
            this.http.put(this.coreUrl + 'rest/users/' + this.user.id + '/status', { "status": "ABS" })
                .subscribe(() => {
                    location.hash = "";
                    location.search = "?display=true&page=logout&abs_mode";
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    askRedirectBasket() {
        this.myBasketExpansionPanel = false;
        this.viewPanels.forEach(p => p.close());
        let r = confirm(this.lang.askRedirectBasketBeforeAbsence);
        if (r) {
            this.selectedIndex = 1;
            setTimeout(() => {
                this.myBasketExpansionPanel = true;
            }, 0);
        } else {
            this.activateAbsence();
        }
    }

    updatePassword() {
        this.passwordModel.currentPassword = this.firstFormGroup.controls['currentPasswordCtrl'].value;
        this.passwordModel.newPassword = this.firstFormGroup.controls['newPasswordCtrl'].value;
        this.passwordModel.reNewPassword = this.firstFormGroup.controls['retypePasswordCtrl'].value;
        this.http.put(this.coreUrl + 'rest/currentUser/password', this.passwordModel)
            .subscribe((data: any) => {
                this.showPassword = false;
                this.passwordModel = {
                    currentPassword: "",
                    newPassword: "",
                    reNewPassword: "",
                };
                this.notify.success(this.lang.passwordUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    submitEmailSignature() {
        this.mailSignatureModel.htmlBody = tinymce.get('emailSignature').getContent();

        this.http.post(this.coreUrl + 'rest/currentUser/emailSignature', this.mailSignatureModel)
            .subscribe((data: any) => {
                if (data.errors) {
                    this.notify.error(data.errors);
                } else {
                    this.user.emailSignatures = data.emailSignatures;
                    this.mailSignatureModel = {
                        selected: -1,
                        htmlBody: "",
                        title: "",
                    };
                    tinymce.get('emailSignature').setContent("");
                    this.notify.success(this.lang.emailSignatureAdded);
                }
            });
    }

    updateEmailSignature() {
        this.mailSignatureModel.htmlBody = tinymce.get('emailSignature').getContent();
        var id = this.user.emailSignatures[this.mailSignatureModel.selected].id;

        this.http.put(this.coreUrl + 'rest/currentUser/emailSignature/' + id, this.mailSignatureModel)
            .subscribe((data: any) => {
                if (data.errors) {
                    this.notify.error(data.errors);
                } else {
                    this.user.emailSignatures[this.mailSignatureModel.selected].title = data.emailSignature.title;
                    this.user.emailSignatures[this.mailSignatureModel.selected].html_body = data.emailSignature.html_body;
                    this.notify.success(this.lang.emailSignatureUpdated);
                }
            });
    }

    deleteEmailSignature() {
        let r = confirm(this.lang.confirmDeleteMailSignature);

        if (r) {
            var id = this.user.emailSignatures[this.mailSignatureModel.selected].id;

            this.http.delete(this.coreUrl + 'rest/currentUser/emailSignature/' + id)
                .subscribe((data: any) => {
                    if (data.errors) {
                        this.notify.error(data.errors);
                    } else {
                        this.user.emailSignatures = data.emailSignatures;
                        this.mailSignatureModel = {
                            selected: -1,
                            htmlBody: "",
                            title: "",
                        };
                        tinymce.get('emailSignature').setContent("");
                        this.notify.success(this.lang.emailSignatureDeleted);
                    }
                });
        }
    }

    submitSignature() {
        this.http.post(this.coreUrl + "rest/users/" + this.user.id + "/signatures", this.signatureModel)
            .subscribe((data: any) => {
                this.user.signatures = data.signatures;
                this.signatureModel = {
                    base64: "",
                    base64ForJs: "",
                    name: "",
                    type: "",
                    size: 0,
                    label: "",
                };
                this.notify.success(this.lang.signatureAdded);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateSignature(signature: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.user.id + "/signatures/" + signature.id, { "label": signature.signature_label })
            .subscribe((data: any) => {
                this.notify.success(this.lang.signatureUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteSignature(id: number) {
        let r = confirm(this.lang.confirmDeleteSignature);

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.user.id + "/signatures/" + id)
                .subscribe((data: any) => {
                    this.user.signatures = data.signatures;
                    this.notify.success(this.lang.signatureDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/currentUser/profile', this.user)
            .subscribe(() => {
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    regexValidator(regex: RegExp, error: ValidationErrors): ValidatorFn {
        return (control: AbstractControl): { [key: string]: any } => {
            if (!control.value) {
                return null;
            }
            const valid = regex.test(control.value);
            return valid ? null : error;
        };
    }

    changePasswd() {
        this.http.get(this.coreUrl + 'rest/passwordRules')
            .subscribe((data: any) => {
                let valArr : ValidatorFn[] = [];
                let ruleTextArr: String[] = [];
                let otherRuleTextArr: String[] = [];

                valArr.push(Validators.required);

                data.rules.forEach((rule: any) => {
                    if (rule.label == 'minLength') {
                        this.passwordRules.minLength.enabled = rule.enabled;
                        this.passwordRules.minLength.value = rule.value;   
                        if (rule.enabled) {
                            valArr.push(Validators.minLength(this.passwordRules.minLength.value));
                            ruleTextArr.push(rule.value + ' ' + this.lang['password' + rule.label]);
                        }
                        

                    } else if (rule.label == 'complexityUpper') {
                        this.passwordRules.complexityUpper.enabled = rule.enabled;
                        this.passwordRules.complexityUpper.value = rule.value;
                        if (rule.enabled) {
                            valArr.push(this.regexValidator(new RegExp('[A-Z]'), { 'complexityUpper': '' }));
                            ruleTextArr.push(this.lang['password' + rule.label]);
                        }
                        

                    } else if (rule.label == 'complexityNumber') {
                        this.passwordRules.complexityNumber.enabled = rule.enabled;
                        this.passwordRules.complexityNumber.value = rule.value;
                        if (rule.enabled) {
                            valArr.push(this.regexValidator(new RegExp('[0-9]'), { 'complexityNumber': '' }));
                            ruleTextArr.push(this.lang['password' + rule.label]);
                        }
                        

                    } else if (rule.label == 'complexitySpecial') {
                        this.passwordRules.complexitySpecial.enabled = rule.enabled;
                        this.passwordRules.complexitySpecial.value = rule.value;  
                        if (rule.enabled) {
                            valArr.push(this.regexValidator(new RegExp('[^A-Za-z0-9]'), { 'complexitySpecial': '' }));
                            ruleTextArr.push(this.lang['password' + rule.label]);
                        }
                    } else if (rule.label == 'renewal') {
                        this.passwordRules.renewal.enabled = rule.enabled;
                        this.passwordRules.renewal.value = rule.value;
                        if (rule.enabled) {
                            otherRuleTextArr.push(this.lang['password' + rule.label] + ' <b>' + rule.value + ' ' + this.lang.days + '</b>. ' + this.lang['password2' + rule.label]+'.');
                        }
                    } else if (rule.label == 'historyLastUse') {
                        this.passwordRules.historyLastUse.enabled = rule.enabled;
                        this.passwordRules.historyLastUse.value = rule.value
                        if (rule.enabled) {
                            otherRuleTextArr.push(this.lang['passwordhistoryLastUseDesc'] + ' <b>' + rule.value + '</b> ' + this.lang['passwordhistoryLastUseDesc2']+'.');
                        }
                    }

                });
                this.ruleText = ruleTextArr.join(', ');
                this.otherRuleText = otherRuleTextArr.join('<br/>');
                this.firstFormGroup.controls["newPasswordCtrl"].setValidators(valArr);
            }, (err) => {
                this.notify.error(err.error.errors);
            });

            this.firstFormGroup = this._formBuilder.group({
                newPasswordCtrl: [
                    ''
                ],
                retypePasswordCtrl: [
                    '',
                    Validators.compose([Validators.required])
                ],
                currentPasswordCtrl: [
                    '',
                    Validators.compose([Validators.required])
                ]
    
            }, {
                    validator: this.matchValidator
                });
        this.validPassword =false;
        this.firstFormGroup.controls['currentPasswordCtrl'].setErrors(null)
        this.firstFormGroup.controls['newPasswordCtrl'].setErrors(null)
        this.firstFormGroup.controls['retypePasswordCtrl'].setErrors(null)
        this.selectedIndex = 0;
        this.showPassword = true;
    }

    matchValidator(group: FormGroup) {

        if (group.controls['newPasswordCtrl'].value == group.controls['retypePasswordCtrl'].value) {
            return false;
        } else {
            group.controls['retypePasswordCtrl'].setErrors({'mismatch': true})
            return {'mismatch': true};
        }
    }

    getErrorMessage() {
        if (this.firstFormGroup.controls['newPasswordCtrl'].value != this.firstFormGroup.controls['retypePasswordCtrl'].value) {
            this.firstFormGroup.controls['retypePasswordCtrl'].setErrors({'mismatch': true});
        } else {
            this.firstFormGroup.controls['retypePasswordCtrl'].setErrors(null);
        }
        if (this.firstFormGroup.controls['newPasswordCtrl'].hasError('required')) {
            return this.lang.requiredField + ' !';

        } else if (this.firstFormGroup.controls['newPasswordCtrl'].hasError('minlength') && this.passwordRules.minLength.enabled) {
            return this.passwordRules.minLength.value + ' ' + this.lang.passwordminLength + ' !';

        } else if (this.firstFormGroup.controls['newPasswordCtrl'].errors != null && this.firstFormGroup.controls['newPasswordCtrl'].errors.complexityUpper !== undefined && this.passwordRules.complexityUpper.enabled) {
            return this.lang.passwordcomplexityUpper + ' !';

        } else if (this.firstFormGroup.controls['newPasswordCtrl'].errors != null && this.firstFormGroup.controls['newPasswordCtrl'].errors.complexityNumber !== undefined && this.passwordRules.complexityNumber.enabled) {
            return this.lang.passwordcomplexityNumber + ' !';

        } else if (this.firstFormGroup.controls['newPasswordCtrl'].errors != null && this.firstFormGroup.controls['newPasswordCtrl'].errors.complexitySpecial !== undefined && this.passwordRules.complexitySpecial.enabled) {
            return this.lang.passwordcomplexitySpecial + ' !';

        } else {
            this.firstFormGroup.controls['newPasswordCtrl'].setErrors(null)
            this.validPassword = true;
            return '';
        }
    }

    showActions(basket:any){
        $j('#'+basket.basket_id+'_'+basket.group_id).show();
    }

    hideActions(basket:any){
        $j('#'+basket.basket_id+'_'+basket.group_id).hide();
    }

    toggleAddGrp() {
        this.initGroupsContact();
        $j('#contactsGroupFormUp').toggle();
        $j('#contactsGroupList').toggle();
    }
    toggleAddContactGrp() {
        $j('#contactsGroupFormAdd').toggle();
        //$j('#contactsGroup').toggle();
    }

    changeTabContactGrp(event:any) {
        this.selectedIndexContactsGrp = event;
        if (event == 0) {
            this.initGroupsContact();
        }
    }
}
