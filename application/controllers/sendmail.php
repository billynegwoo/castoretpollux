<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once('User.php');

class sendmail extends User
{

    public function __construct()
    {
        parent::__construct();

    }


    public function index()
    {
        $this->sendComeBack();
    }

    private function sendComeBack()
    {
        $this->_send_mail_via_file();
        $this->_send_mail_via_html();
        $this->_send_mail_via_json();
    }


    private function _send_mail_via_file()
    {
        $this->parameters = ['format' => 'file', 'password' => 'true', 'orderby' => 'username:random'];
        $list = unserialize($this->listUser());
        $this->_send_email($list);
    }

    private function _send_mail_via_html()
    {
        $this->parameters = ['format' => 'html', 'password' => 'false', 'orderby' => 'password'];
        $list = $this->_html_to_array($this->listUser());
        $this->_send_email($list);
    }

    private function _send_mail_via_json()
    {
        $this->parameters = ['format' => 'json', 'id' => 'true', 'orderby' => 'username'];
        $list = json_decode($this->listUser(), true);
        $this->_send_email($list);
    }


    private function _html_to_array($html)
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $tables = $dom->getElementsByTagName('table');

        foreach ($tables as $table) {
            $rows = $table->getElementsByTagName('tr');
            $cols = $table->getElementsByTagName('th');
            $row_headers = NULL;
            foreach ($cols as $node) {
                $row_headers[] = $node->nodeValue;
            }
            $output = [];
            foreach ($rows as $row) {
                $cols = $row->getElementsByTagName('td');
                $row = [];
                $i = 0;
                foreach ($cols as $node) {

                    if ($row_headers == NULL)
                        array_push($row, $node->nodeValue);
                    else
                        $row[$row_headers[$i]] = $node->nodeValue;
                    $i++;
                }
                array_push($output, $row);
            }
            array_shift($output);
            return $output;
        }
    }

    private function _get_greet()
    {
        $hour = date('H');
        if ($hour >= 6 && $hour < 18) {
            return 'Bonjour';
        } else {
            return 'Bonsoir';
        }
    }

    private function _time_elapsed_string($ptime)
    {
        $now = new DateTime;
        $ago = DateTime::createFromFormat('F d, `Y`, g:i A', $ptime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'an',
            'm' => 'mois',
            'w' => 'semaine',
            'd' => 'jour',
            'h' => 'heure',
            'i' => 'minute',
            's' => 'seconde',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                if ($v == 'mois') $v = $diff->$k . ' ' . $v;
                else $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
        return $string ? implode(', ', $string) : ' maintenant';
    }


    private function _send_email($list)
    {
        $this->load->helper('url');
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.mandrillapp.com',
            'smtp_port' => '587',
            'smtp_user' => 'billynegwoo@gmail.com',
            'smtp_pass' => '6uLWtB5fhPEbC_uyaSfmug',
            'mailtype' => 'html',
            'charset' => 'utf-8'
        );
        $this->load->library('email', $config);
        foreach ($list as $user) {

            $this->email->from('kevin3.blondel@epitech.eu', 'Billynegwoo');
            $data = [
                'username' => $user['username'],
                'greet' => $this->_get_greet(),
                'since' => $this->_time_elapsed_string($user['last_log']),
                'url' => base_url()
            ];

            $this->email->to($user['email']);
            $this->email->subject('please come back :(');

            $body = $this->load->view('emails/comeBackEmail.php', $data, TRUE);
            $this->email->message($body);
            $this->email->send();
        }
    }


}