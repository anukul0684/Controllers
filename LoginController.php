<?php

namespace App\Controllers;

use App\Lib\Validator;
use App\Models\UserModel;
use App\Controllers\ServicesController;
use App\Models\AppointmentModel;
use App\Models\ServiceModel;

/**
 * @file - LoginController.php
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */
class LoginController extends \App\Lib\Controller
{
    /**
     * [login function to go to Login page on GET request]
     */
    public function login()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $this->callAdmin();
            return;
        }
        
        $data['title'] = 'Login';
        $data['slug'] = 'login';    
        $page = $data['slug']; 
                                 
        $this->view($page, $data);
        return;
    }


    /**
     * [authenticate function to check login credentials on POST request]
     */
    public function authenticate()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $this->callAdmin();
            return;
        }
        $valid_login = new Validator($_POST);        
        $user_model = new UserModel();
        
        //array to have required field's name
        $required_login=array( 
          'email',      
          'user_password');

        //all required fields to check
        $valid_login->required($required_login);

        //get the errors and post values
        $errors=$valid_login->errors();
        $post=$valid_login->post();

        if(empty($errors)) {
            
            $check_user = $user_model->checkUser($post['email']);
            
            if($check_user) {
                if(password_verify($post['user_password'], $check_user['user_password'])) { 
                    $_SESSION['user_type'] = $check_user['user_type'];
                    $_SESSION['userid']=$check_user['id'];
                    session_regenerate_id();              
                    $_SESSION['user_name'] = $check_user['first_name'];

                    if($_SESSION['user_type']=='admin') {
                        $this->callAdmin();
                        return;
                    }                    

                    if(!empty($_SESSION['target'])) {       
                        $welcome = 
                        'Welcome Back ' . $check_user['first_name'] . '! Proceed with payment now.';   
                        flash('success', $welcome);     
                        
                        $data['title'] = 'Checkout Page';
                        $data['slug'] = 'checkout_page';    
                        $page = $data['slug'];                                             
                        
                        unset($_SESSION['target']);
                        header('Location: checkout_page');
                        die;

                    } elseif(!empty($_SESSION['history'])) { 
                        $welcome = 
                        'Welcome Back ' . $check_user['first_name'] . '! Your Booking History:-';   
                        flash('success', $welcome);     
                        
                        $data['title'] = 'Appointment History';
                        $data['slug'] = 'appointment_history';    
                        $page = $data['slug'];                                              
                        
                        unset($_SESSION['history']);

                        header('Location: appointment_history');
                        die;

                    } elseif(!empty($_SESSION['service_history'])) {
                        $welcome = 
                        'Welcome Back ' . $check_user['first_name'] . '! Your Booked Services:-';   
                        flash('success', $welcome);     
                        
                        $data['title'] = 'Service History';
                        $data['slug'] = 'service_history';    
                        $page = $data['slug'];                                               
                        
                        unset($_SESSION['service_history']);
                        unset($_SESSION['appointment_id']);

                        header('Location: service_history');
                        die;
                        
                    } else {
                        $welcome = 
                        'Welcome Back ' . $check_user['first_name'] . '! You have successfully logged in.';   
                        flash('success', $welcome);
                        $data['title'] = 'Profile';
                        $data['slug'] = 'user_success';    
                        $page = $data['slug'];                          
                        
                        header('Location: user_success');
                        die; 
                    }                    
                }
            }
            else {
                flash('error','Invalid credentials. Please try again.');
                $data['title'] = 'Login';
                $data['slug'] = 'login';    
                $page = $data['slug'];                                       
                
                header('Location: login');
                die;
            }
        } //end if errors

        flash('error','Please enter correct details to login.');
        $_SESSION['errors'] = $errors;
        $data['title'] = 'Login';
        $data['slug'] = 'login';    
        $page = $data['slug'];             
         
        header('Location: login');
        die;      
    }

    /**
     * [callAdmin function to go to Admin Dashboard when user is an admin]
     */
    public function callAdmin()
    {    

        $data['title'] = 'Admin Page';
        $data['slug'] = 'dashboard';    
        $page = $data['slug'];           
                                
        header('Location: dashboard');
        die;
        //when you just go to a page or ask browser to request is a GET request
        
    }
}
