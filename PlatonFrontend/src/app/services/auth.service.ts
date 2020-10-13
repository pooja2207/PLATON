import { Injectable } from '@angular/core';

import {HttpClient} from '@angular/common/http';

import {environment} from '../../environments/environment';
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  baseURl : string;

  constructor(private http: HttpClient) {
    this.baseURl =environment.baseUrl;
   }

   doLogin(params){
    return this.http.post(this.baseURl +'auth/login',params);
   }


   DoRegister(params){
     return this.http.post(this.baseURl+'auth/register',params);
   }
   

   
}
