<?php

namespace App\Controllers;

use App\Lib\Validator;
use App\Models\UserModel;
use App\Controllers\LoginController;

/**
 * @file - UsersController.php
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated - 2020-09-18
 */
class UsersController extends \App\Lib\Controller
{
        
    /**
     * [index - to show user registration page on GET request]
     */
    public function index()
    {      
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }         
        
        $data['title'] = 'Sign Up';
        $data['slug'] = 'user_registration';
        $page = $data['slug'];      
        
        $this->view($page, $data);
    }

    /**
     * [register function to register new user]
     */
    public function register()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        if (!empty($_POST['token'])) {
            if (hash_equals($_SESSION['token'], $_POST['token'])) {
                $user_model = new UserModel();

                $v = new Validator($_POST);

                //array to have required field's name
                $required_field=array(
                  'first_name',
                  'last_name',
                  'email',
                  'phone',
                  'street_details',
                  'city_name',
                  'province_name',
                  'country_name',
                  'postal_code',
                  'user_password',
                  'confirm_user_password');

                //all required files to check
                $v->required($required_field);

                //check valid names
                $v->check_name('first_name');
                $v->check_name('last_name');
                $v->check_name('city_name');
                $v->check_name('province_name');
                $v->check_name('country_name');

                //check length for first name and last name, city, province, country name
                $v->length_check('first_name',2,255);
                $v->length_check('last_name',2,255);
                $v->length_check('city_name',2,255);
                $v->length_check('province_name',2,255);
                $v->length_check('country_name',2,255);

                //check valid email
                $v->email_check('email');

                //check valid phone in Canadian format with space/-/. in between
                $v->check_phone('phone');

                //check valid Canadian postal code
                $v->check_post('postal_code');

                //check password length
                $v->length_check('user_password',8,16);
                
                //check for Capital, small, special character and number to be present in password
                $v->check_password('user_password');

                //check password and confirm password matches
                $v->match_val('user_password','confirm_user_password');            

                $errors=$v->errors();
                $post=$v->post();

                if(empty($errors)) {
                    try {
                        $check_email = $user_model->checkUser($post['email']);
                        if(!($check_email)) {
                            $user_id = $user_model->saveUser($post);
                            if($user_id!==0) {
                                flash('success', 'You have successfully registered with us.');               
                                $_SESSION['userid'] = $user_id;
                                session_regenerate_id();
                                $data['title'] = 'Profile';
                                $data['slug'] = 'user_success';
                                $page = $data['slug'];                       
                                
                                $data['user'] = $user_model->one($_SESSION['userid']);   
                                $_SESSION['user_name'] = $data['user']['First Name'];                   
                                $_SESSION['user_type'] = $data['user']['user_type'];
                                
                                header('Location: user_success');
                                die;
                            }   
                        } else {
                            flash('error', 'Email ID already exists. Please try again.');
                            $data['title'] = 'Sign Up';
                            $data['slug'] = 'user_registration';
                            $page = $data['slug'];                    
                            
                            //$this->view($page, $data);
                            //return;
                            header('Location: user_registration');
                            die;
                        } 
                    }
                    catch(Exception $excep)
                    {
                        flash('error', $excep->getMessage());
                        $data['title'] = 'Sign Up';
                        $data['slug'] = 'user_registration';
                        $page = $data['slug'];
                                       
                        //$this->view($page, $data);
                        //return;
                        header('Location: user_registration');            
                        die;
                    }
                } else {
                    //dump_die($errors);
                    flash('error', 'Please correct the following errors and resubmit');
                    $_SESSION['errors'] = $errors;
                    $_SESSION['post'] = $post;
                    $data['title'] = 'Sign Up';
                    $data['slug'] = 'user_registration';
                    $page = $data['slug'];                        
                    
                    // $this->view($page, $data);
                    // return;
                    header('Location: user_registration');          
                    die; 
                }    
            } 
        } else {
         // Log this as a warning and keep an eye on these attempts
            flash('error', 'Caution - Unrecognized error. Please try again');
            $data['title'] = 'Sign Up';
            $data['slug'] = 'user_registration';
            $page = $data['slug'];                        
            
            // $this->view($page, $data);
            // return;
            header('Location: user_registration');          
            die; 
        }   
    }
    /**
     * [login_details - function to go to login when user enters 
     * correct details or clicks submit without entering details]
     */
    public function login_details()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        
        if(empty($_SESSION['userid'])) {
            flash('error', 'Please log in or register to continue');
            $data['title'] = 'Login';
            $data['slug'] = 'login';    
            $page = $data['slug'];                   
                           
            $this->view($page, $data);
            //header('Location: login');
            return;
        }

        $user_model = new UserModel();
        $userid = $_SESSION['userid'];
        $data['title'] = 'Profile';
        $data['slug'] = 'user_success';    
        $page = $data['slug'];
          
        $data['user'] = $user_model->one($userid); 
        $this->view($page, $data);                     
    }


    /**
     * [logout - function to logout from the logged details]     
     */
    public function logout()
    {
        unset($_SESSION['userid']);
        session_regenerate_id();       
        unset($_SESSION['user_name']);
        unset($_SESSION['user_type']);
        
        flash('success','You have successfully logged out.');
        $data['title'] = 'Login';
        $data['slug'] = 'login';    
        $page = $data['slug'];     
         
        // $this->view($page, $data);  
        // return;     
        header('Location: login');
        die;
    }
}