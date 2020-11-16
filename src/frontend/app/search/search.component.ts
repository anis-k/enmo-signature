import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { Router } from '@angular/router';
import { AlertController, LoadingController, MenuController } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { VisaWorkflowComponent } from '../document/visa-workflow/visa-workflow.component';
import { AuthService } from '../service/auth.service';
import { NotificationService } from '../service/notification.service';
import { SignaturesContentService } from '../service/signatures.service';

@Component({
    templateUrl: 'search.component.html',
    styleUrls: ['search.component.scss'],
})
export class SearchComponent implements OnInit {

    loading: boolean = false;
    filesToUpload: any[] = [];
    errors: any[] = [];

    worfklowState: any[] = [
        'VAL', 'END', 'INTERRUPT', 'INPROGRESS'
    ];

    /* ressources = Array.from({ length: 1 }).map((_, i) => {
        return {
            'id': 36,
            'title': 'recommande_2D_000_000_0003_1.pdf',
            'reference': 'blabla',
            'mode': 'sign',
            'workflow': [
                {
                  'userId': 3,
                  'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                  'mode': 'visa',
                  'processDate': null,
                  'current': true,
                  'signatureMode': 'stamp',
                  'userSignatureModes': [
                    'stamp'
                  ]
                },
                {
                  'userId': 1,
                  'userDisplay': 'Jenny JANE-SUR-SAINT-ETIENNE',
                  'mode': 'sign',
                  'processDate': null,
                  'current': false,
                  'signatureMode': 'stamp',
                  'userSignatureModes': [
                    'stamp',
                    'eidas',
                    'inca_card',
                    'rgs_2stars'
                  ]
                }
              ],
        };
      }); */

    ressources: any[] = [
        {
            'id': 36,
            'title': 'recommande_2D_000_000_0003_1.pdf',
            'reference': 'blabla',
            'mode': 'sign',
            'workflow': [
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': null,
                    'status': null,
                    'current': true,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 1,
                    'userDisplay': 'Jenny JANE-SUR-SAINT-ETIENNE',
                    'mode': 'sign',
                    'processDate': null,
                    'status': null,
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp',
                        'eidas',
                        'inca_card',
                        'rgs_2stars'
                    ]
                }
            ],
        },
        {
            'id': 2,
            'title': 'bidule',
            'reference': 'efzfefe',
            'mode': 'visa',
            'workflow': [
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': '2020-11-13 19:11:52.066616',
                    'status': 'VAL',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 1,
                    'userDisplay': 'Jenny JANE-SUR-SAINT-ETIENNE',
                    'mode': 'sign',
                    'processDate': null,
                    'status': null,
                    'current': true,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp',
                        'eidas',
                        'inca_card',
                        'rgs_2stars'
                    ]
                }
            ],
        },
        {
            'id': 2,
            'title': 'bidule',
            'reference': 'efzfefe',
            'mode': 'visa',
            'workflow': [
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': '2020-11-13 19:11:52.066616',
                    'status': 'VAL',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': '2020-11-13 19:11:52.066616',
                    'status': 'VAL',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': '2020-11-13 19:11:52.066616',
                    'status': 'VAL',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
            ],
        },
        {
            'id': 2,
            'title': 'bidule',
            'reference': 'efzfefe',
            'mode': 'visa',
            'workflow': [
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': '2020-11-13 19:11:52.066616',
                    'status': 'VAL',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': '2020-11-13 19:11:52.066616',
                    'status': 'VAL',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': '2020-11-13 19:11:52.066616',
                    'status': 'REF',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': null,
                    'status': 'END',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': null,
                    'status': 'END',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                },
                {
                    'userId': 3,
                    'userDisplay': 'Bernard De la MOTTE-SUR-BIDULE',
                    'mode': 'visa',
                    'processDate': null,
                    'status': 'END',
                    'current': false,
                    'signatureMode': 'stamp',
                    'userSignatureModes': [
                        'stamp'
                    ]
                }
            ],
        },
    ];

    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('rightContent', { static: true }) rightContent: TemplateRef<any>;

    constructor(
        public http: HttpClient,
        private translate: TranslateService,
        public router: Router,
        private menu: MenuController,
        public signaturesService: SignaturesContentService,
        public viewContainerRef: ViewContainerRef,
        public notificationService: NotificationService,
        public authService: AuthService,
        public loadingController: LoadingController,
        public alertController: AlertController,
    ) { }

    ngOnInit(): void { }

    ionViewWillEnter() {
        this.menu.enable(true, 'left-menu');
        this.menu.enable(true, 'right-menu');
        this.signaturesService.initTemplate(this.rightContent, this.viewContainerRef, 'rightContent');
    }

    ionViewWillLeave() {
        this.signaturesService.detachTemplate('rightContent');
    }
}
