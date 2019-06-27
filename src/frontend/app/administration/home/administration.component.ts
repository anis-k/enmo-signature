import { Component, OnInit } from '@angular/core';
import { SignaturesContentService } from '../../service/signatures.service';
import { AuthService } from '../../service/auth.service';

export interface Privilege {
    id: string;
    icon: string;
    route: string;
}

@Component({
    selector: 'app-administration',
    templateUrl: 'administration.component.html',
    styleUrls: ['../administration.scss', 'administration.component.scss'],
})

export class AdministrationComponent implements OnInit {

    constructor(public signaturesService: SignaturesContentService, public authService: AuthService) { }

    ngOnInit(): void { }
}
