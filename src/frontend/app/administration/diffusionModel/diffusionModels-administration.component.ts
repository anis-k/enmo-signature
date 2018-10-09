import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatPaginator, MatTableDataSource, MatSort, MatDialog, MatDialogRef, MatSidenav } from '@angular/material';

declare function $j(selector: any): any;
declare var angularGlobals: any;


@Component({
    templateUrl: "diffusionModels-administration.component.html",
    providers: [NotificationService]
})
export class DiffusionModelsAdministrationComponent implements OnInit {
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

    config                          : any       = {};
    listTemplates                   : any[]     = [];
    listTemplatesForAssign          : any[]     = [];

    displayedColumns    = ['title', 'description', 'object_type', 'actions'];
    dataSource          = new MatTableDataSource(this.listTemplates);


    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim();
        filterValue = filterValue.toLowerCase();
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, public dialog: MatDialog) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.diffusionModels);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + "rest/listTemplates")
            .subscribe((data: any) => {
                data['listTemplates'].forEach((template: any) => {
                    if (template.object_id.indexOf('VISA_CIRCUIT_') != -1 || template.object_id.indexOf('AVIS_CIRCUIT_') != -1) {
                        this.listTemplates.push(template);
                    }
                });
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.listTemplates);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, () => {
                location.href = "index.php";
            });
    }

    delete(listTemplate: any) {
        let r = confirm(this.lang.confirmAction + ' ' + this.lang.delete + ' « ' + listTemplate.title + ' »');

        if (r) {
            this.http.delete(this.coreUrl + "rest/listTemplates/" + listTemplate['id'])
                .subscribe(() => {
                    setTimeout(() => {
                        var i = 0;
                        this.listTemplates.forEach((template: any) => {
                            if (template.id == listTemplate['id']) {
                                this.listTemplates.splice(i, 1);
                            }
                            i++;
                        });
                        this.dataSource = new MatTableDataSource(this.listTemplates);
                        this.dataSource.paginator = this.paginator;
                        this.dataSource.sort = this.sort;
                    }, 0);
                    this.notify.success(this.lang.diffusionModelDeleted);

                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
