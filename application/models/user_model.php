<?php

/**
 * Created by PhpStorm.
 * User: kevman
 * Date: 17/03/2016
 * Time: 22:15
 */
class user_model extends CI_Model
{

    public $username;
    public $password;
    public $last_log;


    public function __construct()
    {
        parent::__construct();
    }

    public function insert_into_user($username,$password){
        $this->username = $username;
        $this->password = $password;
        $this->last_log = date('l jS \of F Y h:i:s A');

        $this->db->insert('users', $this);
    }

}