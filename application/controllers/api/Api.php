<?php defined('BASEPATH') OR exit('No direct script access allowed');

#This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Api extends REST_Controller
{
    function __construct()
	{ 
        parent::__construct();  
		
        #model
       
		
        #load helpers
        $this->load->helper(array('url', 'file', 'string', 'html'));
		$this->load->helper(array('date'));
		$this->load->library('session');
    }
	
    public function test_post(){
		echo "kkkkkkkkkkk";
	}
	 
}