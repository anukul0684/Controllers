<?php
namespace App\Controllers;

use App\Controllers\LoginController;
use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\AppointmentModel;
use App\Models\LogModel;
use App\Lib\Validator;

/**
 * @file - AppointmentController.php
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */
class AdminController extends \App\Lib\Controller
{

    private $search = '';

    /**
     * [index getting all details to display on Dashboard]
     */
    public function index()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $serviceModel = new ServiceModel();
            $userModel = new UserModel();
            $appointmentModel = new AppointmentModel;
            $logModel = new LogModel();

            $data['avgCost']=$serviceModel->avgCost(); 
            $data['avgRating']=$serviceModel->avgRating();
            $data['orderByUsers'] = $appointmentModel->userWiseOrders(); 
            $data['serviceOrders'] = $appointmentModel->serviceWiseOrders();
            $data['logDetails'] = $logModel->showLog();
            
            $data['title'] = 'Admin Page';
            $data['slug'] = 'dashboard';    
            $page = $data['slug'];        
                              
            $this->view($page, $data);
            return;               
        }
    }

    /**
     * [usersList function to show users list on Admin User page]
     */
    public function usersList()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $userModel = new UserModel();
            $data['users'] =  $userModel->all();
            $this->callPage('Users','admin_users','success','Users registered with Angel',$data);
            return;
        }
    }

    /**
     * [userdetailsList function to show user details on Admin User Details page]
     */
    public function userdetailsList()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $userModel = new UserModel();
            $user_id = $_GET['id'] ?? '';
            if($user_id!='') {                
                $data['user'] =  $userModel->one($user_id);
                $this->callPage('User','admin_usersdetails','success','User\'s Details',$data);
                return;
            }             
            $userModel = new UserModel();
            $data['users'] =  $userModel->all();
            $this->callPage('Users','admin_users','success','Users registered with Angel',$data);
            return;            
        }
    }

    /**
     * [servicesList function to show services list view on Admin Services Page]
     */
    public function servicesList()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            
            $serviceModel = new ServiceModel();
            
            $this->search = $_GET['search'] ?? '';
            //dump_die($this->search);
            if($this->search == '') {
                $data['search_content'] = 'No';
                $data['searched'] = '';
                $data['services'] = $serviceModel->all();
                $this->callPage('Services','admin_services','success','Services @ Angel',$data);
                return;
            } 

            $data['services'] = $serviceModel->searchResults($this->search);
                        
            $data['search_content'] = 'Yes';
            $data['searched'] = $this->search;
            $data['title'] = 'Services';
            $data['slug'] = 'admin_services';    
            $page = $data['slug']; 
            $this->view($page, $data);
            return;
        }
    }

    /**
     * [addService function to go to Admin's Add Service Page]
     */
    public function addService()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {      
            $data=[];      
            $this->callPage('Add Service','admin_serviceAdd','success','Add Service Here',$data);
            return;
        }
    }

    /**
     * [saveService function to save new service on Admin Add Service Page]
     */
    public function saveService()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $v = new Validator($_POST);

            //array to have required field's name
            $required_field=array(
              'serviceName',
              'serviceDescription');

            //all required files to check
            $v->required($required_field);

            //check valid names
            $v->check_name('serviceName');
            
            $v->checkSelected('serviceCategory');
            $v->checkSelected('serviceType');
            $v->checkPrice('servicePrice');

            $errors=$v->errors();
            $post=$v->post();

            if(empty($errors)) {
               try {
                    $serviceModel = new ServiceModel();
                    $check_service = $serviceModel->checkService($post['serviceName'],$post['serviceCategory']);
                    if(!($check_service)) {
                        $service_id = $serviceModel->saveNewService($post);
                        if($service_id!==0) {
                            flash('success', 'You have successfully a new service - ' . $post['serviceName']);   
                            
                            $data['title'] = 'Services';
                            $data['slug'] = 'admin_services';
                            $page = $data['slug'];                       
                                                                                  
                            header('Location: admin_services');
                            die;
                        }   
                    } else {
                        flash('error', 'Service already exists. Please try again.');
                        $data['title'] = 'Add Service';
                        $data['slug'] = 'admin_serviceAdd';
                        $page = $data['slug'];                   
                        
                        header('Location: admin_serviceAdd');
                        die;
                    } 
                }
                catch(Exception $excep)
                {
                    flash('error', $excep->getMessage());
                    $data['title'] = 'Add Service';
                    $data['slug'] = 'admin_serviceAdd';
                    $page = $data['slug'];                                  
                    
                    header('Location: admin_serviceAdd');            
                    die;
                }
            } else {                
                flash('error', 'Please correct the following errors and try again');
                $_SESSION['errors'] = $errors;
                $_SESSION['post'] = $post;
                $data['title'] = 'Add Service';
                $data['slug'] = 'admin_serviceAdd';
                $page = $data['slug'];                        
                
                header('Location: admin_serviceAdd');          
                die; 
            }     
        }

    }

    /**
     * [serviceDelete function to soft delete Service on Admin Delete Service Page]
     */
    public function serviceDelete()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {      
            $deleteId = $_POST['service_id'];      
            $serviceModel = new ServiceModel();
            $serviceModel->deleteService($deleteId);

            flash('success', $_POST['service_name'] . ' service is inactived and will not be shown to Users');

            $data['title'] = 'Services';
            $data['slug'] = 'admin_services';    
            $page = $data['slug'];                              
            
            header('Location: admin_services');
            die;
        }
    }

    /**
     * [serviceUpdate function to update service details of selected service to Admin Edit Service Page]
     */
    public function serviceUpdate()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {      
            $updateId = $_POST['service_id'];  
            $updateName = $_POST['serviceName'];
            $updatePrice = $_POST['servicePrice'];
            $updateDesc = $_POST['serviceDescription'];

            $updateActive = $_POST['serviceActive'] ?? '';
            
            if($updateActive=='on') {
                $activeStatus = 'Yes';
            } elseif($updateActive=='') {
                $activeStatus = 'No';
            }
            
            $serviceModel = new ServiceModel();
            $serviceModel->updateService($updateId,$updateName,$updatePrice,$updateDesc,$activeStatus);

            flash('success', $updateName . ' service is now updated');

            $data['title'] = 'Services';
            $data['slug'] = 'admin_services';    
            $page = $data['slug'];                              
            
            header('Location: admin_services');
            die;
        }
    }

    /**
     * [coursesList function to go to Admin Course List view Page]
     */
    public function coursesList()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $data=[];
            $this->callPage('Courses','admin_courses','success','Courses @ Angel',$data);
            return;
        }
    }

    /**
     * [seminarsList function to go to Admin Seminars List view Page]
     */
    public function seminarsList()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $data=[];
            $this->callPage('Seminars','admin_seminars','success','Seminars @ Angel',$data);
            return;
        }
    }

    /**
     * [productsList function to go to Admin Products List View Page]
     */
    public function productsList()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $data=[];
            $this->callPage('Products','admin_products','success','Products @ Angel',$data);
            return;
        }
    }

    /**
     * [callPage description]
     * @param  [string] $title      [page title]
     * @param  [type] $page       [page]
     * @param  [type] $flash_type [flash type] // will not work in this case
     * @param  [type] $flash_msg  [flash message] //will not work in this case
     * @param  [array] $data       [data to send to page]
     */
    public function callPage($title,$page,$flash_type,$flash_msg,$data)
    {
        flash($flash_type, $flash_msg);

        $data['title'] = $title;
        $data['slug'] = $page;    
        $page = $page;                              
        
        $this->view($page, $data);                        
        return;
    }
}