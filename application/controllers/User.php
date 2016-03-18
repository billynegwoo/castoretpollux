<?php

/**
 * Created by PhpStorm.
 * User: kevman
 * Date: 18/03/2016
 * Time: 11:38
 */
class User extends CI_Controller
{

    private $xml_data;
    public function __construct()
    {
        parent::__construct();
    }


    public function listUser()
    {
        $parameters = $this->uri->uri_to_assoc();
        $data = $this->user->get_all_users();
        $content_type = 'application/json';
        foreach ($parameters as $key => $value) {
            if ($key == 'format') {
                $value = strtolower($value);
                switch ($value) {
                    case'html':
                        $content_type = 'text/html';
                        $data = $this->_build_table($data);
                        break;
                    case'json':
                        $content_type = 'application/json';
                        $data = json_encode($data);
                        break;
                    case 'xml':
                        $content_type = 'text/xml';
                        $this->xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
                        $this->_array_to_xml($data,$this->xml_data);
                        $data = $this->xml_data->asXML();
                        break;
                    case 'file':
                        $content_type = 'application/octet-stream';
                        $data = print_r($data,true);
                        break;

                }
            }elseif($key = 'option'){

            }
        };
        $this->output->set_content_type($content_type);
        $this->output->set_output($data);
    }


    private function _build_table($array)
    {
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
    }

    private function  _array_to_xml($array,$xml_data){

        foreach( $array as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.$key;
                }
                $subnode = $xml_data->addChild($key);
                $this->_array_to_xml($value,$subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}