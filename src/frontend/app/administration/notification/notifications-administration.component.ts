import { ChangeDetectorRef, Component, ViewChild, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatSidenav, MatPaginator, MatTableDataSource, MatSort } from '@angular/material';


declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "notifications-administration.component.html",
    providers: [NotificationService]
})
export class NotificationsAdministrationComponent implements OnInit {
    /*HEADER*/
    titleHeader                              : string;
    @ViewChild('snav') public  sidenavLeft   : MatSidenav;
    @ViewChild('snav2') public sidenavRight  : MatSidenav;

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;

    notifications: any[] = [];
    loading: boolean = false;
    lang: any = LANG;

    hours : any;
    minutes : any;

    months : any = [];

    dom : any = [];

    dow : any = []

    newCron: any = {
        "m" : "",
        "h" : "",
        "dom" : "",
        "mon" : "",
        "cmd" : "",
        "state": "normal"
    }

    authorizedNotification :any;
    crontab:any;

    displayedColumns = ['notification_id', 'description', 'is_enabled', 'notifications'];
    dataSource = new MatTableDataSource(this.notifications);
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        window['MainHeaderComponent'].refreshTitle(this.lang.administration + ' ' + this.lang.notifications);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(null);

        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.http.get(this.coreUrl + 'rest/notifications')
            .subscribe((data: any) => {
                this.notifications = data.notifications;
                this.loading = false;
                setTimeout(() => {
                    this.dataSource = new MatTableDataSource(this.notifications);
                    this.dataSource.paginator = this.paginator;
                    this.dataSource.sort = this.sort;
                }, 0);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteNotification(notification: any) {
        let r = confirm(this.lang.deleteMsg);

        if (r) {
            this.http.delete(this.coreUrl + 'rest/notifications/' + notification.notification_sid)
                .subscribe((data: any) => {
                    this.notifications = data.notifications;
                    setTimeout(() => {
                        this.dataSource = new MatTableDataSource(this.notifications);
                        this.dataSource.paginator = this.paginator;
                        this.dataSource.sort = this.sort;
                    }, 0);
                    this.sidenavRight.close();
                    this.notify.success(this.lang.notificationDeleted);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    loadCron() {

        this.hours = [{label:this.lang.eachHour,value:'*'}];
        this.minutes = [{label:this.lang.eachMinute,value:'*'}];

        this.months = [
            {label:this.lang.eachMonth,value:'*'},
            {label:this.lang.january,value:"1"},
            {label:this.lang.february,value:"2"},
            {label:this.lang.march,value:"3"},
            {label:this.lang.april,value:"4"},
            {label:this.lang.may,value:"5"},
            {label:this.lang.june,value:"6"},
            {label:this.lang.july,value:"7"},
            {label:this.lang.august,value:"8"},
            {label:this.lang.september,value:"9"},
            {label:this.lang.october,value:"10"},
            {label:this.lang.november,value:"11"},
            {label:this.lang.december,value:"12"}
        ]

        this.dom = [{label:this.lang.notUsed,value:'*'}];

        this.dow = [
            {label:this.lang.eachDay,value:'*'},
            {label:this.lang.monday,value:"1"},
            {label:this.lang.thuesday,value:"2"},
            {label:this.lang.wednesday,value:"3"},
            {label:this.lang.thursday,value:"4"},
            {label:this.lang.friday,value:"5"},
            {label:this.lang.saturday,value:"6"},
            {label:this.lang.sunday,value:"7"}
        ];

        this.newCron = {
            "m" : "",
            "h" : "",
            "dom" : "",
            "mon" : "",
            "cmd" : "",
            "state": "normal"
        };

        for (var i = 0; i <= 23; i++) {
            this.hours.push({label:i,value:String(i)});
        }

        for (var i = 0; i <= 59; i++) {
            this.minutes.push({label:i,value:String(i)});
        }

        for (var i = 1; i <= 31; i++) {
            this.dom.push({label:i,value:String(i)});
        }

        this.http.get(this.coreUrl + 'rest/notifications/schedule')
            .subscribe((data: any) => {
                this.crontab = data.crontab;
                this.authorizedNotification = data.authorizedNotification;
            }, (err) => {
                this.notify.error(err.error.errors);
            });

    }

    saveCron() {
        var description = this.newCron.cmd.split("/");
        this.newCron.description = description[description.length-1];
        this.crontab.push(this.newCron);
        this.http.post(this.coreUrl + 'rest/notifications/schedule', this.crontab)
            .subscribe((data: any) => {
                this.newCron = {
                    "m" : "",
                    "h" : "",
                    "dom" : "",
                    "mon" : "",
                    "cmd" : "",
                    "description" : "",
                    "state": "normal"
                }
                this.notify.success(this.lang.notificationScheduleUpdated);
            }, (err) => {
                this.crontab.pop();
                this.notify.error(err.error.errors);
            });
    }

    deleteCron(i:number) {
        this.crontab[i].state = 'deleted';
        this.http.post(this.coreUrl + 'rest/notifications/schedule', this.crontab)
            .subscribe((data: any) => {
                this.notify.success(this.lang.notificationScheduleUpdated);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
