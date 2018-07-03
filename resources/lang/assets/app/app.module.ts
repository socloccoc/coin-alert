import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule }    from '@angular/forms';
import { HttpModule } from '@angular/http';

import { AlertService,AuthenticationService } from './_services/index';

import {AlertComponent} from './_directives/alert.component'

import { routing } from './app.routing';
import { AppComponent } from './app.component';
import { LoginComponent } from './login/login.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { AccountComponent } from './account/account.component';
import { CoinconfigComponent } from './coinconfig/coinconfig.component';
import { LinegroupComponent } from './linegroup/linegroup.component';

@NgModule({
  declarations: [

    AppComponent,
    LoginComponent,
    DashboardComponent,
    AccountComponent,
    CoinconfigComponent,
    LinegroupComponent,

    AlertComponent
  ],
  imports: [
    routing,
    FormsModule,
    HttpModule,
    BrowserModule
  ],
  providers: [
    AuthenticationService,
    AlertService,
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
