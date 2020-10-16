<?php

namespace App\Controllers;
use App\Controllers\LoginController;

/**
 * @file - PagesController.php
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */
class PagesController extends \App\Lib\Controller
{
    /**
     * [servicesathome - to call Services at home page]
     */
    public function servicesathome()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }

        $data['title']='Services @ Home';
        $data['slug']='servicesathome';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [seminars - to call seminars page]
     */
    public function seminars()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type']== 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }
        $data['title']='Seminars';
        $data['slug']='seminars';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [products - to call products page]
     */
    public function products()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }
        $data['title']='Products';
        $data['slug']='products';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [courses - to call courses page]
     */
    public function courses()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }
        $data['title']='Courses';
        $data['slug']='courses';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [specials - to call specials page]
     */
    public function specials()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }
        $data['title']='Specials';
        $data['slug']='specials';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [about - to call about us page]
     */
    public function about()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }
        $data['title']='About Us';
        $data['slug']='about';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [contactus - to call contact us page]
     */
    public function contactus()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }
        $data['title']='Contact Us';
        $data['slug']='contactus';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [home - to call home page]
     */
    public function home()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }
        $data['title']='Home';
        $data['slug']='home';
        $page=$data['slug'];
        $this->view($page,$data);
        return;
    }

    /**
     * [error404 - to call error page if except allowed pages are called]
     */
    public function error404()
    {
        if(isset($_SESSION['user_type']) && $_SESSION['user_type']== 'admin') {
            $loginController = new LoginController();
            $loginController->callAdmin();
            return;
        }       
        
        $data['title']='404';
        $data['slug']='404';
        $page=$data['slug'];
        http_response_code(404);
        $this->view($page,$data);
        return;
    }
}