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
        /* $this->_send_mail_via_file();
         $this->_send_mail_via_html();
         $this->_send_mail_via_json();*/


        $this->_save_cache('hello om a robot', 'test', 600);
        var_dump($this->_get_cache('test'));
        //$id = shmop_open(15062832132, "c", 0, 10);
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

    private function _save_cache($data, $name, $timeout)
    {


        // get id for name of cache
        $id = shmop_open($this->_get_cache_id($name), "c", 0644, strlen(serialize($data)));

        // return int for data size or boolean false for fail
        if ($id) {
            $this->_set_timeout($name, $timeout);
            return shmop_write($id, serialize($data), 0);
        } else return false;
    }

    function _get_cache($name)
    {
        if (!$this->_check_timeout($name)) {
            $id = shmop_open($this->_get_cache_id($name), "c", 0644, strlen('hello im a robot'));

            if ($id) $data = unserialize(shmop_read($id, 0, shmop_size($id)));
            else return false;          // failed to load data

            if ($data) {                // array retrieved
                shmop_close();
                return $data;
            } else return false;          // failed to load data
        } else return false;              // data was expired
    }

    function _get_cache_id($name)
    {
        $id = array('test' => 15062832132);
        return $id[$name];
    }

    function _set_timeout($name, $int)
    {
        $timeout = new DateTime(date('Y-m-d H:i:s'));
        date_add($timeout, date_interval_create_from_date_string("$int seconds"));
        $timeout = date_format($timeout, 'YmdHis');

        $id = shmop_open(100, "a", 0, 0);
        if ($id) $tl = unserialize(shmop_read($id, 0, shmop_size($id)));
        else $tl = array();
        shmop_delete($id);
        shmop_close($id);

        $tl[$name] = $timeout;
        $id = shmop_open(100, "c", 0644, strlen(serialize($tl)));
        shmop_write($id, serialize($tl), 0);
    }

    function _check_timeout($name)
    {
        $now = new DateTime(date('Y-m-d H:i:s'));
        $now = date_format($now, 'YmdHis');

        $id = shmop_open(100, "a", 0, 0);
        if ($id) $tl = unserialize(shmop_read($id, 0, shmop_size($id)));
        else return true;
        shmop_close($id);

        $timeout = $tl[$name];
        return (intval($now) > intval($timeout));
    }


}