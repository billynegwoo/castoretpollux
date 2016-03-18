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
    public $email;
    public $last_log;


    public function __construct()
    {
        parent::__construct();
    }

    public function insert_into_user($username, $password, $email)
    {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->last_log = now();
        $this->db->insert('users', $this);
    }

    public function get_all_users($fields = [])
    {
        if (count($fields) == 0) {
            $this->db->select('username', 'email', "DATE_FORMAT(last_log, '%M %d, %Y ,%h:%m %r') as last_log");
        }
        foreach ($fields as $field) {
            if ($field == 'last_log') {
                $this->db->select("DATE_FORMAT(last_log, '%M %d, %Y ,%h:%i %p') as last_log");
            } else {
                $this->db->select($field);

            }
        }
        $this->db->order_by('last_log', 'desc');
        $this->db->order_by('LENGTH(username)', 'desc');

        $query = $this->db->get('users');
        return $query->result_array();
    }

}