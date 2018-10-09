import { ChangeDetectorRef, Component, OnInit, NgZone, ViewChild, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav, MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';

import { AutoCompletePlugin } from '../../../plugins/autocomplete.plugin';
import { SelectionModel } from '@angular/cdk/collections';

declare function $j(selector: any): any;
declare const angularGlobals: any;


@Component({
    templateUrl: "user-administration.component.html",
    styleUrls: ['user-administration.component.css'],
    providers: [NotificationService]
})
export class UserAdministrationComponent extends AutoCompletePlugin implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;


    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;
    dialogRef                       : MatDialogRef<any>;
    config                          : any       = {};
    serialId                        : number;
    userId                          : string;
    mode                            : string    = '';;
    user                            : any       = {};
    _search                         : string    = '';
    creationMode                    : boolean;

    signatureModel                  : any       = {
        base64: "",
        base64ForJs: "",
        name: "",
        type: "",
        size: 0,
        label: "",
    };
    userAbsenceModel                : any[]     = [];
    userList                        : any[]     = [];
    selectedSignature               : number    = -1;
    selectedSignatureLabel          : string    = "";
    data                            : any[]     = [];
    CurrentYear                     : number    = new Date().getFullYear();
    currentMonth                    : number    = new Date().getMonth() + 1;
    minDate                         : Date      = new Date();

    displayedColumns    = ['event_date', 'event_type', 'info', 'remote_ip'];
    dataSource          = new MatTableDataSource(this.data);

    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSource.filter = filterValue;
    }

    //Redirect Baskets
    selectionBaskets = new SelectionModel<Element>(true, []);
    masterToggleBaskets(event: any) {
        if (event.checked) {  
            this.user.baskets.forEach((basket: any) => {
                if (basket.userToDisplay == '') {
                    this.selectionBaskets.select(basket); 
                }      
            });
        } else {
            this.selectionBaskets.clear();
        }
    }


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private router: Router, private zone: NgZone, private notify: NotificationService, public dialog: MatDialog) {
        super(http, ['users']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
        window['angularUserAdministrationComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {

        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].refreshTitle(this.lang.userCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.loading = false;
            } else {
                window['MainHeaderComponent'].refreshTitle(this.lang.userModification);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

                this.creationMode = false;
                this.serialId = params['id'];
                this.http.get(this.coreUrl + "rest/users/" + this.serialId + "/details")
                    .subscribe((data: any) => {
                        this.user = data;
                        this.data = data.history;
                        this.userId = data.user_id;
                        this.minDate = new Date(this.CurrentYear + '-' + this.currentMonth + '-01');
                        this.loading = false;
                        setTimeout(() => {
                            this.dataSource = new MatTableDataSource(this.data);
                            this.dataSource.paginator = this.paginator;
                            this.dataSource.sort = this.sort;
                        }, 0);
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    toogleRedirect(basket: any) {
        $j('#redirectUser_' + basket.group_id + '_' + basket.basket_id).toggle();

        this.http.get(this.coreUrl + 'rest/users')
            .subscribe((data: any) => {
                //this.userList = data['users'];

            }, () => {
                location.href = "index.php";
            });
    }

    initService() {
        if ($j('.jstree-container-ul').length == 0) {
            $j('#jstree').jstree({
                "checkbox": {
                    "three_state": false //no cascade selection
                },
                'core': {
                    'themes': {
                        'name': 'proton',
                        'responsive': true
                    },
                    'data': this.user.allEntities
                },
                "plugins": ["checkbox", "search"]
            });
            $j('#jstree')
                // listen for event
                .on('select_node.jstree', (e: any, data: any) => {
                    if (this.mode == '') {
                        this.addEntity(data.node.id);
                    }  
                }).on('deselect_node.jstree', (e: any, data: any) => {
                    this.deleteEntity(data.node.id);
                })
                // create the instance
                .jstree();

            var to: any = false;
            $j('#jstree_search').keyup(function () {
                if (to) { clearTimeout(to); }
                to = setTimeout(function () {
                    var v = $j('#jstree_search').val();
                    $j('#jstree').jstree(true).search(v);
                }, 250);
            });
        }
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
                window['angularUserAdministrationComponent'].componentAfterUpload(value.target.result);
                this.submitSignature();
            };
        }
    }

    displaySignatureEditionForm(index: number) {
        this.selectedSignature = index;
        this.selectedSignatureLabel = this.user.signatures[index].signature_label;
    }

    resetPassword(user: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.resetPsw);

        if (r) {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/password", {})
                .subscribe((data: any) => {
                    this.notify.success(this.lang.pswReseted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    toggleGroup(group: any) {
        if ($j('#' + group.group_id + '-input').is(':checked') == true) {
            var groupReq = {
                "groupId": group.group_id,
                "role": group.role
            };
            this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/groups", groupReq)
                .subscribe((data: any) => {
                    this.user.groups = data.groups;
                    //this.user.allGroups = data.allGroups;
                    this.user.baskets = data.baskets;
                    this.notify.success(this.lang.groupAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id)
                .subscribe((data: any) => {
                    this.user.groups = data.groups;
                    //this.user.allGroups = data.allGroups;
                    this.user.baskets = data.baskets;
                    this.notify.success(this.lang.groupDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    showActions(basket:any){
        $j('#'+basket.basket_id+'_'+basket.group_id).show();
    }

    hideActions(basket:any){
        $j('#'+basket.basket_id+'_'+basket.group_id).hide();
    }

    updateGroup(group: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id, group)
            .subscribe((data: any) => {
                this.notify.success(this.lang.groupUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    addEntity(entiyId: any) {
        let entity = {
            "entityId"  : entiyId,
            "role"      : ''
        };

        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/entities", entity)
            .subscribe((data: any) => {
                this.user.entities = data.entities;
                this.user.allEntities = data.allEntities;
                this.notify.success(this.lang.entityAdded);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id, entity)
            .subscribe(() => {
                this.notify.success(this.lang.entityUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updatePrimaryEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .subscribe((data: any) => {
                this.user['entities'] = data.entities;
                this.notify.success(this.lang.entityTooglePrimary + ' « ' + entity.entity_id + ' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteEntity(entityId: any) {

        //first check confidential state
        this.http.get(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entityId)
            .subscribe((data: any) => {
                console.log(data);
                if (!data['hasConfidentialityInstances'] && !data['hasListTemplates']) {
                    this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entityId)
                        .subscribe((data: any) => {
                            this.user.entities = data.entities;
                            this.user.allEntities = data.allEntities;
                            this.notify.success(this.lang.entityDeleted);
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                } else {
                    this.config = { data: { hasConfidentialityInstances:data['hasConfidentialityInstances'], hasListTemplates:data['hasListTemplates'] } };
                    this.dialogRef = this.dialog.open(UserAdministrationRedirectModalComponent, this.config);
                    this.dialogRef.afterClosed().subscribe((result: any) => {
                        console.log(result);
                        this.mode = 'delete';
                        if (result) {
                            this.mode = result.processMode;
                            this.http.request('DELETE', this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entityId, {body : {"mode":this.mode,"newUser":result.newUser}})
                            .subscribe((data: any) => {
                                this.user.entities = data.entities;
                                this.user.allEntities = data.allEntities;
                                this.notify.success(this.lang.entityDeleted);
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                        } else {
                            $j('#jstree').jstree('select_node', entityId);
                            this.mode = '';
                        }
                    this.dialogRef = null;
                    });
                }
               
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    submitSignature() {
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/signatures", this.signatureModel)
            .subscribe((data: any) => {
                this.user.signatures = data.signatures;
                this.notify.success(this.lang.signAdded);
                this.signatureModel = {
                    base64: "",
                    base64ForJs: "",
                    name: "",
                    type: "",
                    size: 0,
                    label: "",
                };
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateSignature(selectedSignature: any) {
        var id = this.user.signatures[selectedSignature].id;
        var label = this.user.signatures[selectedSignature].signature_label;

        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id, { "label": label })
            .subscribe((data: any) => {
                this.user.signatures[selectedSignature].signature_label = data.signature.signature_label;
                this.notify.success(this.lang.signUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteSignature(signature: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + signature.signature_label + ' »');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + signature.id)
                .subscribe((data: any) => {
                    this.user.signatures = data.signatures;
                    this.notify.success(this.lang.signDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    test(event:any) {
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
                window['angularUserAdministrationComponent'].componentAfterUpload(value.target.result);
                this.submitSignature();
            };
        }
    }

    addBasketRedirection(newUser:any) {
        let basketsRedirect:any[] = [];
        this.user.baskets.forEach((elem: any) => {
            if (this.selectionBaskets.selected.map((e:any) => { return e.basket_id; }).indexOf(elem.basket_id) != -1 && this.selectionBaskets.selected.map((e:any) => { return e.group_id; }).indexOf(elem.group_id) != -1 && elem.allowed) {
                basketsRedirect.push({newUser: newUser,basketId:elem.basket_id,basketOwner:this.user.user_id,virtual:elem.is_virtual})
            }
        });
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.redirectBasket);

        if (r) {
            this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/redirectedBaskets", basketsRedirect)
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

    reassignBasketRedirection(newUser:any, basket: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.redirectBasket);

        if (r) {
            this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/redirectedBaskets", [{"newUser" : newUser, "basketId":basket.basket_id, "basketOwner":basket.basket_owner, "virtual": basket.is_virtual}])
                .subscribe((data: any) => {
                    this.userCtrl.setValue('');
                    this.user.baskets = data["baskets"];
                    this.notify.success(this.lang.basketUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    delBasketRedirection(basket: any) {
        let r = confirm(this.lang.confirmAction);

        if (r) {
            this.http.request('DELETE', this.coreUrl + "rest/users/" + this.serialId + "/redirectedBaskets/"+ basket.basket_id, {body : {"basketOwner":basket.basket_owner}})
                .subscribe((data: any) => {
                    this.userCtrl.setValue('');
                    this.user.baskets = data["baskets"];
                    this.notify.success(this.lang.basketUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    toggleBasket(state:boolean) {
        let basketsDisable:any = [];
        this.user.baskets.forEach((elem: any) => {
            if (this.selectionBaskets.selected.map((e:any) => { return e.basket_id; }).indexOf(elem.basket_id) != -1 && this.selectionBaskets.selected.map((e:any) => { return e.group_id; }).indexOf(elem.group_id) != -1 && elem.allowed != state) {
                elem.allowed = state;
                basketsDisable.push({"basketId" : elem.basket_id, "groupSerialId":elem.groupSerialId, "allowed":state});
            }
        });
        if (basketsDisable.length > 0) {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/baskets", {"baskets" :basketsDisable})
            .subscribe((data: any) => {
                this.selectionBaskets.clear();
                this.notify.success(this.lang.basketsUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }
        
    }

    activateAbsence() {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/status", { "status": "ABS" })
            .subscribe((data: any) => {
                this.user.status = data.user.status;
                this.userAbsenceModel = [];
                this.notify.success(this.lang.absOn);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    desactivateAbsence() {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/status", { "status": "OK" })
            .subscribe((data: any) => {
                this.user.status = data.user.status;
                this.notify.success(this.lang.absOff);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    onSubmit() {
        if (this.creationMode) {
            var r = true;

            this.http.get(this.coreUrl + "rest/users/" + this.user.userId + "/status")
                .subscribe((data: any) => {
                    var deletedUser = false;
                    if (data.status && data.status == 'DEL') {
                        r = confirm(this.lang.reactivateUserDeleted);
                        deletedUser = true;
                    }
                    if (r) {
                        this.http.post(this.coreUrl + "rest/users", this.user)
                            .subscribe((data: any) => {
                                if (deletedUser) {
                                    this.notify.success(this.lang.userUpdated);
                                } else {
                                    this.notify.success(this.lang.userAdded);
                                }
                                this.router.navigate(["/administration/users/" + data.user.id]);
                            }, (err: any) => {
                                this.notify.error(err.error.errors);
                            });
                    }
                }, () => {
                });
        } else {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId, this.user)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.userUpdated);
                }, (err: any) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    setUserModeLogin(event:any) {
        if (event.checked) {
            this.user.loginmode = "restMode";
        } else {
            this.user.loginmode = "standard";
        }
    }
}

@Component({
    templateUrl: "user-administration-redirect-modal.component.html",
    styles: [".mat-dialog-content{max-height: 65vh;width:600px;}"]
})
export class UserAdministrationRedirectModalComponent extends AutoCompletePlugin {
    lang: any = LANG;

    redirectUser: String = '';
    processMode: String = '';

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<UserAdministrationRedirectModalComponent>) {
        super(http, ['users']);
    }
}
