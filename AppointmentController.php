<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Controllers\ServicesController;
use App\Lib\Validator;
use App\Models\UserModel;
use App\Controllers\LoginController;

/**
 * @file - AppointmentController.php
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */
class AppointmentController extends \App\Lib\Controller
{

    /**
     * [index function to go to Checkout Page]
     */
    public function index()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        if(empty($_SESSION['userid'])) {
            flash('error', 'Please log in to continue');
            $data['title'] = 'Login';
            $data['slug'] = 'login';    
            $page = $data['slug'];     
                      
            $_SESSION['target'] = 'appointment_checkout';      
            
            header('Location: login');
            die;            
        }
                
        $userid = $_SESSION['userid'];
        $user_model = new UserModel();
            
        $serviceController = new ServicesController();
        $data = $serviceController->refillCart();

        $data['title'] = 'Checkout Page';
        $data['slug'] = 'checkout_page';    
        $page = $data['slug'];        
        $data['user'] = $user_model->one($userid);                               
                
        $this->view($page, $data);
        return;
    }

    /**
     * [checkLoggedIn going to thank you page when session has appointment id]
     */
    public function checkLoggedIn()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        if(empty($_SESSION['userid'])) {
            flash('error', 'Please log in to continue');
            $data['title'] = 'Login';
            $data['slug'] = 'login';    
            $page = $data['slug'];     
                     
            $_SESSION['target'] = 'appointment_checkout';      
            $this->view($page, $data);
            //header('Location: login');
            return;
        }       

        if(isset($_SESSION['app_id'])) {
            $app_id = $_SESSION['app_id'];
            $appointmentModel = new AppointmentModel();
            $data['placed'] = $appointmentModel->one($app_id);
            unset($_SESSION['app_id']);

            $data['title'] = 'Appointment Placed';
            $data['slug'] = 'appointment_placed';    
            $page = $data['slug'];
            $this->view($page, $data);
            return;
        }

        $serviceController = new ServicesController();
        $serviceController->services();
    }


    /**
     * [saveAppointment saving appointment and returning the id to a session]
     */
    public function saveAppointment()
    {    

        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        $userid = $_SESSION['userid'];
        $user_model = new UserModel();
        $appointmentModel = new AppointmentModel();

        $v = new Validator($_POST);

        //array to have required field's name
        $required_field=array(
          'card_name',
          'card_number',
          'card_expiry',
          'card_cvv');

        //all required files to check
        $v->required($required_field);

        //check valid names
        $v->check_name('card_name');
        $v->check_number('card_number',15,16);
        $v->check_number('card_expiry',4,4);
        $v->checkExpiry('card_expiry');
        $v->check_number('card_cvv',3,3);
        $v->validateCC('card_number','card_type');
        $v->checkSelected('booking_time');

        $errors=$v->errors();
        $post=$v->post();

        if(empty($errors)) {
            $serviceController = new ServicesController();
            $data = $serviceController->refillCart();
            try {
                $service_card = substr($post['card_number'], -4, 4);
                $bookingTime=date("h:i:s",strtotime($post['booking_time']));
                $appointment_id = 
                $appointmentModel->save($data,$userid,$post['booking_date'],$bookingTime,$service_card);
                if($appointment_id) {
                    $_SESSION['app_id'] = $appointment_id;                       
                }

            } catch (Exception $exception_db) {

                $data['title'] = 'Checkout Page';
                $data['slug'] = 'checkout_page';    
                $page = $data['slug'];        
                $data['user'] = $user_model->one($userid);                
                flash('error', $exception_db->getMessage());                         
                            
                header('Location: checkout_page');
                die;            
                
            }

            $data['title'] = 'Appointment Placed';
            $data['slug'] = 'appointment_placed';    
            $page = $data['slug'];
            flash('success', 'Thank you. Your Appointment is booked succesfully. Below are the details:-');           
            unset($_SESSION['cart']);

            header('Location: appointment_placed');
            die;
                      

        } else {           

            $data['title'] = 'Checkout Page';
            $data['slug'] = 'checkout_page';    
            $page = $data['slug'];        
                    
            flash('error', 'Please correct the following errors and resubmit');

            $_SESSION['errors'] = $errors;
            $_SESSION['post'] = $post;                        
                    
            header('Location: checkout_page');
            die;
                          
        }   
    }


    /**
     * [appointmentIndex getting all appointments booked by a user]
     */
    public function appointmentIndex()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        if(empty($_SESSION['userid'])) {
            flash('error', 'Please log in to continue');
            $data['title'] = 'Login';
            $data['slug'] = 'login';    
            $page = $data['slug'];     
                     
            $_SESSION['history'] = 'appointment_placed';      
            header('Location: login');
            die;            
        }

        $userid = $_SESSION['userid'];

        $historyModel = new AppointmentModel();
        
        $data['title'] = 'Appointment History';
        $data['slug'] = 'appointment_history';    
        $page = $data['slug'];     
        
        $data['appointment_history'] = $historyModel->userAppointments($userid);          
              
        $this->view($page, $data);        
        return;
    }

    /**
     * [serviceIndex getting service history of an appointment]
     */
    public function serviceIndex()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        $appointmentId= $_GET['appointmentId'] ?? '';
        if($appointmentId != '') {

            if(empty($_SESSION['userid'])) {
                flash('error', 'Please log in to continue');
                $data['title'] = 'Login';
                $data['slug'] = 'login';    
                $page = $data['slug'];     
                         
                $_SESSION['service_history'] = 'service_placed'; 
                $_SESSION['appointment_id'] = $appointmentId;    
                
                header('Location: login');
                die;
                
            }
              
            $serviceHistoryModel = new AppointmentModel();
            $data['title'] = 'Service History';
            $data['slug'] = 'service_history';    
            $page = $data['slug'];                                
                              
            $data['serviceHistory'] = 
                $serviceHistoryModel->one($appointmentId); 

            $this->view($page, $data);
            return;
        }
    }
}