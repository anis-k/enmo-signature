import { ChangeDetectorRef, Component, OnInit, Inject, ViewChild, ElementRef } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MAT_DIALOG_DATA, MatSidenav } from '@angular/material';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { AutoCompletePlugin } from '../../../plugins/autocomplete.plugin';
import { FormControl } from '@angular/forms';

declare function $j(selector: any): any;
declare var angularGlobals: any;


@Component({
    templateUrl: "basket-administration.component.html",
    providers: [NotificationService]
})
export class BasketAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;
    
    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;
    dialogRef                       : MatDialogRef<any>;

    selectedIndex                   : number    = 0;

    coreUrl                         : string;
    lang                            : any       = LANG;
    loading                         : boolean   = false;

    config                          : any       = {};
    id                              : string;
    basket                          : any       = {};
    basketClone                     : any       = {};
    basketGroups                    : any[]     = [];
    allGroups                       : any[]     = [];
    basketIdAvailable               : boolean;
    actionsList                     : any[]     = [];
    resultPages                     : any[]     = [];
    creationMode                    : boolean;

    displayedColumns        = ['label_action', 'actions'];
    orderColumns            = ['alt_identifier', 'creation_date', 'process_limit_date', 'res_id', 'priority'];
    orderByColumns          = ['asc', 'desc'];
    langVarName             = [this.lang.chrono, this.lang.creationDate, this.lang.processLimitDate, this.lang.id, this.lang.priority];
    langOrderName           = [this.lang.ascending, this.lang.descending];
    orderColumnsSelected    : any[] = [{"column":"res_id", "order":"asc"}];
    dataSource              : any;


    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private route: ActivatedRoute, private router: Router, private notify: NotificationService, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                window['MainHeaderComponent'].refreshTitle(this.lang.basketCreation);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);

                this.creationMode = true;
                this.basketIdAvailable = false;
                this.loading = false;
            } else {
                this.orderColumnsSelected = [];
                window['MainHeaderComponent'].refreshTitle(this.lang.basketModification);
                window['MainHeaderComponent'].setSnav(this.sidenavLeft);
                window['MainHeaderComponent'].setSnavRight(null);
                
                this.creationMode = false;
                this.basketIdAvailable = true;
                this.id = params['id'];
                this.http.get(this.coreUrl + "rest/baskets/" + this.id)
                    .subscribe((data: any) => {
                        this.basket = data.basket;
                        this.basket.id = data.basket.basket_id;
                        this.basket.name = data.basket.basket_name;
                        this.basket.description = data.basket.basket_desc;
                        this.basket.clause = data.basket.basket_clause;
                        this.basket.isSearchBasket = data.basket.is_visible != "Y";
                        this.basket.flagNotif = data.basket.flag_notif == "Y";
                        if (this.basket.basket_res_order == '' || this.basket.basket_res_order == null) {
                            this.orderColumnsSelected = [];
                        }
                        else {
                            var tmpOrderByColumnsSelected = this.basket.basket_res_order.split(', ');
                            for (let i = 0; i < tmpOrderByColumnsSelected.length; i++) {
                                var value = tmpOrderByColumnsSelected[i].split(' ');
                                if (!value[1]) {
                                    value[1] = 'desc';
                                }
                                this.orderColumnsSelected.push({"column":value[0],"order":value[1]});
                            }
                        }

                        this.basketClone = JSON.parse(JSON.stringify(this.basket));

                        this.http.get(this.coreUrl + "rest/baskets/" + this.id + "/groups")
                            .subscribe((data: any) => {
                                this.allGroups = data.allGroups;

                                this.allGroups.forEach((tmpAllGroup: any) => {
                                    tmpAllGroup.isUsed = false;
                                    data.groups.forEach((tmpGroup: any) => {
                                        if (tmpAllGroup.group_id == tmpGroup.group_id) {
                                            tmpAllGroup.isUsed = true
                                        }
                                    });
                                });

                                data.groups.forEach((tmpGroup: any) => {
                                    tmpGroup.groupActions.forEach((tmpAction: any) => {
                                        tmpAction.used_in_basketlist = tmpAction.used_in_basketlist == "Y";
                                        tmpAction.used_in_action_page = tmpAction.used_in_action_page == "Y";
                                        tmpAction.default_action_list = tmpAction.default_action_list == "Y";
                                    });
                                });
                                this.basketGroups = data.groups;
                                this.resultPages = data.pages;

                                this.loading = false;
                            }, () => {
                                location.href = "index.php";
                            });
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    openSettings(group: any, action: any) {
        this.config = { data: { group: group, action: action, pages: this.resultPages } };
        this.dialogRef = this.dialog.open(BasketAdministrationSettingsModalComponent, this.config);
        this.dialogRef.afterClosed().subscribe((result: any) => {
            if (result) {
                this.http.put(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + result.group.group_id, { 'result_page': result.group.result_page, 'groupActions': result.group.groupActions })
                    .subscribe(() => {
                        this.dialogRef = null;
                        this.notify.success(this.lang.basketUpdated);
                    }, (err) => {
                        this.dialogRef = null;
                        this.openSettings(group, action);
                        this.notify.error(err.error.errors);
                    });
            }
        });
    }

    isAvailable() {
        if (this.basket.id) {
            this.http.get(this.coreUrl + "rest/baskets/" + this.basket.id)
                .subscribe(() => {
                    this.basketIdAvailable = false;
                }, (err) => {
                    this.basketIdAvailable = false;
                    if (err.error.errors == "Basket not found") {
                        this.basketIdAvailable = true;
                    }
                });
        } else {
            this.basketIdAvailable = false;
        }
    }

    onSubmit() {
        if (this.orderColumnsSelected !== null && this.orderColumnsSelected.length > 0) {
            let tmpBasketResOrder = [];
            for (let i = 0; i < this.orderColumnsSelected.length; i++) {
                tmpBasketResOrder[i] = this.orderColumnsSelected[i].column + ' ' + this.orderColumnsSelected[i].order;
            }
            this.basket.basket_res_order = tmpBasketResOrder.join(', ')
        } else {
            this.basket.basket_res_order = '';
        }        
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/baskets", this.basket)
                .subscribe(() => {
                    this.notify.success(this.lang.basketAdded);
                    this.router.navigate(["/administration/baskets/" + this.basket.id]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/baskets/" + this.id, this.basket)
                .subscribe(() => {
                    this.notify.success(this.lang.basketUpdated);
                    this.router.navigate(["/administration/baskets"]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    addLine() {
        this.orderColumnsSelected.push(JSON.parse(JSON.stringify(this.orderColumnsSelected[0])));
    }

    removeLine(index: number) {
        this.orderColumnsSelected.splice(index, 1);
    }

    initAction(groupIndex: number) {
        this.dataSource = new MatTableDataSource(this.basketGroups[groupIndex].groupActions);
        this.dataSource.sort = this.sort;
    }

    setDefaultAction(group: any, action: any) {
        group.groupActions.forEach((tmpAction: any) => {
            if (tmpAction.id == action.id) {
                tmpAction.default_action_list = true;
                tmpAction.used_in_action_page = true;
                tmpAction.used_in_basketlist = true;
            } else {
                tmpAction.default_action_list = false;
            }
        });
        this.addAction(group);
    }

    updateResultPage(group: any) {
        this.http.put(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + group.group_id, { 'result_page': group.result_page, 'groupActions': group.groupActions })
            .subscribe(() => {
                this.notify.success(this.lang.resultPageUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }


    unlinkGroup(groupIndex: any) {
        let r = confirm(this.lang.unlinkGroup + ' ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + this.basketGroups[groupIndex].group_id)
                .subscribe(() => {
                    this.allGroups.forEach((tmpGroup: any) => {
                        if (tmpGroup.group_id == this.basketGroups[groupIndex].group_id) {
                            tmpGroup.isUsed = false;
                        }
                    });
                    this.basketGroups.splice(groupIndex, 1);
                    this.notify.success(this.lang.basketUpdated);
                    this.selectedIndex = 0;
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    linkGroup() {
        this.config = { data: { basketId: this.basket.id, groups: this.allGroups, linkedGroups: this.basketGroups } };
        this.dialogRef = this.dialog.open(BasketAdministrationGroupListModalComponent, this.config);
        this.dialogRef.afterClosed().subscribe((result: any) => {
            if (result) {
                this.http.post(this.coreUrl + "rest/baskets/" + this.id + "/groups", result)
                    .subscribe(() => {
                        this.basketGroups.push(result);
                        this.allGroups.forEach((tmpGroup: any) => {
                            if (tmpGroup.group_id == result.group_id) {
                                tmpGroup.isUsed = true;
                            }
                        });
                        this.notify.success(this.lang.basketUpdated);
                        this.selectedIndex = this.basketGroups.length;
                    }, (err) => {
                        this.notify.error(err.error.errors);
                    });
            }
            this.dialogRef = null;
        });
    }

    addAction(group: any) {
        this.http.put(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + group.group_id, { 'result_page': group.result_page, 'groupActions': group.groupActions })
            .subscribe(() => {
                this.notify.success(this.lang.actionsGroupBasketUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    toggleIsSearchBasket(basket: any) {
        basket.isSearchBasket = !basket.isSearchBasket
        this.basketClone.isSearchBasket = basket.isSearchBasket;

        this.http.put(this.coreUrl + "rest/baskets/" + this.id, this.basketClone)
            .subscribe(() => {
                this.notify.success(this.lang.basketUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    toggleFlagNotif(basket: any) {
        basket.flagNotif = !basket.flagNotif;
        this.basketClone.flagNotif = basket.flagNotif;

        this.http.put(this.coreUrl + "rest/baskets/" + this.id, this.basketClone)
            .subscribe(() => {
                this.notify.success(this.lang.basketUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    unlinkAction(group: any, action: any) {
        let r = confirm(this.lang.unlinkAction + " ?");

        if (r) {
            action.checked = false;
            this.http.put(this.coreUrl + "rest/baskets/" + this.id + "/groups/" + group.group_id, { 'result_page': group.result_page, 'groupActions': group.groupActions })
            .subscribe(() => {
                this.notify.success(this.lang.actionsGroupBasketUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }
    }
}

@Component({
    templateUrl: "basket-administration-settings-modal.component.html",
    styles: [".mat-dialog-content{height: 65vh;}"]
})
export class BasketAdministrationSettingsModalComponent extends AutoCompletePlugin {

    lang: any = LANG;
    allEntities: any[] = [];
    statuses: any;
    selectedStatuses: any[] = [];
    statusCtrl = new FormControl();

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<BasketAdministrationSettingsModalComponent>) {
        super(http, ['users', 'statuses']);
    }

    @ViewChild('statusInput') statusInput: ElementRef;

    ngOnInit(): void {
        this.http.get(this.coreUrl + "rest/entities")
            .subscribe((entities: any) => {
                let keywordEntities = [{
                    id: 'ALL_ENTITIES',
                    keyword: 'ALL_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: 'Toutes les entités'
                }, {
                    id: 'ENTITIES_JUST_BELOW',
                    keyword: 'ENTITIES_JUST_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Immédiatement inférieur à mon entité primaire"
                }, {
                    id: 'ENTITIES_BELOW',
                    keyword: 'ENTITIES_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Inférieur à toutes mes entités"
                }, {
                    id: 'ALL_ENTITIES_BELOW',
                    keyword: 'ALL_ENTITIES_BELOW',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Inférieur à mon entité primaire"
                }, {
                    id: 'MY_ENTITIES',
                    keyword: 'MY_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Mes entités"
                }, {
                    id: 'MY_PRIMARY_ENTITY',
                    keyword: 'MY_PRIMARY_ENTITY',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Mon entité primaire"
                }, {
                    id: 'SAME_LEVEL_ENTITIES',
                    keyword: 'SAME_LEVEL_ENTITIES',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Même niveau de mon entité primaire"
                }, {
                    id: 'ENTITIES_JUST_UP',
                    keyword: 'ENTITIES_JUST_UP',
                    parent: '#',
                    icon: 'fa fa-hashtag',
                    allowed: true,
                    text: "Immédiatement supérieur à mon entité primaire"
                }];

                keywordEntities.forEach((keyword: any) => {
                    this.allEntities.push(keyword);
                });
                entities.entities.forEach((entity: any) => {
                    this.allEntities.push(entity);
                });
            }, () => {
                location.href = "index.php";
            });
        this.http.get(this.coreUrl + 'rest/statuses')
            .subscribe((response: any) => {
                this.statuses = response.statuses;
                response.statuses.forEach((status: any) => {
                    if (this.data.action.statuses.indexOf(status.id) > -1) {
                        this.selectedStatuses[this.data.action.statuses.indexOf(status.id)] = { idToDisplay: status.label_status, id: status.id };
                    }
                });
            });            
    }

    remove(index: number): void {
        this.selectedStatuses.splice(index, 1);
        this.statusCtrl.setValue(null);
        this.statusInput.nativeElement.value = '';
    }

    add(status: any): void {
        let isIn = false;

        this.selectedStatuses.forEach((statusList: any) => {
            if (status.id == statusList.id) {
                isIn = true;
            }
        });
        if (!isIn) {
            this.selectedStatuses.push(status);
            this.statusCtrl.setValue(null);
            this.statusInput.nativeElement.value = '';
        }
    }

    initService() {
        this.allEntities.forEach((entity: any) => {
            entity.state = { "opened": false, "selected": false };
            this.data.action.redirects.forEach((keyword: any) => {
                if ((entity.id == keyword.keyword && keyword.redirect_mode == 'ENTITY') || (entity.id == keyword.entity_id && keyword.redirect_mode == 'ENTITY')) {
                    entity.state = { "opened": true, "selected": true };
                } 
            });
        });

        $j('#jstree').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': this.allEntities
            },
            "plugins": ["checkbox", "search"]
        });
        $j('#jstree')
            // listen for event
            .on('select_node.jstree', (e: any, data: any) => {
                if (data.node.original.keyword) {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: '', keyword: data.node.id, redirect_mode: 'ENTITY' })
                } else {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: data.node.id, keyword: '', redirect_mode: 'ENTITY' })
                }

            }).on('deselect_node.jstree', (e: any, data: any) => {
                this.data.action.redirects.forEach((redirect: any) => {
                    if (data.node.original.keyword) {
                        if (redirect.keyword == data.node.original.keyword) {
                            let index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    } else {
                        if (redirect.entity_id == data.node.id) {
                            let index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    }

                });
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

    initService2() {
        this.allEntities.forEach((entity: any) => {
            entity.state = { "opened": false, "selected": false };
            this.data.action.redirects.forEach((keyword: any) => {
                if (entity.id == keyword.keyword && keyword.redirect_mode == 'USERS') {
                    entity.state = { "opened": true, "selected": true };
                }
            });
        });
        $j('#jstree2').jstree({
            "checkbox": {
                "three_state": false //no cascade selection
            },
            'core': {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data': this.allEntities
            },
            "plugins": ["checkbox", "search"]
        });
        $j('#jstree2')
            // listen for event
            .on('select_node.jstree', (e: any, data: any) => {
                if (data.node.original.keyword) {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: '', keyword: data.node.id, redirect_mode: 'USERS' })
                } else {
                    this.data.action.redirects.push({ action_id: this.data.action.id, entity_id: data.node.id, keyword: '', redirect_mode: 'USERS' })
                }

            }).on('deselect_node.jstree', (e: any, data: any) => {
                this.data.action.redirects.forEach((redirect: any) => {
                    if (data.node.original.keyword) {
                        if (redirect.keyword == data.node.original.keyword) {
                            let index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    } else {
                        if (redirect.entity_id == data.node.id) {
                            let index = this.data.action.redirects.indexOf(redirect);
                            this.data.action.redirects.splice(index, 1);
                        }
                    }

                });
            })
            // create the instance
            .jstree();

        var to: any = false;
        $j('#jstree_search2').keyup(function () {
            if (to) { clearTimeout(to); }
            to = setTimeout(function () {
                var v = $j('#jstree_search2').val();
                $j('#jstree2').jstree(true).search(v);
            }, 250);
        });
    }

    saveSettings() {
        this.data.action.statuses = [];
        this.selectedStatuses.forEach((status: any) => {
            this.data.action.statuses.push(status.id);
        });
        this.dialogRef.close(this.data);
    }
}

@Component({
    templateUrl: "basket-administration-groupList-modal.component.html",
    styles: [".mat-dialog-content{height: 65vh;}"]
})
export class BasketAdministrationGroupListModalComponent {

    coreUrl: string;
    lang: any = LANG;
    actionAll: any = [];
    newBasketGroup: any = {};

    constructor(public http: HttpClient, @Inject(MAT_DIALOG_DATA) public data: any, public dialogRef: MatDialogRef<BasketAdministrationGroupListModalComponent>) {
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + "rest/actions")
            .subscribe((data: any) => {
                data.actions.forEach((tmpAction: any) => {
                    tmpAction.where_clause = "";
                    tmpAction.used_in_basketlist = false;
                    tmpAction.default_action_list = false;
                    tmpAction.used_in_action_page = true;
                    tmpAction.statuses = [];
                    tmpAction.redirects = [];
                    tmpAction.checked = false;
                    this.actionAll.push(tmpAction);
                });

            }, () => {
                location.href = "index.php";
            });

        this.data.groups.forEach((tmpGroup: any) => {
            this.data.linkedGroups.forEach((tmpLinkedGroup: any) => {
                if (tmpGroup.group_id == tmpLinkedGroup.group_id) {
                    let index = this.data.groups.indexOf(tmpGroup);
                    this.data.groups.splice(index, 1);
                }
            });
        });
    }

    validateForm(group: any) {
        if (this.data.linkedGroups.length == 0) {
            this.newBasketGroup.result_page = 'list_with_attachments';
            this.actionAll[0].used_in_action_page = true;
            this.actionAll[0].default_action_list = true;
            this.actionAll[0].used_in_basketlist = true;
            this.actionAll[0].checked = true;
            this.newBasketGroup.groupActions = this.actionAll;
        } else {
            this.newBasketGroup = JSON.parse(JSON.stringify(this.data.linkedGroups[this.data.linkedGroups.length - 1]));
        }
        this.newBasketGroup.group_id = group.group_id;
        this.newBasketGroup.group_desc = group.group_desc;
        this.dialogRef.close(this.newBasketGroup);
    }
}
