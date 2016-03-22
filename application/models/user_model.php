<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

    public function get_all_users($fields, $options = [])
    {
        if (count($fields) == 0) {
            return [];
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
        foreach ($options as $option) {
            $option = explode(':', $option);
            if (count($option) > 1 && isset($option[1])) {
                $this->db->order_by($option[0], $option[1]);
            } else {
                $this->db->order_by($option[0]);
            }

        }
        $query = $this->db->get('users');
        return $query->result_array();
    }

}