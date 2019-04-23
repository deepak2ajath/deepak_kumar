<?php defined('BASEPATH') OR exit('No direct script access allowed');

#This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Ziqqi_api extends REST_Controller
{
    function __construct()
	{ 
        parent::__construct();  
		
        #model
        $this->load->model('Api_model');
		
        #load helpers
        $this->load->helper(array('url', 'file', 'string', 'html'));
		$this->load->helper(array('date'));
		$this->load->library('session');
    }
	
	#THIS IS COMMON FUNCTION USING FOR REQUIRED PARAMETER VALIDATION.
	public function checkRequiredParam($postdata)
	{
		$error='';
		foreach ($postdata as $key => $value) 
		{
			if (!isset($postdata[$key]) || strlen(trim($postdata[$key])) <= 0) {
			
				$error .= $key . ', ';
			}
		}
		return rtrim($error,", ");
	}
	
	#THIS FUNCTION IS USING FOR SIGNUP.
	function signup_post()
	{
	    
		#set input data.
		$this->Api_model->setuserName($this->post('email'));
		$this->Api_model->setpassword($this->post('password').HASHTOKEN);
		$this->Api_model->setfname($this->post('fname'));
		$this->Api_model->setlname($this->post('lname'));
		$this->Api_model->setphone($this->post('phone'));
		$this->Api_model->setphonecode($this->post('phonecode'));
		$this->Api_model->setGender('M');
		
		$emailToken=md5(uniqid(microtime()) . $this->post('fname') . $this->post('lname'));
		$this->Api_model->setEmailToken($emailToken);
		$this->Api_model->setPhoneToken(rand(1000, 9999));
	
	    #set black data in input;
		$this->Api_model->setGoogleOauth('');
	    $this->Api_model->setGoogleLogin(0);
		$this->Api_model->setFacebookLogin(0);
		$this->Api_model->setFacebookOauth('');
		
		$this->Api_model->setEmailVerify(1);
		$this->Api_model->setPhoneVerify(1);
		$this->Api_model->setUserStatus(0);

        if(!empty($this->post('gender')))
		{
			$this->Api_model->setGender($this->post('gender'));
		}
       
	    #array for check mandatory input fields.
		$postdata=array(
		        'email'     =>  $this->post('email'),
				'password'  =>  $this->post('password'),
				'fname'     =>  $this->post('fname'),
				'lname'     =>  $this->post('lname'),
				'phone'     =>  $this->post('phone'),
				'phonecode' =>  $this->post('phonecode'),
		);
		
		$email        =  $this->post('email');
		$name         =  $this->post('fname');
		$contact_num  =  $this->post('phone');
		$phonecode    =  $this->post('phonecode');
	

		#input fields validation.
		$errorStr     =  $this->checkRequiredParam($postdata);

	
		#check customer by their email id.
		$emailcheck   =  $this->Api_model->check_customer_email();
		
		#check customer by their phone.
		$phonecheck   =  $this->Api_model->check_customer_phone();
		

		#mandatory field.
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr));
		}
		else if(!empty($emailcheck))
		{
	        $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Email ID already in use.'));
		}else if(!empty($phonecheck))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Phone Number already in use.'));
        }else{
		  
		    $newuserid = $this->Api_model->save();
			$OTP       = rand(1000, 9999);
			$bodymsg   = 'Thank you for registering with Ziqqi. To get started kindly enter otp ' . $OTP . '.
			Regards, 
			Team Ziqqi';
			$subject = 'Ziqqi - Otp Verification';
            #$statussms = $this->SendsmsOtp($OTP,$email,$contact_num,$name,$bodymsg);

           $otp_msg='Your OTP is :'.$OTP;
          $this->phone_otp('00'.$phonecode.$contact_num,$phonecode,$otp_msg);
         
          
            $statusmail = $this->email_otp($OTP,$email,$contact_num,$name,$bodymsg,$subject);
            
            
            if(!empty($statusmail)){
            $this->Api_model->setuserid($newuserid);
            $this->Api_model->setotp($OTP);
            $result = $this->Api_model->insertotp();
            }
			$otdetails = array('customer_id' => $newuserid,'otp' => $OTP);
			$this->Api_model->setuserid($newuserid);
			//fetch registered user details
			$customer = $this->Api_model->get_customer();
			
			#set customer id.
			$this->Api_model->setuserid($customer->id);
			
			$customerDevice = $this->Api_model->get_deciceDetailsByUserId();
			
			#get user authtoken.
			
			if($device_type != $customerDevice['device_type'])
			{
				$authtoken   = $this->Api_model->updateAuthToken();
			}else{
				$authtoken   = $this->Api_model->authtoken();
			}
			$customer->auth_token=$authtoken;
		
			if(!empty($newuserid))
			{
                $this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'registered successfully.','Payload'=>$customer,'otpdetails'=>$otdetails));				
            }else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Internal Error, Please try again.'));
            }  
        }
    }
	
    #THIS FUNCTION IS USING FOR SOCIAL SIGNUP.
	function socialSignup_post()
	{
	    
	    $phone='';
	    if(!empty($this->post('phone')))
	    {
	       $phone= $this->post('phone');
	    }
	
	   $email='';
	   if(!empty($this->post('email')))
	   {
	        $email=$this->post('email');
	   }
		$this->Api_model->setuserName($email);
		$this->Api_model->setfname($this->post('fname'));
		$this->Api_model->setlname($this->post('lname'));
		$this->Api_model->setphone($phone);
		$this->Api_model->setphonecode('');
		$this->Api_model->setDeviceId($this->post('device_id'));
		$this->Api_model->setDeviceType($this->post('device_type'));
		$this->Api_model->setGender('M');
		
		#set blank data in input  fileds.
		$this->Api_model->setEmailVerify(1);
		$this->Api_model->setPhoneVerify(1);
		$this->Api_model->setUserStatus(1);
		
		$lpginType  = $this->post('social_login_type');
		$socialId   = $this->post('social_id');
		$emailToken = md5(uniqid(microtime()) . $this->post('fname') . $this->post('lname'));
		
		$this->Api_model->setEmailToken($emailToken);
		$this->Api_model->setPhoneToken(rand(100000, 999999));
		
		if($lpginType == 'g')
		{
				$loginType='g';
				$this->Api_model->setGoogleOauth($socialId);
				$this->Api_model->setGoogleLogin(1);
				$this->Api_model->setpassword(md5('ziqqigoogle'));
				
				$this->Api_model->setFacebookLogin(0);
				$this->Api_model->setFacebookOauth('');	
		}
		if($lpginType == 'f')
		{
				$loginType='f';
			    $this->Api_model->setFacebookOauth($socialId);
				$this->Api_model->setFacebookLogin(1);
				$this->Api_model->setpassword(md5('ziqqifacebook'));
				
				$this->Api_model->setGoogleLogin(0);
				$this->Api_model->setGoogleOauth('');	
		}

      $postdata=array(
		     //   'email'                 =>  $this->post('email'),
				'fname'                 =>  $this->post('fname'),
				'lname'                 =>  $this->post('lname'),
				'device_id'             =>  $this->post('device_id'),
				'device_type'           =>  $this->post('device_type'),
				'social_login_type'     =>  $this->post('social_login_type'),
				'social_id'             =>  $this->post('social_id')
	
		);
		
		//$email        =  $this->post('email');
		$name         =  $this->post('fname');
		//$contact_num  =  $this->post('phone');
		$errorStr     =  $this->checkRequiredParam($postdata);
		$device_type=$this->post('device_type');
		
		#check mandatory field validation.
		if($errorStr)
		{
			$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else if(empty($email) && empty($phone))
		{
		    	$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Please fill either email or phone number','Payload'=>array()));
		}
		else{
			
			$flag = false;
			if($email != '')
			{
				$customerData1 =$this->Api_model->get_customer_by_email();
				
				$flag = false;
				if(count($customerData1)>0)
				{
					//update all data with 
					$customer['id'] = $customerData1['id'];
					
					$this->Api_model->setuserid($customer['id']);
					$customer_id = $this->Api_model->save();
					
					$this->Api_model->setDeviceId($this->post('device_id'));
					$this->Api_model->setDeviceType($this->post('device_type'));
					$this->Api_model->save_deviceDetails();
					
	
        			$customerDevice = $this->Api_model->get_deciceDetailsByUserId();
        			
        			#get user authtoken.
        			
        			if($device_type != $customerDevice['device_type'])
        			{
        				$authtoken   = $this->Api_model->updateAuthToken();
        			}else{
        				$authtoken   = $this->Api_model->authtoken();
        			}
					
					$customerData1['auth_token']  = $authtoken;
					//$this->response(array('status'=>1,'msg'=>'Login successfully.','userdata' =>$customerData1), 200);
					
					if(!empty($customer_id))
					{
						$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Login successfully.','Payload'=>$customerData1));
					}else{
						$this->response(array('Error'=>true,'Status'=>0,'Message' => 'something went wrong.','Payload'=>array()));
					}
					$flag = true;
				}
			
			}
			else
			{
				$customerData2 =$this->Api_model->get_customer_by_phone();
				
				
				$flag = false;
				if(count($customerData2)>0)
				{
				   
					//update all data with 
					$customer['id'] = $customerData2['id'];
					
					$this->Api_model->setuserid($customer['id']);
					$this->Api_model->save();
					
					$this->Api_model->setDeviceId($this->post('device_id'));
					$this->Api_model->setDeviceType($this->post('device_type'));
					$this->Api_model->save_deviceDetails();
					
					$customerDevice = $this->Api_model->get_deciceDetailsByUserId();
        			
        			#get user authtoken.
        			
        			if($device_type != $customerDevice['device_type'])
        			{
        				$authtoken   = $this->Api_model->updateAuthToken();
        			}else{
        				$authtoken   = $this->Api_model->authtoken();
        			}
					
					$customerData2['auth_token']  = $authtoken;
							
					$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Login successfully.','Payload'=>$customerData2));
					$flag = true;
				}
				
			}
		
			if(!$flag)
			{
	
			
			   $customer_id = $this->Api_model->save();
			 
			  
			    $this->Api_model->setuserid($customer_id);
				
				$OTP = rand(1000, 9999);
				$bodymsg = 'Thank you for registering with Ziqqi. To get started kindly enter otp ' . $OTP . '.
				Regards, 
				Team Ziqqi';
				$subject = 'Ziqqi - Otp Verification';
				
				#$statussms = $this->SendsmsOtp($OTP,$email,$contact_num,$name,$bodymsg);
				
				$statusmail = $this->SendmailOtp($OTP,$email,$contact_num,$name,$bodymsg,$subject);
				
				if(!empty($statusmail)){
				$this->Api_model->setotp($OTP);
				$result = $this->Api_model->insertotp();
				}
		
				#get customer details
			
				$customerData3	= $this->Api_model->get_customer();
			
		
				$this->Api_model->setDeviceId($this->post('device_id'));
				$this->Api_model->setDeviceType($this->post('device_type'));
				$this->Api_model->save_deviceDetails();
				
				$customerDevice = $this->Api_model->get_deciceDetailsByUserId();
        		
        			#get user authtoken.
        			
        			if($device_type != $customerDevice['device_type'])
        			{
        				$authtoken   = $this->Api_model->updateAuthToken();
        			}else{
        			$authtoken   = $this->Api_model->authtoken();
        			}
        			
					$customerData3->auth_token  = $authtoken;
				
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Login successfully.','Payload'=>$customerData3));
			}
		}
	}
	
	#THIS FUNCTION IS USING FOR CUSTOMER LOGIN.
    function login_post()
	{  
	    $username    = $this->post('username');
		$password    = $this->post('password');
	    $device_type = $this->post('device_type');
		$device_id   = $this->post('device_id');
	
		$postdata=array(
		            'username'    => $username,
					'password'    => $password,
					'device_type' => $device_type,
					'device_id'   => $device_id				
		        );
		
		#check mandatory input fields.
        $errorStr = $this->checkRequiredParam($postdata);
		if($errorStr)
		{
			$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr));
		}
		else
		{
	        #set username and password.
			$this->Api_model->setuserName($this->post('username'));
			$this->Api_model->setpassword($this->post('password').HASHTOKEN);
			
			#check customer by their username and password.
            $customer=$this->Api_model->customer_login();
          
            
            if(is_array($customer))
			{
          
    			#set customer id.
    			$this->Api_model->setuserid($customer['id']);
    			
    			$customerDevice = $this->Api_model->get_deciceDetailsByUserId();
    			#get user authtoken.
    			if($device_type != $customerDevice['device_type'])
    			{
    				$authtoken   = $this->Api_model->updateAuthToken();
    			}else{
    				$authtoken   = $this->Api_model->authtoken();
    			}
    		
    			#set customer device data.
    			$this->Api_model->setDeviceId($device_id);
    			$this->Api_model->setDeviceType($device_type);
    			
    			#save customer device details.
    		    $this->Api_model->save_deviceDetails();
               
    			#get customer device details.
    			$customerdetails = array(
    			                    'id'          =>   $customer['id'],
    								'first_name'  =>   $customer['first_name'],
    								'last_name'   =>   $customer['last_name'],
    								'gender'      =>   $customer['gender'],
    								'email'       =>   $customer['email'],
    								'mobile'      =>   $customer['mobile'],
    								'auth_token'  =>   $authtoken
    								);
    		
    				$this->response(array('Error'=>false,'Status'=>1,'Code'=>200,'Message' => 'Login successfully.','Payload'=>$customerdetails));
    				#200 being the HTTP response code   
            }
            else if($customer ==true)
			{
			 
			  $arrCustomer   = explode("#&#",$customer);
			 
			  $customer_id   = $arrCustomer[0];
			  $name          = $arrCustomer[1];
			  $contact_num   = $arrCustomer[2];
			  $phonecode     = $arrCustomer[3];
			  $email         = $arrCustomer[4];
			  
			 
			 
			 $OTP       = rand(1000, 9999);
			$bodymsg   = 'Thank you for registering with Ziqqi. To get started kindly enter otp ' . $OTP . '.
			Regards, 
			Team Ziqqi';
			$subject = 'Ziqqi - Otp Verification';
            #$statussms = $this->SendsmsOtp($OTP,$email,$contact_num,$name,$bodymsg);

           
            $this->phone_otp('00'.$phonecode.$contact_num,$phonecode,$OTP);
            $statusmail = $this->email_otp($OTP,$email,$contact_num,$name,$bodymsg,$subject);
            if(!empty($statusmail)){
                $this->Api_model->setuserid($customer_id);
                $this->Api_model->setotp($OTP);
                $result = $this->Api_model->insertotp();
            }
            
             $otpdetails=array('customer_id'=>$customer_id,'otp'=>$OTP);
				$this->response(array('Error'=>true,'Status'=>0,'Code'=>203,'Message' => 'Customer is not activated.','Payload'=>$otpdetails));
			}
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Code'=>204,'Message' => 'Please enter valid username and password.'));
            }  
        }
    }   
	
	#THIS FUNCTION IS USING FOR FORGOT PASSWORD.
	function forgot_password_post()
	{
	    $email          =  $this->input->post('email');
	    $otp_method     = 0;
	    $msg='Your verification code has been sent to your email.';
		if(!empty($this->input->post('otp_method')))
		{
			$otp_method = $this->input->post('otp_method');
			 $msg='Your verification code has been sent to your phone.';
		}
	
		$this->Api_model->setotpmethod($otp_method);
		$this->Api_model->setnewpassword($this->input->post('new_password'));
		$this->Api_model->setotp($this->input->post('otp'));
		
		
	    $postdata  =  array('email' => $email);
		
		#check mandatory input fields.
		$errorStr  =  $this->checkRequiredParam($postdata);
		
		$this->Api_model->setuserName($email);
		if($errorStr)
		{
            $this->response(array('Error'=>true,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr));
		
		}else if(!$this -> Api_model -> check_customer_email())
		{
			$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Email id is not registered.'));
        }else
		{
    	   $mail = $this->Api_model->reset_password();
    	    if($mail == 1)
			{
				//$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Your password has been sent to your email.'));
				
				$this->response(array('Error'=>false,'Status'=>1,'Message' => $msg));
			}
    	   if($mail=='otp_missmatched')
    	   {
    	       
    	       $this->response(array('Error'=>false,'Status'=>1,'Message' => 'OTP does not match.'));
    	   }else if($mail=='password_changed'){
    	       
    	       $this->response(array('Error'=>false,'Status'=>1,'Message' => 'Your password has been changed successfully.'));
    	   }
			
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Internal Error, Please try again.'));
			}
				
        }
	}

    #THIS FUNCTION IS USING FOR RESENT OTP.
	public function resendotp_post(){

	 	$customer_id  =  $this->input->post('customer_id');
	    //$auth_token   =  $this->input->post('auth_token');
	 	$postdata     =  array(
							'customer_id' => $customer_id,
						   //'auth_token'  => $auth_token
							);
	 	
	    //$this->Api_model->setauthtoken($auth_token);
		
		#check user auth token.
	    //$checkToken   = $this->Api_model->istokenExists();
		
		#check mandatory input fields.
		$errorStr     = $this->checkRequiredParam($postdata);
	
	 	if($errorStr)
		{
			$this->response(array('Error'=>true,'Code'=>200,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
			
		}/*else if($checkToken == 0)
		{
			$this->response(array('Error'=>false,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>''));
		}*/else{
			$this->Api_model->setuserid($customer_id);
			$result = $this->Api_model->checkexistinguserbyid();
		    if($result){
		        $this->Api_model->setuserid($customer_id);
		        $customerdetails    = $this->Api_model->getcustomerdetails();
		        $customerotpdetails = $this->Api_model->getuserotpdetails();
			   
			    $name               = $customerdetails['fname'];
			    $lastinsertid       = $customerdetails['id'];
			    $OTP                = $customerotpdetails['otp'];
			    $email              = $customerdetails['email'];
			    $contact_num        = $customerdetails['mobile'];
				$bodymsg = 'Thank you for registering with Ziqqi. To get started kindly enter otp ' . $OTP . '.
				   Regards, 
				   Team Ziqqi';
			  
			    $subject            = 'Ziqqi - Otp Verification';
			    #$statussms = $this->SendsmsOtp($OTP,$email,$contact_num,$name,$bodymsg);
			
			    $statusmail         = $this->SendmailOtp($OTP,$email,$contact_num,$name,$bodymsg,$subject);
				if(!empty($statusmail))
				{
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'otp sent successfully.','Payload'=>array('otp'=>$OTP)));				
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'otp not sent.','Payload'=>array()));
				}  
			}else{
			 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Customer not exist.','Payload'=>array()));
			} 
        }
    }

    #THIS FUNCTION IS USING FOR VAIFY OTP.
    public function verifyotp_post(){

	 	$customer_id = $this->input->post('customer_id');
	 	$otp         = $this->input->post('otp');
	  // $auth_token  = $this->input->post('auth_token');
		
	 	$postdata  =  array( 
					'customer_id' => $customer_id,
					'otp'         => $otp,
					//'auth_token'  => $auth_token
					);
	 	$errorStr  =  $this->checkRequiredParam($postdata);
		$this->Api_model->setauthtoken($auth_token);
		$checkToken=$this->Api_model->istokenExists();
	 	if($errorStr)
		{
			$this->response(array('Error'=>true,'Code'=>200,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		
		/*else if($checkToken == 0)
		{
			$this->response(array('Error'=>false,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>''));
		}*/
		
		else{
			$this->Api_model->setuserid($customer_id);
			$result = $this->Api_model->checkexistinguserbyid();
				if($result)
				{
				    $this->Api_model->setuserid($customer_id);
				    $customerdetails = $this->Api_model->getcustomerdetails();
				    $customerotpdetails = $this->Api_model->getuserotpdetails();
				  
					if(!empty($customerotpdetails) && $customerotpdetails['otp'] == $otp)
					{  
					    $this->Api_model->updatecustomer();
					    $contact_num = $customerdetails['mobile'];
					    $email       = $customerdetails['email'];
					    $name        = $customerdetails['first_name'];
					    $bodymsg     = 'Thank you for activating your account with Ziqqi.Regards, Team Ziqqi';
					    $subject     = 'Ziqqi - Login Mail';
					   // $statussms = $this->SendsmsOtp($otp,$email,$contact_num,$name,$bodymsg);
				
						$statusmail      = $this->email_otp($otp,$email,$contact_num,$name,$bodymsg,$subject);
						if ($statusmail) 
						{
							$authtoken   =  $this->Api_model->authtoken();
						} 
						$customerdetails = array(
										    'id'=>$customerdetails['id'],
										    'first_name'=>$customerdetails['first_name'],
											'last_name'=>$customerdetails['last_name'],
											'gender'=>$customerdetails['gender'],
											'email'=>$customerdetails['email'],
											'mobile'=>$customerdetails['mobile'],
											'auth_token'=>$authtoken
											);
						 $payload         = array($customerdetails);
						 $this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Thankyou customer has been verified.','Payload'=>$payload));		
					}else{
						$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please enter correct otp.','Payload'=>array()));
					}  
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Customer not exist.','Payload'=>array()));

				} 
        }
    }

    #THIS FUNCTION IS USING FOR GET FEEDBACK MASTER DATA.
	function feedback_master_get()
	{
	   
	    $feedbackmaster = $this->Api_model->getfeedbackmaster();
		
		if(!empty($feedbackmaster)){
			$response["Error"] = false;
			$response["Status"] = 1;
			$response["Message"] = "Feedback master sent successfully.";
			$response["Payload"] = $feedbackmaster;
			$this->response($response);
			}else{
			$response["Error"] = true;
			$response["Status"] = 1;
			$response["Message"] = "Some error in feedback.";
			$this->response($response);
			}
    }

    #THIS FUNCTION IS USING FOR GET LANGUAGE MASTER DATA.
    function language_master_get()
	{
		$languagemaster = $this->Api_model->getlanguagemaster();
		if(!empty($languagemaster)){
			$response["Error"] = false;
			$response["Status"] = 1;
			$response["Message"] = "Language master sent successfully.";
			$response["Payload"] = $languagemaster;
			$this->response($response);
			}else{
			$response["Error"] = true;
			$response["Status"] = 1;
			$response["Message"] = "Some error in language.";
			$this->response($response);
			}
    }

	#THIS FUNCTION IS USING FOR GET CURRENCY MASTER DATA.
	function currency_master_get()
	{
		$currencymaster = $this->Api_model->getcurrencymaster();
		if(!empty($currencymaster)){
			$response["Error"] = false;
			$response["Status"] = 1;
			$response["Message"] = "Currency master sent successfully.";
			$response["Payload"] = $currencymaster;
			$this->response($response);
			}else{
			$response["Error"] = true;
			$response["Status"] = 1;
			$response["Message"] = "Some error in currency master.";
			$this->response($response);
			}
	}

    #THIS FUNCTION IS USING FOR GET HOME BANNER.
	function home_banners_get()
	{
		
		#ger banners.
		$arrBanner = $this->Api_model->get_banners();
		
		#API response.
		if($arrBanner)
		{   $homebanner = array();
		    foreach($arrBanner as $element){
		    $homebanner[] = array( "id"=> $element['id'],
            "alt"=> $element['alt'],
            "image_path"=> PRODUCT_IMAGE.$element['image_path'],
            "link"=> $element['link'],
            "display_order"=> $element['display_banner']);
		    
		}
			$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$homebanner,'Code'=>200));
	    }
		else
		{
			$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
		}
	}

	#THIS FUNCTION IS USING FOR HOME MAIN CATEGRORY.
	function home_categories_post()
	{
	
		$subcatId    = $this->post('category_id');
		//$subcatId = $this->post('subcategory_id');
		
		#get category.
	    $arrCategories = $this->Api_model->homeCategory($catId=false, $subcatId);
	  
		$arrProducts   = array();
		if(count($arrCategories)>0)
		{
			foreach($arrCategories as &$value)
			{
			/*	if($value['parent_category_id'] !=0)
				{*/
					$arrsubcatg = $this->Api_model->homeCategory($catId=false, $value['id']);
				
					$subcat_Id='';
					if(count($arrsubcatg)>0)
					{
						foreach($arrsubcatg as $subcat)
						{
							$subcat_Id  .=$subcat['id'].",";
						}
					}
					$subcat_Id=rtrim($subcat_Id,",");
					
					$arrProducts=$this->Api_model->get_subcategoryproduct($subcat_Id);
				
			
					//$arrProducts=$this->Api_model->get_bestSellerProductBycatId($value['id']);
					if(count($arrProducts)>0)
					{
						foreach($arrProducts as &$prod)
						{
							#set product id.
							$text = strip_tags($prod['small_desc'], '<br><p><li>');
                            $text = preg_replace ('/<[^>]*>/', PHP_EOL, $text);
							$prod['small_desc'] = $text;
							$this->Api_model->setProductId($prod['product_id']);
							
							#set brand id.
							$this->Api_model->setBrandId($prod['brand_id']);
							
							#get product price.
							$arrProdPrice = $this->Api_model->get_productPriceById();
							
							$prod['mrp_price']  = "0.00";
							$prod['sale_price'] = "0.00";
							if(count($arrProdPrice)>0)
							{
								$prod['mrp_price']  = $arrProdPrice['mrp_price'];
								$prod['sale_price'] = $arrProdPrice['sale_price'];
								
							}
							#get product image.
							$arrProdImg   = $this->Api_model->get_productImageById();
							
							#get brand.
							$arrProdBrand = $this->Api_model->get_brandById();
							
							$prod['image']='';
							if(count($arrProdImg)>0)
							{
								$prod['image']=PRODUCT_IMAGE.$arrProdImg[0]['large_image_path'];
							}
							
						
        					
							$prod['brand_name']='';
							if(count($arrProdBrand)>0)
							{
								$prod['brand_name']=$arrProdBrand['name'];
							}
							
						}
					}
						
			/*	}*/
				$categoryimage = $this->Api_model->getcategoryimage($value['id']);
			
			$catImg='na.jpg';
			if($categoryimage['category_image'])
			{
			    	$catImg=$categoryimage['category_image'];
			}
				$value['category_image']    = HTTP_IMAGES_PATH.'category/'.$catImg;
				$value['bestsellerProduct'] = $arrProducts;
				$value['home_banner'] = PRODUCT_IMAGE.$value['home_banner'];
			
			}
		}
		$bannerImg='';
	   if($subcatId==1)
	   {
	       $bannerImg='banner_mobiles_tablets1.jpg';
	   }else if($subcatId==4)
	   {
	       $bannerImg='banner_computer1.jpg';
	   }else if($subcatId==6)
	   {
	       $bannerImg='banner_televisions_audio1.jpg';
	   }
	   else if($subcatId==7)
	   {
	       $bannerImg='banner_camera1.jpg';
	   }
	   else if($subcatId==18)
	   {
	       $bannerImg='banner_appliances1.jpg';
	   }
	   else if($subcatId==19)
	   {
	       $bannerImg='banner_gaming1.jpg';
	   }
	   else if($subcatId==275)
	   {
	       $bannerImg='banner_perfume1.jpg';
	   }
	   else if($subcatId==276)
	   {
	       $bannerImg='banner_pharmacy1.jpg';
	   }
	   else if($subcatId==297)
	   {
	       $bannerImg='banner_supermarket1.jpg';
	   }
	   
		#API Response.
		if(count($arrCategories)>0)
		{
			$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=> $arrCategories,'category_banner'=> HTTP_IMAGES_PATH.'banner_image/'.$bannerImg,'Code'=>200));
	    }
		else
		{
			$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
		}
	}

	#THIS FUNCTION IS USING FOR GET PRODUCT BY CATEGRORY.
	function getcategoryProduct_post()
	{
		$postdata  = array('category_id' =>  $this->post('category_id'));
		
		$errorStr  = $this->checkRequiredParam($postdata);
		$catId     = $this->post('category_id');
		
		$auth_token  = $this->post('auth_token');
		
		$this->Api_model->setauthtoken($auth_token);
		
		$customer    = $this->Api_model->getcustomerid();
		$customer_id = $customer['user_id'];
		#set data.
		$this->Api_model->setuserid($customer_id); 
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>false,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else{
			     $limit = 15; 
                 $start = 0;
                 $page = $this->input->post('page');
                 if(!is_numeric($page)){
                 $page = 1;
                 }else{
                 $page = $page;   
                 }
                 $start = ($page - 1)*$limit;
			#get product by category.
			$arrProducts=$this->Api_model->get_productBycatId($catId,$start,$limit);
			
			if(count($arrProducts)>0)
			{
				foreach($arrProducts as &$prod)
				{   
		    		$text = strip_tags($prod['small_desc'], '<br><p><li>');
                    $text = preg_replace ('/<[^>]*>/', PHP_EOL, $text);
					$prod['small_desc'] = $text;
					#set product id and brand id.
					$this->Api_model->setProductId($prod['id']);
					$this->Api_model->setProductType($prod['product_type']);
					$this->Api_model->setBrandId($prod['brand_id']);
					
					#get product image.
					
					$arrProdImg   = $this->Api_model->get_productImageById();
					
					#get brand.
					$arrProdBrand = $this->Api_model->get_brandById();
					
					#get product price.
					$arrProdPrice = $this->Api_model->get_productPriceById();
				
					$prod['mrp_price']  = "0.00";
					$prod['sale_price'] = "0.00";
					if(count($arrProdPrice)>0)
					{
						$prod['mrp_price']  = $arrProdPrice['mrp_price'];
					    $prod['sale_price'] = $arrProdPrice['sale_price'];
						
					}
					
					$prod['image'] = '';
					$arrImg        = array();
					$productImg    ='';
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							$arrImg[]              = PRODUCT_IMAGE.$img['large_image_path'];
							//$arrProducts['image']  = base_url($img['large_image_path']);
							if($img['large_image_path'])
    						{
    						   $productImg    =PRODUCT_IMAGE.$img['large_image_path'];
    						}
						}
						
						$prod['image']    = $productImg;
						
					}
					
					$prod['brand_name']='';
					if(count($arrProdBrand)>0)
					{
						$prod['brand_name'] = $arrProdBrand['name'];
					}
					
					#check wishlist products.
    				$isWishlist=0;
    				if($customer_id)
    				{
    					$countWishlist=$this->Api_model->count_wishlist();
    					if($countWishlist>0)
    					{
    						$isWishlist=1;
    					}
    				}
    				
    				$prod['is_wishlist']=$isWishlist;
				}
			}
		
			#API Response.
			if(count($arrProducts)>0)
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrProducts,'Code'=>200));
			}
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
			}
			
		}
	}

	#THIS FUNCTION IS USING FOR GET PRODUCT BY THEIR ID.
	function productDetails_post()
	{  
        $product_id = $this->input->post('product_id');
     
		$postdata  = array('product_id' =>  $product_id);
		
		$errorStr      = $this->checkRequiredParam($postdata);
		$productId     = $this->post('product_id');
		
		$auth_token  = $this->post('auth_token');
		
		$this->Api_model->setauthtoken($auth_token);
		
		$customer    = $this->Api_model->getcustomerid();
		$customer_id = $customer['user_id'];
	
		#set data.
		$this->Api_model->setuserid($customer_id); 
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else{
			
			$this->Api_model->setProductId($productId);
			
			#get product by category.
			$arrProducts=$this->Api_model->get_productById();
			
			
			if(count($arrProducts)>0)
			{      
                    $productfeatures=$this->Api_model->get_productfeatures();
                    
                  
				   if(!empty($productfeatures)){
				   foreach($productfeatures as $element){
				    $arrProducts['features'][] = array('label' => $element['flabel'],
				    'value' => $element['fvalue']
				    );   
				       
				   } 
				   }else{
				   $arrProducts['features'] = array();       
				   }
				
                 
				   
					#set product id and brand id.
					
					$this->Api_model->setProductType($arrProducts['product_type']);
					$this->Api_model->setBrandId($arrProducts['brand_id']);
					
					#get product image.
					
					$arrProdImg   = $this->Api_model->get_productImageById();
					
					#get brand.
					$arrProdBrand = $this->Api_model->get_brandById();
					
					#get product price.
					$arrProdPrice = $this->Api_model->get_productPriceById();
				
					$arrProducts['mrp_price']  = "0.00";
					$arrProducts['sale_price'] = "0.00";
					if(count($arrProdPrice)>0)
					{
						$arrProducts['mrp_price']=$arrProdPrice['mrp_price'];
					    $arrProducts['sale_price']=$arrProdPrice['sale_price'];
						
					}
					
					$arrProducts['image']='';
					$arrImg=array();
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{
							$arrImg[]                = PRODUCT_IMAGE.$img['large_image_path'];
							$arrProducts['image']    = $arrImg;
						}
						
					}
					$arrProducts['brand_name'] = '';
					if(count($arrProdBrand)>0)
					{
						$arrProducts['brand_name']=$arrProdBrand['name'];
					}
				
					$arrReview = $this->Api_model->get_productReview();
					$ratting   = 0;
					$ttlRating = 0;
					if(count($arrReview))
					{
						foreach($arrReview as $rate)
						{
							$ttlRating += $rate['rate_star'];
						}
					}
					$ratting=round($ttlRating/count($arrReview));
					
					$arrProducts['rating']       = $ratting;
					$arrProducts['total_review'] = count($arrReview);
					$arrProducts['reviews']      = $arrReview;
					
					#check wishlist products.
    				$isWishlist=0;
    				if($customer_id)
    				{
    					$countWishlist=$this->Api_model->count_wishlist();
    					if($countWishlist>0)
    					{
    						$isWishlist=1;
    					}
    				}
    				
    				$arrProducts['is_wishlist']=$isWishlist;
			}
		
			#API Response.
			if(count($arrProducts))
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrProducts,'Code'=>200));
			}
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
			}
			
		}
		
	}
	
    #THIS FUNCTION IS USING FOR GET PRODUCT BY HOME SEARCH.
	function categorysearch_post()
	{   
	
	    $searchname   = $this->post('searchname');
		$postdata     = array('searchname' =>  $searchname);
		$errorStr     = $this->checkRequiredParam($postdata);
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else{
			
			$this->Api_model->setProductname($searchname);
			
			#get product by category.
			$arrCategories=$this->Api_model->getproductbysearchname();
			if(count($arrCategories)>0)
			{
				foreach($arrCategories as $categories)
				{
				$searchresult[] = array('category_id' => $categories['id'],
				'category_name' => $categories['name']);
				}
			}
		
			#API Response.
			if(count($searchresult))
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$searchresult,'Code'=>200));
			}
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
			}
			
		}
		
	}

	#THIS FUNCTION IS USING FOR GET PRODUCT BY HOME SEARCH.
	function homesearch_post()
	{   $category_id   = $this->post('category_id');
		$postdata      = array('category_id' =>  $category_id);
		$errorStr      = $this->checkRequiredParam($postdata);
		
		$auth_token  = $this->post('auth_token');
		
		$this->Api_model->setauthtoken($auth_token);
		
		$customer    = $this->Api_model->getcustomerid();
		$customer_id = $customer['user_id'];
		#set data.
		$this->Api_model->setuserid($customer_id);
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else{
			
		         $limit = 15; 
                 $start = 0;
                 $page = $this->input->post('page');
                 if(!is_numeric($page)){
                 $page = 1;
                 }else{
                 $page = $page;   
                 }
                 $start = ($page - 1)*$limit;
			#get product by category.
			$arrProducts=$this->Api_model->get_productBycatId($category_id,$start,$limit);
			if(count($arrProducts)>0)
			{
				foreach($arrProducts as &$prod)
				{
					#set product id and brand id.
					$this->Api_model->setProductId($prod['id']);
					$this->Api_model->setProductType($prod['product_type']);
					$this->Api_model->setBrandId($prod['brand_id']);
					
					#get product image.
					
					$arrProdImg   = $this->Api_model->get_productImageById();
					
					#get brand.
					$arrProdBrand = $this->Api_model->get_brandById();
					
					#get product price.
					$arrProdPrice = $this->Api_model->get_productPriceById();
				
					$prod['mrp_price']  = "0.00";
					$prod['sale_price'] = "0.00";
					if(count($arrProdPrice)>0)
					{
						$prod['mrp_price']  = $arrProdPrice['mrp_price'];
					    $prod['sale_price'] = $arrProdPrice['sale_price'];
						
					}
					
					$prod['image'] = array();
					$arrImg        = array();
					
					$productImg='';
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							$arrImg[] = PRODUCT_IMAGE.$img['large_image_path'];
							//$arrProducts['image']  = base_url($img['large_image_path']);
							if($img['large_image_path'])
							{
							    $productImg=PRODUCT_IMAGE.$img['large_image_path'];
							}
						}
						//$prod['image']    = $arrImg;
						$prod['image']    = $productImg;
					}
					
					$prod['brand_name']='';
					if(count($arrProdBrand)>0)
					{
						$prod['brand_name'] = $arrProdBrand['name'];
					}
					
					#check wishlist products.
    				$isWishlist=0;
    				if($customer_id)
    				{
    					$countWishlist=$this->Api_model->count_wishlist();
    					if($countWishlist>0)
    					{
    						$isWishlist=1;
    					}
    				}
    				
    				$prod['is_wishlist']=$isWishlist;
					
				}
			}
		
			#API Response.
			if(count($arrProducts))
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrProducts,'Code'=>200));
			}
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
			}
			
		}
		
	}


	#THIS FUNCTION IS USING FOR ADD TO PRODUCTS IN CART.
    function addTocart_post()
	{
		$product_id          = $this->post('product_id');
		$product_variant_id  = $this->post('product_variant_id');
		$auth_token          = $this->post('auth_token');
		$quantity            = $this->post('quantity');
		$postdata  = array(
		                'auth_token'         =>  $auth_token,
						'product_id'         =>  $product_id,
						'quantity'           =>  $quantity
						);
		
		
		$errorStr      = $this->checkRequiredParam($postdata);
		$this->Api_model->setauthtoken($auth_token);
	
	    $checkToken    = $this->Api_model->istokenExists();
	  
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else{
		
		
		    $customer      = $this->Api_model->getcustomerid();
		    $customer_id  = $customer['user_id'];
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setGuestId('');
			$this->Api_model->setProductVariant('');
			$this->Api_model->setProductId($product_id);
			$this->Api_model->setCartId('');
			
			
	        $cartExitProduct=$this->Api_model->get_cartExitProduct();
			if(count($cartExitProduct)>0)
			{
				$quantity +=$cartExitProduct['qty'];
				
				$this->Api_model->setCartId($cartExitProduct['id']);
			}
			
			
			$this->Api_model->setProductQuantity($quantity);
			
			
			$arrProduct=$this->Api_model->get_productById();
			
			
			
			if(count($arrProduct)>0)
			{
				#add product in cart.
			    $cartResult=$this->Api_model->add_to_cart();
			
				#get user cart data.
				$cartData=$this->Api_model->get_cartProduct();
				
				#get product price.
				
				$cartData['mrp_price']."==".$cartData['sale_price'];
				
				if(count($cartData)>0)
				{
					foreach($cartData as &$pod)
					{
						
						$this->Api_model->setProductId($pod['id']);
						$arrProdPrice = $this->Api_model->get_productPriceById();
						
						$pod['mrp_price']  = "0.00";
						$pod['sale_price'] = "0.00";
						if(count($arrProdPrice)>0)
						{
							$pod['mrp_price']  = $arrProdPrice['mrp_price'];
							$pod['sale_price'] = $arrProdPrice['sale_price'];
							
						}
					}
				}
			
				$cart_item=$this->Api_model->count_cart_item();
			
				#api response.
				if($cartResult==true){
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Product is added to cart','Payload'=>$cartData,'total_item'=>$cart_item));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong','Payload'=>array()));
				}
				
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'This product not found.','Payload'=>array()));
			}
			
		}
	}
	
	#THIS FUNCTION IS USING FOR VIEW CART PRODUCT.

 function viewCartProduct_post(){

	    $auth_token  = $this->post('auth_token');
		$postdata    = array( 'auth_token'   =>  $auth_token );
	
	    $this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
	    $checkToken    = $this->Api_model->istokenExists();
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else{

                $customer      = $this->Api_model->getcustomerid();
				$customer_id  = $customer['user_id'];
				
				#set data.
				$this->Api_model->setuserid($customer_id);
			
				#get user cart data.
				$total      = 0;
				$sub_total  = 0;
				$shipping   = 0;
				$cartData=$this->Api_model->get_cartProduct();
			
				if(count($cartData)>0)
				{
					foreach($cartData as &$prod)
					{
						#set product id and brand id.
						$this->Api_model->setProductId($prod['id']);
						$this->Api_model->setBrandId($prod['brand_id']);
						
						#get product image.
						
						$arrProdImg   = $this->Api_model->get_productImageById();
					
						
						#get brand.
						$arrProdBrand = $this->Api_model->get_brandById();
						
						#get product price.
						$arrProdPrice = $this->Api_model->get_productPriceById();
						
						#get product variant
						$arrProdcuVarient=$this->Api_model->get_productvariantById();
						

						$prod['mrp_price']  = "0.00";
						$prod['sale_price'] = "0.00";
						if(count($arrProdPrice)>0)
						{
							$prod['mrp_price']  = $arrProdPrice['mrp_price'];
							$prod['sale_price'] = $arrProdPrice['sale_price'];
							
						}
						
						
						$total += $prod['sale_price']*$prod['qty'];
						$prod['image']='';
						if(count($arrProdImg)>0)
						{
							$prod['image']=PRODUCT_IMAGE.$arrProdImg[0]['large_image_path'];
						}
						$prod['brand_name']='';
						if(count($arrProdBrand)>0)
						{
							$prod['brand_name']=$arrProdBrand['name'];
						}
						
						$prod['product_variant_id']='';
						if(count($arrProdcuVarient))
						{
							$prod['product_variant_id']=$arrProdcuVarient['product_variant_id'];
						}
					}
				}
			
			    $cart_item=$this->Api_model->count_cart_item();
				#api response.
				if(count($cartData)>0){
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Cart Product is fetched successfully.','Payload'=>$cartData,'total'=>$total,'sub_total'=>$total,'shipping'=>$shipping,'total_item'=>$cart_item));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have product in cart.','Payload'=>array()));
				}
		/*	}else{
				$this->response(array('Error'=>false,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>''));
			}*/
			
		}
	}
	function cart_data()
	{
		$cartData   = $this->Api_model->get_cartProduct();
		$total      = 0;
		$sub_total  = 0;
		$shipping   = 0;
		if(count($cartData)>0)
		{
			foreach($cartData as &$prod)
			{
				#set product id and brand id.
				$this->Api_model->setProductId($prod['id']);
				$this->Api_model->setBrandId($prod['brand_id']);
				
				#get product image.
				
				$arrProdImg   = $this->Api_model->get_productImageById();
				
				#get brand.
				$arrProdBrand = $this->Api_model->get_brandById();
				
				#get product price.
				$arrProdPrice = $this->Api_model->get_productPriceById();
				
				#get product variant
				$arrProdcuVarient=$this->Api_model->get_productvariantById();
				

				$prod['mrp_price']  = "0.00";
				$prod['sale_price'] = "0.00";
				if(count($arrProdPrice)>0)
				{
					$prod['mrp_price']  = $arrProdPrice['mrp_price'];
					$prod['sale_price'] = $arrProdPrice['sale_price'];
					
				}
				
				
				$total += $prod['sale_price']*$prod['qty'];
				$prod['image']='';
				if(count($arrProdImg)>0)
				{
					$prod['image']=PRODUCT_IMAGE.$arrProdImg['large_image_path'];
				}
				$prod['brand_name']='';
				if(count($arrProdBrand)>0)
				{
					$prod['brand_name']=$arrProdBrand['name'];
				}
				$prod['product_variant_id']='';
				if(count($arrProdcuVarient))
				{
					$prod['product_variant_id']=$arrProdcuVarient['product_variant_id'];
				}
			}
			$cartData['total']     =$total;
			$cartData['sub_total'] =$total;
			$cartData['shipping']  =$shipping;
		}
		return $cartData;
	}
	#THIS FUNCTION IS USING FOR DELTE CART PRODUCT.

    function deleteCartProduct_post()
	{
		$auth_token          = $this->post('auth_token');
		$postdata  = array('auth_token'   =>  $auth_token );
	
		$errorStr      = $this->checkRequiredParam($postdata);
		$this->Api_model->setauthtoken($auth_token);
	
	    $checkToken    = $this->Api_model->istokenExists();
	  
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			
			$customer      = $this->Api_model->getcustomerid();
			$customer_id   = $customer['user_id'];
			
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setProductId($this->post('product_id'));
			
		
			
			#delete cart product.
			$result=$this->Api_model->delete_cartProduct();
			
			$cart_item=$this->Api_model->count_cart_item();
			
			 $cartData=$this->Api_model->get_cartProduct();
		
			
			if($result==true)
			{  
	
			  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Cart product is deleted successfully','Payload'=>$cartData,'total_item'=>$cart_item));
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong.','Payload'=>array()));
			}
		}
	}
    #THIS FUNCTION IS USING FOR GET BILLING ADDRESS.
	function getBillingAddress_post(){
		
		$auth_token = $this->post('auth_token');
		$postdata   = array( 'auth_token'   =>  $auth_token);
	
	    $this->Api_model->setauthtoken($auth_token);
		
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>new stdClass()));
		
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>new stdClass()));
		}
		else
		{
			
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setAddressPrimary($this->post('is_primary'));
			$this->Api_model->setAddressId($this->post('address_id'));
	
			#get customer.
			$arrCustomer=$this->Api_model->get_customer();
			
			#check customer.
			if(count($arrCustomer)>0)
			{
				#get customer bill address.
				$result =$this->Api_model->get_billingAddress();
				
				/*if(count($result)==0)
				{
				  $result = $arrCustomer;
				}*/
				
				#api response.
				if($result==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer billing address fetched successfully.','Payload'=>$result));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>new stdClass()));
				}
			}else
			{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>new stdClass()));
			}
			
			
		}
	}
	
	#THIS FUNCTION IS USING FOE GET SHIPPING ADDRESS.
    function getShippingAddress_post(){
		
		$auth_token = $this->post('auth_token');
		$postdata   = array( 'auth_token'   =>  $auth_token );
	
	    $this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setAddressPrimary($this->post('is_primary'));
			$this->Api_model->setAddressId($this->post('address_id'));
	
			#get customer.
			$arrCustomer=$this->Api_model->get_customer();
			
			#check customer.
			if(count($arrCustomer)>0)
			{
				#get customer shipping address.
				$result =$this->Api_model->get_shippingAddress();
				
				/*if(count($result)==0)
				{
				  $result = $arrCustomer;
				}*/
				
				#api response.
				if($result==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer shipping address fetched successfully.','Payload'=>$result));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}else
			{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
			
			
		}
	}

    #THIS FUNCTION IS USING FOR ADD BILLING ADDRESS.
    function addBillingAddress_bck_post(){
		$auth_token = $this->post('auth_token');
		$postdata   = array(
						  //'email'                =>  $this->post('email'),
						  'first_name'           =>  $this->post('first_name'),
						  'last_name'            =>  $this->post('last_name'),
						  'mobile'               =>  $this->post('mobile'),
						  'country'              =>  $this->post('country'),
						  'address_details'      =>  $this->post('address_details'), 
						  'auth_token'           =>  $this->post('auth_token')
						  
						  
		                  );
	
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$isPrimary     = 0;
		if(!empty($this->post('is_primary')))
		{
			$isPrimary = 1;
		}
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		else if($checkToken == 0)
		{
            $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'you do not have authentication.','Payload'=>array()));
		}
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
		  
			
			#set data.
			$this->Api_model->setuserid($customer_id); 
			$this->Api_model->setfname($this->post('first_name'));
			$this->Api_model->setlname($this->post('last_name'));
			$this->Api_model->setphone($this->post('mobile'));
			$this->Api_model->setphone('');
			$this->Api_model->setuserName('');
			$this->Api_model->setAddressDetails($this->post('address_details'));
			$this->Api_model->setCountry($this->post('country'));
			$this->Api_model->setAddressPrimary($isPrimary);
			$this->Api_model->setAddressId($this->post('address_id'));
			
			#blank input fields.
			$this->Api_model->setphonecode('');
			$this->Api_model->setAddress1('');
			$this->Api_model->setAddress2('');
			$this->Api_model->setCity('');
			$this->Api_model->setState('');
			$this->Api_model->setPincode('');
			$this->Api_model->setPhoneProvider('');
			$this->Api_model->setPayMobile('');
		
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			if(count($arrCustomer)>0)
			{
				#save billing address.
				$address_id  = $this->Api_model->saveCustomerAddress();
			
				if($isPrimary == 1)
				{
					#get customer addresses.
					$arrAdrss = $this->Api_model->get_customerBillingAdress();
					
					if(count($arrAdrss)>0)
					{
						foreach($arrAdrss as $addrss)
						{
							if($addrss['id'] != $address_id)
							{
								#set update data.
								$update['id']         = $addrss['id'];
								$update['is_primary'] = 0;
								
								#update customer primary address status.
								$this->Api_model->updatePrimaryBillingAddrss($update);
								
							}
						}
					}
					
				}
				
				#set address id.
				$this->Api_model->setAddressId($address_id);
				
				#get billing address.
				$result      = $this->Api_model->get_billingAddress();
			
				#api response.
				if($result==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer billing address added successfully','Payload'=>$result));
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
			
		}
	} 
	
	 function addBillingAddress_post(){
		
		$auth_token = $this->post('auth_token');
		$postdata   = array(
						  'first_name'           =>  $this->post('first_name'),
						  'last_name'            =>  $this->post('last_name'),
						  'mobile'               =>  $this->post('mobile'),
						  'country'              =>  $this->post('country'), 
						  //'city'                 =>  $this->post('city'),
						 // 'location'             =>  $this->post('location'),
						  'address'              =>  $this->post('address'),
						  'auth_token'           =>  $auth_token
		                  );
	
	    $city='';
	    if(!empty($this->post('city')))
	    {
	        $city=$this->post('city');
	    }
	    $location='';
	    if(!empty($this->post('location')))
	    {
	         $location=$this->post('location');
	    }
	    
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$isPrimary     = 0;
		if(!empty($this->post('is_primary')))
		{
			$isPrimary = 1;
		}
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}
		else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			$this->Api_model->setuserid($customer_id); 
			$arrBillingAdds=$this->Api_model->get_billingAddress();
			
			$address_id=$this->post('address_id');
			
			
			$success_msg="Address added successfully.";
			
			if(count($arrBillingAdds)>0)
			{
				$address_id=$arrBillingAdds['id'];
					$success_msg="Address updated successfully.";
			}

		    #set data.
			
			$this->Api_model->setfname($this->post('first_name'));
			$this->Api_model->setlname($this->post('last_name'));
			$this->Api_model->setphone($this->post('mobile'));
			$this->Api_model->setCountry($this->post('country'));
			$this->Api_model->setAddressPrimary($isPrimary);
			$this->Api_model->setAddressId($address_id);
			$this->Api_model->setAddress1($this->post('address'));
			$this->Api_model->setCity($city);
			$this->Api_model->setLocation($location);
			
			#set blank data in input fields.
			//$this->Api_model->setlname('');
			$this->Api_model->setphonecode('');
			$this->Api_model->setuserName('');
			$this->Api_model->setAddressDetails($this->post('address'));
			$this->Api_model->setAddress2('');
			$this->Api_model->setCity($city);
			$this->Api_model->setState('');
			$this->Api_model->setPincode('');
			$this->Api_model->setPhoneProvider('');
			$this->Api_model->setPayMobile('');
		
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			
			if(count($arrCustomer)>0)
			{
				#save billing address.
			    $address_id  = $this->Api_model->saveCustomerAddress();
			 
				#set address id.
				$this->Api_model->setAddressId($address_id);
				
				#add primary shipping address.
				if($isPrimary == 1)
				{
					#get customer addresses.
					$arrAdrss = $this->Api_model->get_customerBillingAdress();
					
					if(count($arrAdrss)>0)
					{
						foreach($arrAdrss as $addrss)
						{
							if($addrss['id'] != $address_id)
							{
								#set update data.
								$update['id']         = $addrss['id'];
								$update['is_primary'] = 0;
								
								#update customer primary address status.
								$this->Api_model->updatePrimaryBillingAddrss($update);
								
							}
						}
					}
					
				}
			
			
			    #get billing address.
				$result      = $this->Api_model->get_billingAddress();
				
				#api response.
				if($result==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => $success_msg,'Payload'=>$result));
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			} 
		}
	}
	
	#THIS FUNCTION IS USING FOR ADD SHIPPING ADDRESS.
     function addShippingAddress_post(){
		
		$auth_token = $this->post('auth_token');
		$postdata   = array(
						  'name'                 =>  $this->post('name'),
						  'mobile'               =>  $this->post('mobile'),
						  'country'              =>  $this->post('country'), 
						  'city'                 =>  $this->post('city'),
						  'location'             =>  $this->post('location'),
						  'address'              =>  $this->post('address'),
						  'auth_token'           =>  $auth_token
		                  );
	
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$isPrimary     = 0;
		if(!empty($this->post('is_primary')))
		{
			$isPrimary = 1;
		}
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}
		else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			
	        $this->Api_model->setuserid($customer_id); 
			
			
			$arrShipAdds =$this->Api_model->get_shippingAddress();
			
			$address_id=$this->post('address_id');
			
			$success_msg="Address added successfully.";
			if(count($arrShipAdds)>0)
			{
				$address_id=$arrShipAdds['id'];
				$success_msg="Address updated successfully.";
			}
			
		    #set data.
			
			$this->Api_model->setfname($this->post('name'));
			$this->Api_model->setphone($this->post('mobile'));
			$this->Api_model->setCountry($this->post('country'));
			$this->Api_model->setAddressPrimary($isPrimary);
			$this->Api_model->setAddressId($address_id);
			$this->Api_model->setAddress1($this->post('address'));
			$this->Api_model->setCity($this->post('city'));
			$this->Api_model->setLocation($this->post('location'));
			
			#set blank data in input fields.
			$this->Api_model->setlname('');
			$this->Api_model->setphonecode('');
			$this->Api_model->setuserName('');
			$this->Api_model->setAddressDetails('');
			$this->Api_model->setAddress2('');
			//$this->Api_model->setCity('');
			$this->Api_model->setState('');
			$this->Api_model->setPincode('');
			$this->Api_model->setPhoneProvider('');
			$this->Api_model->setPayMobile('');
		
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			if(count($arrCustomer)>0)
			{
				#save shipping address.
			    $address_id  = $this->Api_model->saveShippingAddress();
				
				#set address id.
				$this->Api_model->setAddressId($address_id);
				
				#get shipping address.
				$result      = $this->Api_model->get_shippingAddressById();
				
				#add primary shipping address.
				if($isPrimary == 1)
				{
					#get customer addresses.
					$arrAdrss = $this->Api_model->get_customerShippingAdress();
					
					if(count($arrAdrss)>0)
					{
						foreach($arrAdrss as $addrss)
						{
							if($addrss['id'] != $address_id)
							{
								#set update data.
								$update['id']         = $addrss['id'];
								$update['is_primary'] = 0;
								
								#update customer primary address status.
								$this->Api_model->updatePrimaryShippingAddrss($update);
								
							}
						}
					}
					
				}
			
				#api response.
				if($result==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => $success_msg,'Payload'=>$result));
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			} 
		}
	}
    
    #THIS FUNCTION USING FOR ADD CUSTOMER FEEDBACK.
    function addCustomerFeedback_post()
	{
		$auth_token  = $this->post('auth_token');
		$arrRatting  = $this->post('ratting');
		
		$postdata    = array(
							'auth_token'   =>  $auth_token
							);
	
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		
		$checkToken    = $this->Api_model->istokenExists();
        $arrFeedback   = $this->Api_model->getfeedbackmaster(); 
		//$arrCustomerFeeds   = $this->Api_model->get_customerFeedbacks();
	
		#data validation.
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}else if(count($this->post('ratting'))==0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please enter feedbacks ratting.','Payload'=>array()));
		}
		// else if( count($arrFeedback) != count(!arrCustomerFeeds))
		// {

			// $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Number of feedback and ratting does not match.','Payload'=>array()));
		// }
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
	        #set customer id.
	        $this->Api_model->setuserid($customer_id);
			
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			
			if(count($arrCustomer)>0)
			{

				if(count($arrFeedback)>0)
				{
					$i=0;
				
					foreach($arrFeedback as $feed)
					{
						#set data.
						$this->Api_model->setFeedbackId($feed['id']);
						
						$this->Api_model->setFeedbackRatting(0);
						
						foreach ($arrRatting as $key => $rate)
						{
	
							if($feed['id']==$key)
							{
								$this->Api_model->setFeedbackRatting($rate);
							}
						}
						
						$this->Api_model->setCustomerFeedbackId('');
						
						#check customer feedback.
						$arrCustomerFeedback = $this->Api_model->get_customerFeedbckById();
						
						if(count($arrCustomerFeedback)>0)
						{
							$this->Api_model->setCustomerFeedbackId($arrCustomerFeedback['id']);
						}
						
						#save customer feedback.
						
					    $this->Api_model->saveCustomerFeedback();
						
					    $i++;
					}
					
					#get customer feedback.
					$result      =  $this->Api_model->get_customerFeedback();
				
					#api response.
					if($result==true)
					{  
					  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer feedback added successfully','Payload'=>$result));
					}else{
					 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
					}
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Feedback not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
	    }
    }
	
	#THIS FUNCTION IS USING FOR GET CUSTOMER FEEDBACKS.
	function getCustomerFeedback_post(){

		$auth_token  = $this->post('auth_token');
		$postdata    = array(
							'auth_token'   =>  $auth_token
						    );
		
		
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
	
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
		  
			#set data.
		    $this->Api_model->setuserid($customer_id); 
		
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			if(count($arrCustomer)>0)
			{
				#get customer feedback.
				$result  =  $this->Api_model->get_customerFeedback();
				
				#api response.
				if($result==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer feedback fetched successfully','Payload'=>$result));
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
	    }
	}
	
	#THIS FUNCTION IS USING FOR ADD WISHLIST.
    function addTowishlist_post(){
		$productId     =  $this->post('product_id');
		$auth_token    =  $this->post('auth_token');
		$postdata      = array(
							'product_id'         =>  $productId,
							'auth_token'         =>  $auth_token
					     	);
		
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		
	   
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else{
		    $customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setProductId($this->post('product_id'));
			
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			if(count($arrCustomer)>0)
			{
			    $arrProduct=$this->Api_model->get_productById();
				if(count($arrProduct)>0)
				{
					
					#add product in cart.
					 $wishListResult=$this->Api_model->add_to_wishlist();
					 
					#get user cart data.
					$wishlistData=$this->Api_model->get_wishlistProduct();
					
					$wishlistItem=$this->Api_model->count_wishlist_item();
					if($wishlistData)
					{
						foreach($wishlistData as &$prod)
						{
							#set product id and brand id.
							$this->Api_model->setProductId($prod['id']);
							$this->Api_model->setBrandId($prod['brand_id']);
							
							#get product image.
							
							$arrProdImg   = $this->Api_model->get_productImageById();
							
							#get brand.
							$arrProdBrand = $this->Api_model->get_brandById();
							
							#get product price.
							$arrProdPrice = $this->Api_model->get_productPriceById();
							
							#get product variant
							$arrProdcuVarient=$this->Api_model->get_productvariantById();
							

							$prod['mrp_price']  = "0.00";
							$prod['sale_price'] = "0.00";
							if(count($arrProdPrice)>0)
							{
								$prod['mrp_price']  = $arrProdPrice['mrp_price'];
								$prod['sale_price'] = $arrProdPrice['sale_price'];
								
							}
						
							$prod['image']='';
							if(count($arrProdImg)>0)
							{
								$prod['image']=PRODUCT_IMAGE.$arrProdImg[0]['large_image_path'];
							}
							$prod['brand_name']='';
							if(count($arrProdBrand)>0)
							{
								$prod['brand_name']=$arrProdBrand['name'];
							}
							$prod['product_variant_id']='';
							if(count($arrProdcuVarient))
							{
								$prod['product_variant_id']=$arrProdcuVarient['product_variant_id'];
							}
						}
					}
					
					
					#api response.
					if($wishListResult==true){
						$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Product is added to wishlist','Payload'=>$wishlistData,'wishlist_item'=>$wishlistItem));
					}else{
						$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong','Payload'=>array()));
					}
					
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Product not found.','Payload'=>array()));
				}
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Customer not found.','Payload'=>array()));
			}
		}
	}
	
	#THIS FUNCTION IS USING FOR VIEW CUSTOMER WISHLIST PRODUCT.
	function veiwWishlistProduct_post()
	{
		$auth_token     = $this->post('auth_token');
		$postdata       = array( 'auth_token'   =>  $auth_token );
		
		#check mandatory field validation.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();

		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}else{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
			$this->Api_model->setuserid($customer_id);
		
			#get user cart data.
		
			$wishlistData=$this->Api_model->get_wishlistProduct();
			
			$wishlistItem=$this->Api_model->count_wishlist_item();
			
			if(count($wishlistData)>0)
			{
				foreach($wishlistData as &$prod)
			    {
					#set product id and brand id.
					$this->Api_model->setProductId($prod['id']);
					$this->Api_model->setBrandId($prod['brand_id']);
					
					#get product image.
					
					$arrProdImg   = $this->Api_model->get_productImageById();
					
					#get brand.
					$arrProdBrand = $this->Api_model->get_brandById();
					
					#get product price.
					$arrProdPrice = $this->Api_model->get_productPriceById();
					
					#get product variant
					$arrProdcuVarient=$this->Api_model->get_productvariantById();
					

					$prod['mrp_price']  = "0.00";
					$prod['sale_price'] = "0.00";
					if(count($arrProdPrice)>0)
					{
						$prod['mrp_price']  = $arrProdPrice['mrp_price'];
						$prod['sale_price'] = $arrProdPrice['sale_price'];
						
					}

					$prod['image']='';
					if(count($arrProdImg)>0)
					{
						$prod['image']=PRODUCT_IMAGE.$arrProdImg[0]['large_image_path'];
					}
					
					$prod['brand_name']='';
					if(count($arrProdBrand)>0)
					{
						$prod['brand_name']=$arrProdBrand['name'];
					}
					
					$prod['product_variant_id']='';
					if(count($arrProdcuVarient))
					{
						$prod['product_variant_id']=$arrProdcuVarient['product_variant_id'];
					}
				}
			}
			#api response.
			if(count($wishlistData)>0){
				$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Wishlist product is feched successfully.','Payload'=>$wishlistData,'wishlist_item'=>$wishlistItem));
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'No Product in the wishlist','Payload'=>array()));
			}
		}
	}
	
	#THIS FUNCTION IS USING FOR DELETE WISHLIST PRODUCT.
	function deleteWishlistProduct_post(){
		
		$auth_token     = $this->post('auth_token');
		$postdata       = array('auth_token'   =>  $auth_token );
		
		#check mandatory field validation.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		
		$productId     = '';
		if(!empty($this->post('product_id')))
		{
			$productId = $this->post('product_id');
		}
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		else if($checkToken == 0 )
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}else
		{   
		    $customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
		    $this->Api_model->setuserid($customer_id);
			$this->Api_model->setProductId($productId);
			
			#delete cart product.
			$result=$this->Api_model->delete_wishlistProduct();
			
			$wishlistItem=$this->Api_model->count_wishlist_item();
			
			if($result==true)
			{  
		      $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Wishlist product is deleted successfully','Payload'=>array(),'wishlist_item'=>$wishlistItem));
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong.','Payload'=>array()));
			}
		}
	}
	
	
   

    #THIS FUNCTION IS USING FOR SEND OTP EMAIL.
    
    private function email_otp($otp,$email,$contact_num,$name,$bodymsg,$subject){
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		// More headers
		$headers .= 'From: <info@idukaan.ae>' . "\r\n";
		$headers .= 'Cc: info@idukaan.ae' . "\r\n";
		mail($email,$subject,$bodymsg,$headers);
		$success = 'success';
		return $success;

    }
	private function phone_otp($mobileno,$code,$message)
    {
        $message = urlencode($message);
        
          $url ="https://esahal.com/idukaan2333/sms.php?acct=idukaan2333&user=idukaanzqi&password=ZvaRnmiC3c&ref=".$code."&to=".$mobileno."&msg=".$message;
 
            //https://esahal.com/idukaan2333/sms.php?acct=idukaan2333&user=idukaanzqi&password=ZvaRnmiC3c&ref=123456&to=971551227712&msg=test
            // create a new cURL resource
            $ch = curl_init();
            
            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
           // curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // grab URL and pass it to the browser
            curl_exec($ch);
            
            // close cURL resource, and free up system resources
            curl_close($ch);
    }
    private function SendmailOtp($otp,$email,$contact_num,$name,$bodymsg,$subject){
        #Pear Mail Libraryhi
     
		$ci = get_instance();
		$ci->load->library('email');
		$config['protocol'] = "smtp";
		$config['smtp_host'] = "ssl://smtp.gmail.com";
		$config['smtp_port'] = "465";
		$config['smtp_user'] = "ajathtesting@gmail.com"; 
		$config['smtp_pass'] = "nisar@12345";
		$config['charset'] = "utf-8";
		$config['mailtype'] = "html";
		$config['newline'] = "\r\n";

		$ci->email->initialize($config);

		$ci->email->from('info@ziqqi.com', 'Ziqqi');
		$ci->email->to($email);
		$ci->email->reply_to('ajathtesting@gmail.com', 'Explendid Videos');
		$ci->email->subject($subject);
		$ci->email->message($bodymsg);
		$send=$ci->email->send();
		
		$success = 'success';
		return $success;

    }
    
    #THIS FUNCTION IS USING FOR GET PRODUCT BY HOME SEARCH.
	function similar_products_post()
	{   $product_id   = $this->post('product_id');
		$postdata      = array('product_id' =>  $product_id);
		$errorStr      = $this->checkRequiredParam($postdata);
		
		
		$auth_token  = $this->post('auth_token');
		
		$this->Api_model->setauthtoken($auth_token);
		
		$customer    = $this->Api_model->getcustomerid();
		$customer_id = $customer['user_id'];
		#set data.
		$this->Api_model->setuserid($customer_id); 
		
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else{
			
			$this->Api_model->setProductid($product_id);
			
			#get product by category.
		    //$arrProducts=$this->Api_model->get_productById();
			$categories=$this->Api_model->getproductcategory();
		    foreach($categories as $element){
		    $categories_id[] = $element['categories_id'];     
		    }
		         $limit = 15; 
                 $start = 0;
                 $page = $this->input->post('page');
                 if(!is_numeric($page)){
                 $page = 1;
                 }else{
                 $page = $page;   
                 }
                 $start = ($page - 1)*$limit;
		    $arrsimilarProducts=$this->Api_model->get_similarproducts($categories_id,$start,$limit);
			if(count($arrsimilarProducts)>0)
			{
				foreach($arrsimilarProducts as &$prod)
				{
					#set product id and brand id.
					$this->Api_model->setProductId($prod['id']);
					$this->Api_model->setProductType($prod['product_type']);
					$this->Api_model->setBrandId($prod['brand_id']);
					
					#get product image.
					
					$arrProdImg   = $this->Api_model->get_productImageById();
					#get brand.
					$arrProdBrand = $this->Api_model->get_brandById();
					
					#get product price.
					$arrProdPrice = $this->Api_model->get_productPriceById();
				
					$prod['mrp_price']  = "0.00";
					$prod['sale_price'] = "0.00";
					if(count($arrProdPrice)>0)
					{
						$prod['mrp_price']  = $arrProdPrice['mrp_price'];
					    $prod['sale_price'] = $arrProdPrice['sale_price'];
						
					}
					
					$prod['image'] = array();
					$arrImg        = array();
					$productImg    = '';
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							$arrImg[]        = PRODUCT_IMAGE.$img['large_image_path'];
							//$arrProducts['image']  = base_url($img['large_image_path']);
							
							if($img['large_image_path'])
							{
							    $productImg=PRODUCT_IMAGE.$img['large_image_path'];
							}
						}
						//$prod['image']    = $arrImg;
						$prod['image']      = $productImg;
						
						
					}
					
					$prod['brand_name']='';
					if(count($arrProdBrand)>0)
					{
						$prod['brand_name'] = $arrProdBrand['name'];
					}
					
					#check wishlist products.
    				$isWishlist=0;
    				if($customer_id)
    				{
    					$countWishlist=$this->Api_model->count_wishlist();
    					if($countWishlist>0)
    					{
    						$isWishlist=1;
    					}
    				}
    				$prod['is_wishlist']=$isWishlist;
					
					
					
				}
			}
		
			#API Response.
			if(count($arrsimilarProducts)>0)
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrsimilarProducts,'Code'=>200));
			}
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
			}
			
		}
		
	}
	
	#THIS FUNCTION IS USING FOR deals.
	function deals_post()
	{   
	    

		         $limit = 15; 
                 $start = 0;
                 $page = $this->input->post('page');
                 if(!is_numeric($page)){
                 $page = 1;
                 }else{
                 $page = $page;   
                 }
                 $start = ($page - 1)*$limit;
                
		    $arrdealsProducts=$this->Api_model->getfeaturedproducts($start,$limit);
			if(count($arrdealsProducts)>0)
			{
				foreach($arrdealsProducts as &$prod)
				{
					#set product id and brand id.
					$this->Api_model->setProductId($prod['id']);
					$this->Api_model->setProductType($prod['product_type']);
					$this->Api_model->setBrandId($prod['brand_id']);
					
					#get product image.
					
					$arrProdImg   = $this->Api_model->get_productImageById();
					#get brand.
					$arrProdBrand = $this->Api_model->get_brandById();
					
					#get product price.
					$arrProdPrice = $this->Api_model->get_productPriceById();
				
					$prod['mrp_price']  = "0.00";
					$prod['sale_price'] = "0.00";
					if(count($arrProdPrice)>0)
					{
						$prod['mrp_price']  = $arrProdPrice['mrp_price'];
					    $prod['sale_price'] = $arrProdPrice['sale_price'];
						
					}
					
					$prod['image'] = array();
					$arrImg        = array();
					$productImg='';
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							//$arrImg[]        = PRODUCT_IMAGE.$img['large_image_path'];
						
							//$arrProducts['image']  = base_url($img['large_image_path']);
							if($img['large_image_path'])
							{
							   $productImg=PRODUCT_IMAGE.$img['large_image_path'];
							}
						}
						//$prod['image']    = $arrImg;
					    $prod['image']    = $productImg;
						
					}
					
					$prod['brand_name']='';
					if(count($arrProdBrand)>0)
					{
						$prod['brand_name'] = $arrProdBrand['name'];
					}
					
				}
			}
		
			#API Response.
			if(count($arrdealsProducts))
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrdealsProducts,'category_banner'=> HTTP_IMAGES_PATH.'category/slide-110.jpg'.$bannerImg,'Code'=>200));
			}
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
			}
	}
	
	#THIS FUNCTION IS USING FOR BEST SELLER PRODUCTS.
	function bestSellerProduct_post()
	{   
	
		$limit       = 15; 
		$start       = 0;
		$page        = $this->input->post('page');
		$auth_token  = $this->post('auth_token');
		
		$this->Api_model->setauthtoken($auth_token);
		
		$customer    = $this->Api_model->getcustomerid();
		$customer_id = $customer['user_id'];
		#set data.
		$this->Api_model->setuserid($customer_id); 
		
		
		if(!is_numeric($page))
		{
		 $page = 1;
		}else
		{
		   $page = $page;   
		}
		$start = ($page - 1)*$limit;
	    $arrBestSellerProducts=$this->Api_model->get_bestSellerProduct($start,$limit);
		
		if(count($arrBestSellerProducts)>0)
		{
			foreach($arrBestSellerProducts as &$prod)
			{
				#set product id and brand id.
				$this->Api_model->setProductId($prod['id']);
				$this->Api_model->setProductType($prod['product_type']);
				$this->Api_model->setBrandId($prod['brand_id']);
				
				#get product image.
				
				$arrProdImg   = $this->Api_model->get_productImageById();
				#get brand.
				$arrProdBrand = $this->Api_model->get_brandById();
				
				#get product price.
				$arrProdPrice = $this->Api_model->get_productPriceById();
			
				$prod['mrp_price']  = "0.00";
				$prod['sale_price'] = "0.00";
				if(count($arrProdPrice)>0)
				{
					$prod['mrp_price']  = $arrProdPrice['mrp_price'];
					$prod['sale_price'] = $arrProdPrice['sale_price'];
					
				}
				
				$prod['image'] = array();
				$arrImg        = array();
				if(count($arrProdImg)>0)
				{
					foreach($arrProdImg as $img)
					{	
						$arrImg[]        = PRODUCT_IMAGE.$img['large_image_path'];
						//$arrProducts['image']  = base_url($img['large_image_path']);
					}
					$prod['image']    = $arrImg;
				}
				
				$prod['brand_name']='';
				if(count($arrProdBrand)>0)
				{
					$prod['brand_name'] = $arrProdBrand['name'];
				}
				
				#check wishlist products.
				$isWishlist=0;
				if($customer_id)
				{
					$countWishlist=$this->Api_model->count_wishlist();
					if($countWishlist>0)
					{
						$isWishlist=1;
					}
				}
				
				$prod['is_wishlist']=$isWishlist;
				
			}
		}
		#API Response.
		if(count($arrBestSellerProducts))
		{
			$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrBestSellerProducts,'Code'=>200));
		}
		else
		{
	        $this->response(array('Error'=>true,'Status'=>0,'Message' => 'Data not found.','Code'=>204,'Payload'=>array()));
		}		
	}
	
	#THIS FUNCTION IS USING FOR GET CUSTOMER ORDERS.
	function getMyOrders_post(){
		$auth_token  = $this->post('auth_token');
		$postdata    = array('auth_token'   =>  $auth_token);
		
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$checkToken   =1;

		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
		    $this->Api_model->setuserid($customer_id); 
			
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
	
			if(count($arrCustomer)>0)
			{
				#get customer orders.
				
			    $arrOrders  =  $this->Api_model->get_customerOrders();
				
				if(count($arrOrders)>0)
				{
					foreach($arrOrders as &$ord)
					{
					    
					    
					    if($ord['payment_status']=='UNPAID')
						{
						    $ord['status']='Payment Pending';
						}else{
						    $ord['status']='Order Confirmed';
						}
						
						$this->Api_model->setProductId($ord['product_id']);
						
						$arrProducts=$this->Api_model->get_productById();
						
						$this->Api_model->setBrandId($arrProducts['brand_id']);
						
						#get product image.
						$arrProdImg   = $this->Api_model->get_productImageById();
							
						#get brand.
						$arrProdBrand = $this->Api_model->get_brandById();
							
						$ord['product_image']='';
						if(count($arrProdImg)>0)
						{
							$ord['product_image']=PRODUCT_IMAGE.$arrProdImg[0]['large_image_path'];
						}
						$ord['brand_name']='';
						if(count($arrProdBrand)>0)
						{
							$ord['brand_name']=$arrProdBrand['name'];
						}
						

					}
					
				}
			
				#api response.
				if($arrOrders==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer Order fetched successfully','Payload'=>$arrOrders));
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
	    }
	}
	
	#THIS FUNCTION IS USING FOR GET ORDER DETAILS.
	function getorderDetails_post(){
		$order_id    = $this->post('order_id');
		$postdata    = array(
		                    'order_id'   =>  $this->post('order_id'),
						    );
		
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$checkToken    =1;

		if(!empty($errorStr))
		{
            $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}else if($checkToken == 0)
		{
            $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			#set data.
		    $this->Api_model->setorderid($order_id); 
			
			if(count($order_id)>0)
			{
				#get customer orders.
			    $arrOrders  =  $this->Api_model->get_orderDetails();
				#api response.
				if($arrOrders==true)
				{  
                    $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Data fetched successfully','Payload'=>$arrOrders));
				}else{
	                $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
	    }
	}

	#THIS FUNCTION IS USING FOR GET GET HELP CENTERS.
	function getHelpCenters_get(){
		
		$arrahelpcenter = $this->Api_model->get_helpCenter();
		if(!empty($arrahelpcenter))
		{
			 $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Data fetched successfully','Payload'=>$arrahelpcenter));
			$this->response($response);
		}else
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'data not found.','Payload'=>array()));
			
	    }
	}
	
	#THIS FUNCTION IS USING GET HELP CENTER BY THEIR ID.
	function getHelpCenterById_post(){
	    $help_id     = $this->post('help_id');
		$postdata    = array('help_id'  =>  $help_id);
		
	    #check mandatory fields.
		$errorStr      = $this->checkRequiredParam($postdata);
		
		if(!empty($errorStr))
		{
            $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}
		else
		{
			#set data.
		    $this->Api_model->sethelpid($help_id); 
			$arrhelps = $this->Api_model->get_helpCenterById();
	
			if(count($arrhelps)>0)
			{
				  
                $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Data fetched successfully','Payload'=>$arrhelps));
				
			}
			else
			{
				#data not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'data not found.','Payload'=>array()));
			}
	    }
		
	}
	#THIS FUNCTION IS USING FOR PLACE ORDERS.
	function placeOrder_post()
	{
		$auth_token     = $this->post('auth_token');
		$postdata      = array(
		                 'auth_token'           =>  $this->post('auth_token'),
						 'billing_fname'        =>  $this->post('billing_fname'),
						 'billing_lname'        =>  $this->post('billing_lname'),
						 'billing_mobile'       =>  $this->post('billing_mobile'),
						 'pickup_name'          =>  $this->post('pickup_name'),
						 'pickup_mobile'        =>  $this->post('pickup_mobile'),
						 'pickup_name'          =>  $this->post('pickup_name'),
						 'pickup_country'       =>  $this->post('pickup_country'),
						 'pickup_city'          =>  $this->post('pickup_city'),
						 'pickup_location'      =>  $this->post('pickup_location'),
						 'pickup_address'       =>  $this->post('pickup_address'),
		                  );
		
		$billing_fname='';
		if(!empty($this->post('billing_fname')))
		{
			$billing_fname= $this->post('billing_fname');
		}
		
		$billing_lname='';
		if(!empty($this->post('billing_lname')))
		{
			$billing_lname= $this->post('billing_lname');
		}
		
		$billing_mobile='';
		if(!empty($this->post('billing_mobile')))
		{
			$billing_mobile= $this->post('billing_mobile');
		}
		
		$pickup_name='';
		if(!empty($this->post('pickup_name')))
		{
			$pickup_name= $this->post('pickup_name');
		}
		
		$pickup_mobile='';
		if(!empty($this->post('pickup_mobile')))
		{
			$pickup_mobile= $this->post('pickup_mobile');
		}
		
		$pickup_country='';
		if(!empty($this->post('pickup_country')))
		{
			$pickup_country= $this->post('pickup_country');
		}
		
		$pickup_city='';
		if(!empty($this->post('pickup_city')))
		{
			$pickup_city= $this->post('pickup_city');
		}
		
		$pickup_location='';
		if(!empty($this->post('pickup_location')))
		{
			$pickup_location= $this->post('pickup_location');
		}
		
		$pickup_address='';
		if(!empty($this->post('pickup_address')))
		{
			$pickup_address= $this->post('pickup_address');
		}
		
		$payment_method='ZAAD';
		if(!empty($this->post('payment_method')))
		{
			$payment_method= $this->post('payment_method');
		}
		
		$order_status='Order Received';
		if(!empty($this->post('order_status')))
		{
			$order_status= $this->post('order_status');
		}
		
		$payment_status='unpaid';
		if(!empty($this->post('payment_status')))
		{
			$payment_status= $this->post('payment_status');
		}
		
		$transaction_id=uniqid();
		if(!empty($this->post('transaction_id')))
		{
			$transaction_id= $this->post('transaction_id');
		}
		
		$pay_mobile='';
		if(!empty($this->post('wallet_mobile_no')))
		{
			$pay_mobile= $this->post('wallet_mobile_no');
		}
		
				
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$checkToken    = 1;

		if(!empty($errorStr))
		{
            $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}else if($checkToken == 0)
		{
            $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];

			#set data.
			$this->Api_model->setuserid($customer_id);
			
			$customer =  $this->Api_model->get_customer();
		    $usd_to_sls=  $this->Api_model->get_usd_to_sls();
				
				
			if(count($customer)>0)
			{
				
				$cartData = $this->cart_data();
				
				if(count($cartData)>0)
				{
					// $biiling  = $this->Api_model->get_billingAddress();
					// $shipping = $this->Api_model->get_shippingAddress();
					
					$order['order_datetime']           = date("Y-m-d h:i:sa");
					$order['customers_id']             = $customer_id;
					$order['bfname']                   = $billing_fname;
					$order['blname']                   = $billing_lname;
					$order['bemail']                   = '';
					$order['bphonecode']               = '';
					$order['bphoneprovider']           = '';
					$order['bmobile']                  = $billing_mobile;
					$order['bpincode']                 = '';
					$order['bcity']                    = '';
					$order['bstate']                   = '';
					$order['bcountry']                 = '';
					$order['baddress1']                = '';
					$order['baddress2']                = '';
					
					$order['pickup_name']              = $pickup_name;
					$order['pickup_mobile']            = $pickup_mobile;
					$order['pickup_country']           = $pickup_country;
					$order['pickup_city']              = $pickup_city;
					$order['pickup_location']          = $pickup_location;
					$order['pickup_address']           = $pickup_address;
					$order['pickup_address_details']   = '';
					$order['pay_mobile']               = $pay_mobile;
					$order['total_amount']             = $cartData['total'];
					$order['shipping_amount']          = $cartData['shipping'];
					$order['discount_amount']          = '';
					$order['coupon_code']              = '';
					$order['grand_total']              = $cartData['total'];
					$order['sls_grand_total']          = $usd_to_sls['value']*$cartData['total'];
					$order['payment_gateway']          = ucfirst($payment_method);
					$order['status']                   = $order_status;
					
					
					$order['payment_status']           = strtoupper($payment_status);
					$order['status_detail']            = '';
					$order['payment_currency']         = '';
					$order['authcode']                 = '';
					$order['txnid']                    = $transaction_id;
					$order['zoho_sales_order_id']      = '';
					$order['payer_email']              = '';
					$order['customer_note']            = '';
					$order['admin_note']               = '';
					
				    $order_id =$this->Api_model->save_order($order);
					//$order_id=387;
					
					foreach($cartData as $cartProduct)
					{
						$item['orders_id']                      = $order_id;
						$item['product_id']                     = $cartProduct['id'];
						$item['product_name']                   = $cartProduct['name'];
						$item['product_variation_id']           = '';
						$item['product_variation_details']      = '';
						$item['price']                          = $cartProduct['sale_price'];
						$item['qty']                            = $cartProduct['qty'];
						$item['is_cancel']                      = '';
						$item['is_return']                      = '';
						$item['return_serial_number']           = '';
						$item['return_reason']                  = '';
						
						$this->Api_model->order_items($item);
					}
					
					$this->Api_model->setorderid($order_id); 
				
					$arrOrders  =  $this->Api_model->get_orderDetails();
					
					if(count($arrOrders)>0)
					{
					    foreach($arrOrders as &$ord)
					    {
					        
					        
					        $this->Api_model->setProductId($ord['product_id']);
						
    						$arrProducts=$this->Api_model->get_productById();
    						
    						$this->Api_model->setBrandId($arrProducts['brand_id']);
    						
    						#get product image.
    						$arrProdImg   = $this->Api_model->get_productImageById();
    							
    						#get brand.
    						$arrProdBrand = $this->Api_model->get_brandById();
    							
    						$ord['product_image']='';
    						if(count($arrProdImg)>0)
    						{
    							$ord['product_image']=PRODUCT_IMAGE.$arrProdImg[0]['large_image_path'];
    						}
    						$ord['brand_name']='';
    						if(count($arrProdBrand)>0)
    						{
    							$ord['brand_name']=$arrProdBrand['name'];
    						}
					        
					    }
					}
					
						
						
					if(count($arrOrders)>0)
					{
					    $this->Api_model->delete_customer_cart();
					    $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Order placed successfully','Payload'=>$arrOrders));
					}else{
					    $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' =>'Order not found.','Payload'=>array()));
					}
				                 
				}else{
					 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' =>'Cart is empty.','Payload'=>array()));
				}
			
			}else
			{
                $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' =>'Customer not found.','Payload'=>array()));
			}
	    }
	}
	
	#THIS FUNCTION IS USING FOR GET communication_preferences.
	function getPreference_get()
	{
		
		$arrpreference = $this->Api_model->get_preference();
		if(!empty($arrpreference))
		{
			 $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Data fetched successfully','Payload'=>$arrpreference));
			$this->response($response);
		}else
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'data not found.','Payload'=>array()));
			
	    }
	}
	#THIS FUNCTION IS USING FOR GET communication_preferences.
	function changeRecommendation_post()
	{
		
		$recommendation_id     = $this->post('recommendation_id');
		$status                = $this->post('status');
		$postdata              = array('recommendation_id'  =>  $recommendation_id);
		
	    #check mandatory fields.
		$errorStr      = $this->checkRequiredParam($postdata);
		
		if(!empty($errorStr))
		{
            $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}
		else
		{
			#set data.
			$update['id']      = $recommendation_id;
			$update['status']  = $status;
		    //$this->Api_model->setRecommendationId($recommendation_id); 
			$this->Api_model->update_recomdation($update);
			$arrRecommendation = $this->Api_model->get_recommendation();
	
			if(count($arrRecommendation)>0)
			{
				  
                $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Data fetched successfully','Payload'=>$arrRecommendation));
				
			}
			else
			{
				#data not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'data not found.','Payload'=>array()));
			}
	    }
	}
	 
	#THIS FUNCTION IS USING FOR UPDATE USER PROFILE.
	function updateCustomerProfile_post(){
		$auth_token     =  $this->post('auth_token');
		$first_name     =  $this->post('first_name');
		$last_name      =  $this->post('last_name');
		$gender         =  $this->post('gender');
		$phone_code     =  $this->post('phone_code');
		
		$postdata       = array(
							'auth_token'         =>  $auth_token,
							'first_name'         =>  $first_name,
							'last_name'          =>  $last_name,
							'gender'             =>  $gender,
							'phone_code'         =>  $phone_code
					     	);
		
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		
	   
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else{
			
		    $customer    = $this->Api_model->getcustomerid();
	
		    $customer_id = $customer['user_id'];
			#set data.
			$this->Api_model->setuserid($customer_id);

			
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();

			if(count($arrCustomer)>0)
			{
		
                $this->Api_model->setfname($first_name);
				$this->Api_model->setlname($last_name);
				$this->Api_model->setGender(ucfirst($gender));
                $this->Api_model->setphonecode($phone_code);
			
			    $customerId = $this->Api_model->upadate_profile();
				$arrCustomer = $this->Api_model->get_customer();
	
			    #api response.
				if($customerId==true){
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrCustomer));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong','Payload'=>array()));
				}
				
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Customer not found.','Payload'=>array()));
			}
		}
	}
	
	#THIS FUNCTION IS USING FOR GET country.
	function country_master_get(){
		
		$arrcountry = $this->Api_model->get_country();
		#api response.
		if(count($arrcountry)>0)
		{
			$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrcountry));
		}else{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'country not found.','Payload'=>array()));
		}
	}
	
	#THIS FUNCTION IS USING FOR GET STATE.
	function  getState_post(){
		$country_id     =  $this->post('country_id');
		$postdata       = array(
							'country_id'         =>  $country_id
					     	);
		
		
		$errorStr      = $this->checkRequiredParam($postdata);
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		else{
		
			#set data.
			$this->Api_model->setcountryid($country_id);
			
			#get customer.
			$arrState = $this->Api_model->get_state();

			if(count($arrState)>0)
			{
		       
			    $this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrState));
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'state not found','Payload'=>array()));
			}
				
			
		}
	}
	#THIS FUNCTION IS USING FOR GET STATE.
	function  getCity_post(){
		$country_id     =  $this->post('country_id');
		$postdata       = array(
							'country_id'         =>  $country_id
					     	);
		
		
		$errorStr      = $this->checkRequiredParam($postdata);
		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}
		else{
		
		  
		    #set data.
			$arrCity=array();
			$this->Api_model->setcountryid($country_id);
			
			#get state.
			$arrState = $this->Api_model->get_state();
			if(count($arrState)>0)
			{
				$i=0;
				foreach($arrState as $state)
				{
					$this->Api_model->setstateid($state['id']);
					$arrNewCity = $this->Api_model->get_city();
					if(count($arrNewCity)>0)
					{
						foreach($arrNewCity as $newCity)
						{
							array_push($arrCity, $newCity);
						}
					}
				}
			}
			if(count($arrCity)>0)
			{
		       
			    $this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrCity));
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'state not found','Payload'=>array()));
			}
				
			
		}
	}
	
	#THIS FUNCTION IS USING FOR TRACK CUSTOMER ORDERS.
	function orderTracking_post()
	{
		$auth_token  = $this->post('auth_token');
		$postdata    = array('auth_token'   =>  $auth_token);
		
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$checkToken   =1;

		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
		    $this->Api_model->setuserid($customer_id); 
			
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			
	
			if(count($arrCustomer)>0)
			{
				#get customer orders.
				
			    $arrOrders  =  $this->Api_model->get_customerOrders();
			    
				
				if(count($arrOrders)>0)
				{
					foreach($arrOrders as &$ord)
					{
					   
						if($ord['payment_status']=='UNPAID')
						{
						    $ord['status']='Payment Pending';
						}else{
						    $ord['status']='Order Confirmed';
						}
					
						$this->Api_model->setorderid($ord['id']);
						
						$arrOrderStatus=$this->Api_model->get_orderStatus();
						
						$this->Api_model->setProductId($ord['product_id']);
						
						$arrProducts=$this->Api_model->get_productById();
						
						$this->Api_model->setBrandId($arrProducts['brand_id']);
						
						#get product image.
						$arrProdImg   = $this->Api_model->get_productImageById();
							
						#get brand.
						$arrProdBrand = $this->Api_model->get_brandById();
							
						$ord['product_image']='';
						if(count($arrProdImg)>0)
						{
							$ord['product_image']=PRODUCT_IMAGE.$arrProdImg[0]['large_image_path'];
						}
						$ord['brand_name']='';
						if(count($arrProdBrand)>0)
						{
							$ord['brand_name']=$arrProdBrand['name'];
						}
						
						$ord['status']=$ord['status'];
						if(count($arrOrderStatus)>0)
						{
							$ord['status']=$arrOrderStatus['status'];
						}
						
						$ord['product_desc']='';
						if(count($arrProducts)>0)
						{
							$ord['product_desc']=$arrProducts['overview'];
						}
						
						$ord['customer_name']=$arrCustomer->first_name." ".$arrCustomer->last_name;
					}
					
				}
			
				#api response.
				if($arrOrders==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer Order fetched successfully','Payload'=>$arrOrders));
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
	    }
	}
	
	#THIS FUNCTION IS USING CHANGE CART QUANTITY .
	function change_cart_quantity_post()
	{
		$auth_token  = $this->post('auth_token');
		$product_id  = $this->post('product_id');
		$type        = $this->post('type');
		$postdata    = array(
		                 'auth_token'    =>  $auth_token,
						  'product_id'   =>  $product_id,
						  'type'         =>  $type
		                );
		
	    #check mandatory fields.
		$this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$checkToken    = $this->Api_model->istokenExists();
		$checkToken   =1;

		if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		}else if($checkToken == 0)
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>array()));
		}
		else
		{
			$customer    = $this->Api_model->getcustomerid();
		    $customer_id = $customer['user_id'];
			#set data.
		    $this->Api_model->setuserid($customer_id); 
			$this->Api_model->setProductId($product_id);
			
			#get customer.
			$arrCustomer = $this->Api_model->get_customer();
			
	
			if(count($arrCustomer)>0)
			{
				#get customer orders.
				
				 $arrCart  =  $this->Api_model->get_cartExitProduct();
				 
			    $arrOrders  =  $this->Api_model->get_customerOrders();
				
				$quantity=0;
				if(count($arrCart)>0)
				{
						if($type==1)
						{
						    $arrCart['qty'] += 1;
				            $quantity        = $arrCart['qty'];
						}else{
							
							if($arrCart['qty']>1)
							{
								$arrCart['qty'] -= 1;
				                $quantity        = $arrCart['qty'];
							}else{
								 $quantity        = $arrCart['qty'];
							}
							
						}
						
						$this->Api_model->setProductQuantity($quantity);
						$this->Api_model->setGuestId('');
						$this->Api_model->setProductVariant('');
						$this->Api_model->setProductId($product_id);
						$this->Api_model->setCartId($arrCart['id']);
						#add product in cart.
						$cartResult=$this->Api_model->add_to_cart();
					
						#get user cart data.
						$cartData=$this->Api_model->get_cartProduct();
						
				}
			
				#api response.
				if(count($arrCart)>0)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Cart updated successfully','Payload'=>$cartData));
				}else{
				 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'This product is not added in cart.','Payload'=>array()));
				}
			}
			else
			{
				#customer not found.
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
			}
	    }
	}
}