import { ChangeDetectorRef, Component, OnInit, ViewChild, Inject } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { ConfirmModalComponent } from '../../confirmModal.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav, MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material';

import { AutoCompletePlugin } from '../../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;

@Component({
    templateUrl: "entities-administration.component.html",
    styleUrls: ['entities-administration.component.css'],
    providers: [NotificationService]
})
export class EntitiesAdministrationComponent extends AutoCompletePlugin implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;
    dialogRef                       : MatDialogRef<any>;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    entities                        : any[]     = [];
    listTemplateRoles               : any[]     = [];
    entityTypeList                  : any[]     = [];
    currentEntity                   : any       = {};
    isDraggable                     : boolean   = true;
    newEntity                       : boolean   = false;
    creationMode                    : boolean   = false;
    listDiffModified                : boolean   = false;
    idCircuitVisa                   : number;
    config                          : any       = {};

    dataSource          = new MatTableDataSource(this.currentEntity.users);
    displayedColumns    = ['firstname', 'lastname'];


    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        super(http, ['adminUsers', 'usersAndEntities', 'visaUsers']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.entities);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;
        this.http.get(this.coreUrl + "rest/entityTypes")
            .subscribe((data: any) => {
                this.entityTypeList = data['types'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        this.http.get(this.coreUrl + "rest/listTemplates/types/entity_id/roles")
            .subscribe((data: any) => {
                this.listTemplateRoles = data['roles'];
            }, (err) => {
                this.notify.error(err.error.errors);
            });

        this.http.get(this.coreUrl + "rest/entities")
            .subscribe((data: any) => {
                this.entities = data['entities'];
                this.loading = false;

                setTimeout(() => {
                    $j('#jstree').jstree({
                        "checkbox": {
                            'deselect_all': true,
                            "three_state": false //no cascade selection
                        },
                        'core': {
                            'themes': {
                                'name': 'proton',
                                'responsive': true
                            },
                            'multiple': false,
                            'data': this.entities,
                            "check_callback": function (operation: any, node: any, node_parent: any, node_position: any, more: any) {
                                if (operation == 'move_node') {
                                    if (node_parent.id == '#') {
                                        return false;
                                    } else if (!node_parent.original.allowed) {
                                        return false;
                                    } else {
                                        return true;
                                    }
                                }
                            }
                        },
                        "dnd": {
                            is_draggable: function (nodes: any) {
                                var i = 0;
                                var j = nodes.length;
                                for (; i < j; i++) {
                                    if (!nodes[i].original.allowed) {
                                        return false;
                                    }
                                }
                                return true;
                            }
                        },
                        "plugins": ["checkbox", "search", "dnd", "sort"]
                    });
                    $j('#jstree').jstree('select_node', this.entities[0]);
                    var to: any = false;
                    $j('#jstree_search').keyup(function () {
                        if (to) { clearTimeout(to); }
                        to = setTimeout(function () {
                            var v = $j('#jstree_search').val();
                            $j('#jstree').jstree(true).search(v);
                        }, 250);
                    });
                    $j('#jstree')
                        // listen for event
                        .on('select_node.jstree', (e: any, data: any) => {
                            if (this.sidenavRight.opened == false) {
                                this.sidenavRight.open();
                            }
                            if (this.creationMode == true) {
                                this.currentEntity.parent_entity_id = data.node.id;
                            } else {
                                if (this.newEntity == true) {
                                    this.loadEntity(this.currentEntity.entity_id);
                                    this.newEntity = false;
                                } else {
                                    this.loadEntity(data.node.id);
                                }
                            }

                        }).on('deselect_node.jstree', (e: any, data: any) => {

                            this.sidenavRight.close();

                        }).on('move_node.jstree', (e: any, data: any) => {


                            if (this.currentEntity.parent_entity_id != this.currentEntity.entity_id) {
                                this.currentEntity.parent_entity_id = data.parent;
                            }
                            this.moveEntity();
                        })
                        // create the instance
                        .jstree();

                    $j(document).on('dnd_start.vakata', (e: any, data: any) => {
                        $j('#jstree').jstree('deselect_all');
                        $j('#jstree').jstree('select_node', data.data.nodes[0]);
                    });
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    loadEntity(entity_id: any) {
        this.listDiffModified = false;
        this.http.get(this.coreUrl + "rest/entities/" + entity_id + '/details')
            .subscribe((data: any) => {
                this.currentEntity = data['entity'];
                if (this.currentEntity.visaTemplate[0]) {
                    this.idCircuitVisa = this.currentEntity.visaTemplate[0].id;
                } else {
                    this.idCircuitVisa = null;
                }
                this.dataSource = new MatTableDataSource(this.currentEntity.users);
                this.dataSource.paginator = this.paginator;
                this.dataSource.sort = this.sort;
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    addElemListModel(element: any) {
        this.listDiffModified = true;
        var inListModel = false;
        var newElemListModel: any = {};

        this.listTemplateRoles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate[role.id]) {
                    this.currentEntity.listTemplate[role.id].forEach((listModel: any) => {
                        if (listModel.item_id == element.id) {
                            inListModel = true;
                        }
                    });
                }
            }
        });
        if (!inListModel) {
            if (this.currentEntity.listTemplate.dest.length == 0 && element.type == 'user') {
                newElemListModel = {
                    "item_type": "user_id",
                    "item_id": element.id,
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo,
                    "item_mode": "dest",
                    "sequence": 0
                };
                this.currentEntity.listTemplate.dest.unshift(newElemListModel);

            } else {
                var itemType = '';
                if (element.type == 'user') {
                    itemType = 'user_id';
                } else {
                    itemType = 'entity_id';
                }

                newElemListModel = {
                    "item_type": itemType,
                    "item_id": element.id,
                    "labelToDisplay": element.idToDisplay,
                    "descriptionToDisplay": element.otherInfo,
                    "item_mode": "cc",
                    "sequence": 0
                };
                this.currentEntity.listTemplate.cc.unshift(newElemListModel);
            }
        }
        this.elementCtrl.setValue('');
        $j('.autocompleteSearch').blur();
    }

    addElemListModelVisa(element: any) {
        this.listDiffModified = true;
        let newElemListModel = {
            "id": '',
            "item_type": 'user_id',
            "item_mode": "sign",
            "item_id": element.id,
            "sequence": this.currentEntity.visaTemplate.length,
            "idToDisplay": element.idToDisplay,
            "descriptionToDisplay": element.otherInfo
        };

        this.currentEntity.visaTemplate.push(newElemListModel);
        if (this.currentEntity.visaTemplate.length > 1) {
            this.currentEntity.visaTemplate[this.currentEntity.visaTemplate.length-2].item_mode = 'visa';
        }
        this.visaUserCtrl.setValue('');
        $j('.autocompleteSearch').blur();
    }

    saveEntity() {
        if (this.currentEntity.parent_entity_id == '#') {
            this.currentEntity.parent_entity_id = '';
        }

        var r = true;
        if (this.currentEntity.parent_entity_id == '') {
            r = confirm(this.lang.entityWithoutParentMessage);
        }

        if (r) {
            if (this.creationMode) {
                this.http.post(this.coreUrl + "rest/entities", this.currentEntity)
                    .subscribe((data: any) => {
                        this.currentEntity.listTemplate = [];
                        this.entities = data['entities'];
                        this.creationMode = false;
                        this.newEntity = true;
                        $j('#jstree').jstree(true).settings.core.data = this.entities;
                        $j('#jstree').jstree(true).settings.select_node = this.currentEntity;
                        $j('#jstree').jstree(true).refresh();
                        $j('#jstree').on("refresh.jstree", (e:any) => {
                            $j('#jstree').jstree('deselect_all');
                            $j('#jstree').jstree('select_node', this.currentEntity.entity_id);
                          });
                        this.notify.success(this.lang.entityAdded);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            } else {
                this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
                    .subscribe((data: any) => {
                        this.entities = data['entities'];
                        $j('#jstree').jstree(true).settings.core.data = this.entities;
                        $j('#jstree').jstree("refresh");
                        this.notify.success(this.lang.entityUpdated);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
        }
    }

    moveEntity() {
        this.http.put(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id, this.currentEntity)
            .subscribe(() => {
                this.notify.success(this.lang.entityUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    readMode() {
        this.creationMode = false;
        this.isDraggable = true;
        $j('#jstree').jstree('deselect_all');
        if (this.currentEntity.parent_entity_id) {
            for (let i = 0; i < this.entities.length; i++) {
                if (this.entities[i].entity_id == this.currentEntity.parent_entity_id) {
                    $j('#jstree').jstree('select_node', this.entities[i]);
                    break;
                }
            }
        } else {
            this.sidenavRight.close();
        }
    }

    selectParentEntity(entity_id: any) {
        if (this.creationMode) {
            $j('#jstree').jstree('deselect_all');
            $j('#jstree').jstree('select_node', entity_id);
        }
    }

    removeEntity() {
        if (this.currentEntity.documents > 0 || this.currentEntity.redirects > 0 || this.currentEntity.instances > 0 || this.currentEntity.users.length > 0) {
            this.config = { data: { entity: this.currentEntity } };
            this.dialogRef = this.dialog.open(EntitiesAdministrationRedirectModalComponent, this.config);
            this.dialogRef.afterClosed().subscribe((result: any) => {
                if (result) {
                    if (this.currentEntity.listTemplate.id) {
                        this.http.delete(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id)
                        .subscribe((data: any) => {
                            this.currentEntity.listTemplate.id = data.id;
                            this.http.get(this.coreUrl + "rest/listTemplates/types/entity_id/roles")
                                .subscribe((data: any) => {
                                    this.listTemplateRoles = data['roles'];
                                }, (err) => {
                                    this.notify.error(err.error.errors);
                                });
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    }

                    if (this.idCircuitVisa) {
                        this.http.delete(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa)
                            .subscribe(() => {
                                this.idCircuitVisa = null;
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                    }
                    
                    this.http.put(this.coreUrl + "rest/entities/" + result.entity_id + "/reassign/" + result.redirectEntity, {})
                        .subscribe((data: any) => {
                            this.entities = data['entities'];
                            $j('#jstree').jstree(true).settings.core.data = this.entities;
                            $j('#jstree').jstree("refresh");
                            this.sidenavRight.close();
                            this.notify.success(this.lang.entityDeleted);

                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                }
                this.dialogRef = null;
            });
        } else {
            let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + this.currentEntity.entity_label + ' »');

            if (r) {
                if (this.currentEntity.listTemplate.id) {
                    this.http.delete(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id)
                    .subscribe((data: any) => {
                        this.currentEntity.listTemplate.id = data.id;
                        this.http.get(this.coreUrl + "rest/listTemplates/types/entity_id/roles")
                            .subscribe((data: any) => {
                                this.listTemplateRoles = data['roles'];
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
                }

                if (this.idCircuitVisa) {
                    this.http.delete(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa)
                        .subscribe(() => {
                            this.idCircuitVisa = null;
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                }
                
                this.http.delete(this.coreUrl + "rest/entities/" + this.currentEntity.entity_id)
                    .subscribe((data: any) => {
                        this.entities = data['entities'];
                        $j('#jstree').jstree(true).settings.core.data = this.entities;
                        $j('#jstree').jstree("refresh");
                        this.sidenavRight.close();
                        this.notify.success(this.lang.entityDeleted);
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
        }

    }

    prepareEntityAdd() {
        this.creationMode = true;
        this.isDraggable = false;
        if (this.currentEntity.entity_id) {
            for (let i = 0; i < this.entities.length; i++) {
                if (this.entities[i].entity_id == this.currentEntity.entity_id) {
                    this.currentEntity = { "entity_type": this.entityTypeList[0].id };
                    this.currentEntity.parent_entity_id = this.entities[i].entity_id;
                    break;
                }
            }
        } else {
            this.currentEntity = { "entity_type": this.entityTypeList[0].id };
            $j('#jstree').jstree('deselect_all');
            this.sidenavRight.open();
            /*for (let i = 0; i < this.entities.length; i++) {
                if (this.entities[i].allowed == true) {
                    $j('#jstree').jstree('select_node', this.entities[i]);
                    break;
                }
            }*/
        }
    }

    updateStatus(entity: any, method: string) {
        this.http.put(this.coreUrl + "rest/entities/" + entity['entity_id'] + "/status", { "method": method })
            .subscribe((data: any) => {
                this.notify.success("");
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateDiffList(template: any, role: any): any {
        this.listDiffModified = true;
        if (role == 'dest' && this.currentEntity.listTemplate.dest.length > 0) {
            this.currentEntity.listTemplate.dest.forEach((listModel: any) => {
                if (listModel.item_id != template.item_id) {
                    this.currentEntity.listTemplate.cc.push(listModel);
                }
            });
            this.currentEntity.listTemplate.dest = [template];
        }
    }

    saveDiffList() {
        this.listDiffModified = false;
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "entity_id",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        };

        this.listTemplateRoles.forEach((role: any) => {
            if (role.available == true) {
                if (this.currentEntity.listTemplate[role.id]) {
                    this.currentEntity.listTemplate[role.id].forEach((listModel: any) => {
                        newDiffList.items.push({
                            "item_id": listModel.item_id,
                            "item_type": listModel.item_type,
                            "item_mode": role.id,
                            "sequence": listModel.sequence
                        })
                    });
                }
            }
        });

        if (newDiffList.items.length == 0) {
            this.http.delete(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id)
                .subscribe((data: any) => {
                    this.currentEntity.listTemplate.id = null;
                    this.http.get(this.coreUrl + "rest/listTemplates/types/entity_id/roles")
                        .subscribe((data: any) => {
                            this.listTemplateRoles = data['roles'];
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    this.notify.success(this.lang.diffusionModelDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.currentEntity.listTemplate.id) {
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.currentEntity.listTemplate.id, newDiffList)
                .subscribe((data: any) => {
                    this.currentEntity.listTemplate.id = data.id;
                    this.http.get(this.coreUrl + "rest/listTemplates/types/entity_id/roles")
                        .subscribe((data: any) => {
                            this.listTemplateRoles = data['roles'];
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.currentEntity.listTemplate.id = data.id;
                    this.http.get(this.coreUrl + "rest/listTemplates/types/entity_id/roles")
                        .subscribe((data: any) => {
                            this.listTemplateRoles = data['roles'];
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    updateDiffListVisa(template: any): any {
        this.listDiffModified = true;
        this.currentEntity.visaTemplate.forEach((listModel: any, i: number) => {
            listModel.sequence = i;
            if (i == (this.currentEntity.visaTemplate.length - 1)) {
                listModel.item_mode = "sign";
            } else {
                listModel.item_mode = "visa";
            }
        });
    }

    saveDiffListVisa() {
        this.listDiffModified = false;
        var newDiffList = {
            "object_id": this.currentEntity.entity_id,
            "object_type": "VISA_CIRCUIT",
            "title": this.currentEntity.entity_id,
            "description": this.currentEntity.entity_id,
            "items": Array()
        };
        if (this.idCircuitVisa == null) {
            this.currentEntity.visaTemplate.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (i == (this.currentEntity.visaTemplate.length - 1)) {
                    listModel.item_mode = "sign";
                } else {
                    listModel.item_mode = "visa";
                }
                newDiffList.items.push({
                    "id": listModel.id,
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.post(this.coreUrl + "rest/listTemplates", newDiffList)
                .subscribe((data: any) => {
                    this.idCircuitVisa = data.id;
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else if (this.currentEntity.visaTemplate.length > 0) {
            this.currentEntity.visaTemplate.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (i == (this.currentEntity.visaTemplate.length - 1)) {
                    listModel.item_mode = "sign";
                } else {
                    listModel.item_mode = "visa";
                }
                newDiffList.items.push({
                    "id": listModel.id,
                    "item_id": listModel.item_id,
                    "item_type": "user_id",
                    "item_mode": listModel.item_mode,
                    "sequence": listModel.sequence
                });
            });
            this.http.put(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa, newDiffList)
                .subscribe((data: any) => {
                    this.idCircuitVisa = data.id;
                    this.notify.success(this.lang.diffusionModelUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.delete(this.coreUrl + "rest/listTemplates/" + this.idCircuitVisa)
            .subscribe(() => {
                this.idCircuitVisa = null;
                this.notify.success(this.lang.diffusionModelDeleted);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }
    }

    removeDiffList(i: number, role: string): any {
        this.listDiffModified = true;
        this.currentEntity.listTemplate[role].splice(i, 1);
    }

    removeDiffListVisa(template: any, i: number): any {
        this.listDiffModified = true;
        this.currentEntity.visaTemplate.splice(i, 1);

        if (this.currentEntity.visaTemplate.length > 0) {
            this.currentEntity.visaTemplate.forEach((listModel: any, i: number) => {
                listModel.sequence = i;
                if (i == (this.currentEntity.visaTemplate.length - 1)) {
                    listModel.item_mode = "sign";
                } else {
                    listModel.item_mode = "visa";
                }
            });
        }
    }

    toggleRole(role: any) {
        if (role.usedIn.length > 0) {
            this.config = { data: { msg: this.lang.confirmAction, warn: this.lang.roleUsedInTemplateInfo + " : <b>" + role.usedIn.join(', ') + '</b><br/>' + this.lang.roleUsedInTemplateInfo2 } };
            let dialogRef = this.dialog.open(ConfirmModalComponent, this.config);
            dialogRef.afterClosed().subscribe(result => {
                if (result === "ok") {
                    role.available = !role.available;
                    this.http.put(this.coreUrl + "rest/listTemplates/types/entity_id/roles", { "roles": this.listTemplateRoles })
                        .subscribe(() => {
                            role.usedIn = [];
                            if (this.currentEntity.listTemplate) {
                                this.currentEntity.listTemplate[role.id] = [];
                            }
                            this.notify.success(this.lang.listTemplatesRolesUpdated);
                        }, (err) => {
                            this.notify.error(err.error.errors);
                        });
                }
            });
        } else {
            role.available = !role.available;
            this.http.put(this.coreUrl + "rest/listTemplates/types/entity_id/roles", { "roles": this.listTemplateRoles })
                .subscribe(() => {
                    if (this.currentEntity.listTemplate) {
                        this.currentEntity.listTemplate[role.id] = [];
                        this.http.get(this.coreUrl + "rest/listTemplates/types/entity_id/roles")
                            .subscribe((data: any) => {
                                this.listTemplateRoles = data['roles'];
                            }, (err) => {
                                this.notify.error(err.error.errors);
                            });
                    }
                    this.notify.success(this.lang.listTemplatesRolesUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    linkUser(newUser:any) {
        console.log(newUser);
        this.userCtrl.setValue('');
        $j('.autocompleteSearch').blur();
        let entity = {
            "entityId"  : this.currentEntity.entity_id,
            "role"      : ''
        };

        this.http.post(this.coreUrl + "rest/users/" + newUser.id + "/entities", entity)
            .subscribe((data: any) => {
                var displayName = newUser.idToDisplay.split(" ");
                var user = {
                    id : newUser.id,
                    user_id : newUser.otherInfo,
                    firstname : displayName[0],
                    lastname : displayName[1]
                }
                this.currentEntity.users.push(user);
                this.dataSource = new MatTableDataSource(this.currentEntity.users);
                this.dataSource.paginator = this.paginator;
                this.dataSource.sort = this.sort;
                this.notify.success(this.lang.userAdded);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
@Component({
    templateUrl: "entities-administration-redirect-modal.component.html"
})
export class EntitiesAdministrationRedirectModalComponent extends AutoCompletePlugin {
    lang: any = LANG;

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<EntitiesAdministrationRedirectModalComponent>) {
        super(http, ['entities']);
    }
}
