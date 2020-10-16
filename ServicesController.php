<?php

namespace App\Controllers;
use App\Models\ServiceModel;
use App\Controllers\PagesController;
use App\Controllers\LoginController;

/**
 * @file - ServicesController.php
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated - 2020-09-30
 */
class ServicesController extends \App\Lib\Controller
{    
    //properties defined for pagination, category, and search links
    private $page = 0;
    private $category = '';
    private $search = '';
    
    /**
     * [services - to show all available services]
     */
    public function services()
    {               
        try
        {
            if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
                $loginController = new LoginController();
                $loginController->callAdmin();
                return;
            }
            //initialize $page for which page is called
            $this->page = $_GET['page'] ?? 1;
            $this->category = $_GET['category'] ?? '';
            $this->search = $_GET['search'] ?? '';

            $service_model = new ServiceModel();

            $data['title'] = 'Services';
            $data['slug'] = 'services';    
            $page = $data['slug'];     

            if($this->category == '') {
                if($this->search == '') {                     
                    $data['services_by_page'] = $service_model->resultsPerPage($this->page);
                    $total=$service_model->getNumLinks();
                    $data['number_of_pages']=$total;
                    $data['sub_category'] = 'No';
                    $data['category_name'] = 'all';
                    $data['search_content'] = 'No';
                }
                else {
                    $data['services_by_page'] = $service_model->searchResultsPerPage($this->search,$this->page);
                    $total=$service_model->getNumSearchLinks($this->search);
                    $data['number_of_pages']=$total;
                    $data['sub_category'] = 'No';
                    $data['category_name'] = 'all';
                    $data['search_content'] = 'Yes';
                    $data['searched'] = $this->search;
                }
            }
            else {
                $data['services_by_page'] = $service_model->getCategoryServices($this->category,$this->page);
                $total=$service_model->getNumCategoryLinks($this->category);
                $data['number_of_pages']=$total;
                $data['sub_category'] = 'Yes';
                $data['category_name'] = $this->category;
                $data['search_content'] = 'No';
            }
            $data['services_category'] = $service_model->getCategory();
            
            $this->view($page, $data);
            return;
        }
        catch(Exception $exception_error)
        {
            $pageController= new PagesController();
            $pageController->error404(); 
        }
    }    

    /**
     * [serviceDetails to show detail of one service selected by user]
     */
    public function serviceDetails()
    {      
        try
        {
            $id = $_GET['service_id'] ?? 0;
            $deleteId = $_GET['serviceid'] ?? 0;
            
            $service_model = new ServiceModel();

            if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {

                if($id != 0) {
                    $data['title'] = 'Service Details';
                    $data['slug'] = 'admin_serviceEdit';    
                    $page = $data['slug'];

                    $data['service_details'] = $service_model->one($id);

                } else if( $deleteId !=0) {
                    $data['title'] = 'Service Details';
                    $data['slug'] = 'admin_serviceDelete';    
                    $page = $data['slug'];

                    $data['service_details'] = $service_model->one($deleteId);

                } else {
                    $pageController= new PagesController();
                    $pageController->error404(); 
                }                 

            } else {

                if($id==0) {
                    $this->services();
                }

                $data['title'] = 'Service Details';
                $data['slug'] = 'service_details';    
                $page = $data['slug'];         

                try
                {
                    $data['service_details'] = $service_model->one($id); 
                    $data['other_services'] = 
                        $service_model->getOtherServices($id,$data['service_details']['service_category']);            
                }
                catch(Exception $exception_error)
                {
                    $pageController= new PagesController();
                    $pageController->error404();   
                }
            }
            $this->view($page, $data);
            return;
        }
        catch(Exception $exception_error)
        {
            $pageController= new PagesController();
            $pageController->error404();            
        }
    }

    
    /**
     * [goToCart function to show cart details on GET Request]
     */
    public function goToCart()
    {
        try
        {
            if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
                $loginController = new LoginController();
                $loginController->callAdmin();
                return;
            }

            $delete_cart_service = $_GET['cart_service'] ?? '';
            if($delete_cart_service != '') {
                if(!empty($_SESSION['cart'])) {
                    for($i=0;$i<count($_SESSION['cart']);$i++)
                    {                        
                        foreach($_SESSION['cart'][$i] as $cart_service_data)
                        {
                            $id = intval($_SESSION['cart'][$i]['service_id']);
                            if($id == $delete_cart_service)
                            {                                
                                array_splice($_SESSION['cart'], intval($i), 1);                                    
                                break;                                
                            }                            
                        }
                    } 
                    $data = $this->refillCart();                    
                }

            } else {
                $data = $this->refillCart();
            }            
            
            $data['title'] = 'Appointment Cart';
            $data['slug'] = 'appointment_cart';    
            $page = $data['slug'];
            $this->view($page, $data);
            return;
        }
        catch(Exception $exception_error)
        {
            $pageController= new PagesController();
            $pageController->error404();   
        }
    }

    /**
     * [addBufferCart function to add services to cart]
     */
    public function addBufferCart()
    {
        try
        {
            if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
                $loginController = new LoginController();
                $loginController->callAdmin();
                return;
            }
            if(!empty($_SESSION['cart'])) {
                if(isset($_POST)) {
                    array_push($_SESSION['cart'], $_POST);                    
                }            
            } else {
                $_SESSION['cart'][] = $_POST;                
            }        
    

            flash('success', 'Add more services OR click Proceed to Buy');
            
            $data['title'] = 'Appointment Cart';
            $data['slug'] = 'appointment_cart';    
            $page = $data['slug'];

            header('Location: appointment_cart');
            die;
            
        }
        catch(Exception $exception_error)
        {
            $pageController= new PagesController();
            $pageController->error404();   
        }
    }

    /**
     * [refillCart function called on GET and POST request to fill cart]
     * @return [array] [multidimensional array containing all cart details]
     */
    public function refillCart()
    {
        $service_model = new ServiceModel();

        if(!empty($_SESSION['cart'])) {
            if(count($_SESSION['cart'])>1) {
                
                for($i=0;$i<count($_SESSION['cart']);$i++)
                {
                    foreach($_SESSION['cart'][$i] as $row)
                    {
                        $id = intval($_SESSION['cart'][$i]['service_id']);
                        $book_qty = intval($_SESSION['cart'][$i]['service_qty']);
                        $data['cart'][$i][$i] = $service_model->one($id);

                        $data['cart'][$i][$i]['service_quantity'] = $book_qty;
                        
                        $data['cart'][$i][$i]['service_totalcost'] = 
                            $book_qty * $data['cart'][$i][$i]['service_price'];                        
                    }                       
                } 
            } else {
                
                for($i=0;$i<count($_SESSION['cart']);$i++)
                {                
                    foreach($_SESSION['cart'][$i] as $row)
                    {
                        $id = intval($_SESSION['cart'][$i]['service_id']);
                        $book_qty = intval($_SESSION['cart'][$i]['service_qty']);
                        $data['cart'][$i][$i] = $service_model->one($id); 

                        $data['cart'][$i][$i]['service_quantity'] = $book_qty;
                        
                        $data['cart'][$i][$i]['service_totalcost'] = 
                            (floatval($data['cart'][$i][$i]['service_price']) * $book_qty);                                    
                    }                
                }     
            }     
            $data['appointment_cost'] = 0;

            for($i=0;$i<count($data['cart']);$i++)
            {
                foreach($data['cart'][$i] as $service_data)
                {
                    $data['appointment_cost'] = 
                        floatval($data['appointment_cost'] + $data['cart'][$i][$i]['service_totalcost']);
                }
            } 

            $data['gst_cost'] = floatval($data['appointment_cost'] * 0.05); //5% gst

            $data['total_cost'] = floatval($data['appointment_cost'] + $data['gst_cost']); // service cost + gst

            return $data;
        }
    }

    /**
     * [clearCart function to Empty the cart]
     */
    public function clearCart()
    {
        unset($_SESSION['cart']);
        $this->services();
    }
}