<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class User extends CI_Controller
{

    private $xml_data;

    public $parameters;

    private $fields ;

    private $options = [];

    private $data = [];

    public function __construct()
    {
        parent::__construct();
        $this->fields = ['username', 'last_log', 'email'];
    }


    public function listUser()
    {
        if(is_null($this->parameters)){
            $this->parameters = $this->uri->uri_to_assoc();
        };
        foreach ($this->parameters as $key => $value) {
            if ($this->parameters[$key] === 'true')
                array_push($this->fields, $key);
            elseif ($this->parameters[$key] === 'false') {
                $key = array_search($key, $this->fields);
                if ($key !== false) {
                    unset($this->fields[$key]);
                }
            }
        }
        foreach ($this->parameters as $key => $value) {
            if ($key == 'orderby') {
                array_push($this->options, $value);
            }
        }
        $this->data = json_encode($this->user->get_all_users($this->fields, $this->options));
        foreach ($this->parameters as $key => $value) {
            if ($key == 'format') {
                $value = strtolower($value);
                switch ($value) {
                    case'html':
                        $this->data = $this->_build_table($this->user->get_all_users($this->fields, $this->options));
                        break;
                    case'json':
                        $this->data = json_encode($this->user->get_all_users($this->fields, $this->options));
                        break;
                    case 'xml':
                        $this->xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
                        $this->_array_to_xml($this->user->get_all_users($this->fields, $this->options), $this->xml_data);
                        $this->data = $this->xml_data->asXML();
                        break;
                    case 'file':
                        $this->data = serialize($this->user->get_all_users($this->fields, $this->options));
                        break;

                }
            }
        };
        return $this->data;
    }

    private function _build_table($array)
    {
        if ($array) {
            $html = '<table>';
            $html .= '<tr>';
            foreach ($array[0] as $key => $value) {
                $html .= '<th>' . $key . '</th>';
            }
            $html .= '</tr>';
            foreach ($array as $key => $value) {
                $html .= '<tr>';
                foreach ($value as $key2 => $value2) {
                    $html .= '<td>' . $value2 . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
            return $html;
        } else {
            return '<object></object>';
        }

    }

    private function  _array_to_xml($array, $xml_data)
    {

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subnode = $xml_data->addChild($key);
                $this->_array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}