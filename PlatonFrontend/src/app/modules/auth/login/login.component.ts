import { Component, OnInit } from '@angular/core';
import { FormGroup , FormBuilder ,FormControl , Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { ValidatorService} from '../../../services/validator.service';
import { AuthService} from '../../../services/auth.service';
import { from } from 'rxjs';
import { environment } from '../../../../environments/environment';
@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  loginForm : FormGroup;
  public validationMessages = ValidatorService.ValidationMessages;
  title = 'toaster-not';
 

  constructor(
    private fb:FormBuilder,
    private authservice : AuthService,
    ) { }

  ngOnInit() {
    this.createLoginForm();
   }
  
   

   


  createLoginForm(){
    this.loginForm = this.fb.group({
    username: ['', [Validators.required]],
    password: ['', [Validators.required,Validators.minLength(6)]],
    });
   
  }
  onLoginSubmit(values){
    if(this.loginForm.valid ){
      this.authservice.doLogin(values).subscribe(result=>{
      if(result['status'] === "success" && !result['data']['authorization_token']['error']){
        result['data']['last_access_time'] = new Date().getTime();
        localStorage.setItem('authData', JSON.stringify(result['data']));
        localStorage.setItem('role', JSON.stringify(result['data']['role_id']));
        localStorage.setItem('authToken',JSON.stringify(result['data']['authorization_token']));
        
        this.otherservice.setUserData(result['data']);
        this.notifyService.showSuccess("Login Succesfully !!");
        this.router.navigate(['client']);
        }else if (result['status'] === 'error') {
          this.notifyService.showError(result['message']);
      }else if (result['data']['authorization_token']['error']) {
        this.notifyService.showError(result['data']['authorization_token']['error_description']);
    } else {
        this.notifyService.showError('Error');
    }
    },(error) =>{
      this.otherservice.unAuthorizedUserAccess(error);
    });
   }else{
     this.validateFields('loginForm');
   }
   
  }

  validateFields(formGroup) {
    Object.keys(this[formGroup].controls).forEach(field => {
        const control = this[formGroup].get(field);
        control.markAsTouched({ onlySelf: true });
        control.markAsDirty({ onlySelf: true });
    });
}



}
