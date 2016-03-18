<?php

/**
 * Created by PhpStorm.
 * User: kevman
 * Date: 18/03/2016
 * Time: 11:38
 */
class User extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }


    public function listUser(){
        $parameters = $this->uri->uri_to_assoc();
        echo '<pre>'.json_encode($this->user->get_all_users(['username','last_log','email'])).'</pre>';

    }
}