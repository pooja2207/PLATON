import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { ProfileComponent } from './profile/profile.component';
const routes: Routes = [
  {
    path:'',
    component : ProfileComponent 
  },
  {
    path:'users',
    children:[
      {
        path:'',
        
        component : ProfileComponent
      },
      {
        path:'user-view/:id',
        component : ProfileComponent
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UserRoutingModule { }
