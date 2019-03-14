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
            
            $statusmail = $this->SendmailOtp($OTP,$email,$contact_num,$name,$bodymsg,$subject);
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
					
					$customerData3['auth_token']  = $authtoken;
				
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
			if(count($customer))
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Login successfully.','Payload'=>$customerdetails));
				#200 being the HTTP response code   
            }
			else
			{
				$this->response(array('Error'=>true,'Status'=>0,'Message' => 'Please enter valid email and password.'));
            }  
        }
    }   
	
	#THIS FUNCTION IS USING FOR FORGOT PASSWORD.
	function forgot_password_post()
	{
	    $email     =  $this->input->post('email');
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
    		
    		
			if($mail)
			{
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Your password has been sent to your email.'));
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
	//	$auth_token  = $this->input->post('auth_token');
		
	 	$postdata  =  array( 
					'customer_id' => $customer_id,
					'otp'         => $otp
				//	'auth_token'  => $auth_token
					);
	 	$errorStr  =  $this->checkRequiredParam($postdata);
		$this->Api_model->setauthtoken($auth_token);
	//	$checkToken=$this->Api_model->istokenExists();
	 	if($errorStr)
		{
			$this->response(array('Error'=>true,'Code'=>200,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}/*else if($checkToken == 0)
		{
			$this->response(array('Error'=>false,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>''));
		}*/else{
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
				
						$statusmail      = $this->SendmailOtp($otp,$email,$contact_num,$name,$bodymsg,$subject);
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
						$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'otp not sent.','Payload'=>array()));
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
            "image_path"=> base_url().$element['mobile_image_path'],
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
					$arrProducts=$this->Api_model->get_bestSellerProductBycatId($value['id']);
					if(count($arrProducts)>0)
					{
						foreach($arrProducts as &$prod)
						{
							#set product id.
							$text = strip_tags($prod['small_desc'], '<br><p><li>');
                            $text = preg_replace ('/<[^>]*>/', PHP_EOL, $text);
							$prod['small_desc'] = $text;
							$this->Api_model->setProductId($prod['id']);
							
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
								$prod['image']=base_url($arrProdImg[0]['small_image_path']);
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
				$value['category_image']    = HTTP_IMAGES_PATH.'category/'.$categoryimage['category_image'];
				$value['bestsellerProduct'] = $arrProducts;
			
			}
		}
	   
		#API Response.
		if(count($arrCategories)>0)
		{
			$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=> $arrCategories,'Code'=>200));
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
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							$arrImg[]              = base_url($img['middle_image_path']);
							//$arrProducts['image']  = base_url($img['middle_image_path']);
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
							$arrImg[]                = base_url($img['middle_image_path']);
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
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							$arrImg[]        = base_url($img['middle_image_path']);
							//$arrProducts['image']  = base_url($img['middle_image_path']);
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
	function addTocart_back_post()
	{
		$product_id          = $this->post('product_id');
		$product_variant_id  = $this->post('product_variant_id');
		$postdata  = array(
						'product_id'         =>  $product_id,
						'product_variant_id' =>  $product_variant_id
						);
		#decleare variables here.
		$quantity = 1;
		if(!empty($this->post('quantity')))
		{
			$quantity = $this->post('quantity');
		}
		
		$customer_id='';
		if(!empty($this->input->post('customer_id')))
		{
			$customer_id=$this->input->post('customer_id');
		}
		
		$guestId='';
		if(!empty($this->input->post('guest_id')))
		{
		  $guestId=$this->input->post('guest_id');
		}
		
		$errorStr      = $this->checkRequiredParam($postdata);
	    if(empty($customer_id) && empty($guestId))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please enter guest id/customer id','Payload'=>array()));
		}
		else if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}else{
		
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setGuestId($guestId);
			$this->Api_model->setProductId($product_id);
			$this->Api_model->setProductVariant($product_variant_id);
			$this->Api_model->setProductQuantity($quantity);
			
			$arrProduct=$this->Api_model->get_productById();
			if(count($arrProduct)>0)
			{
				#add product in cart.
				$cartResult=$this->Api_model->add_to_cart();
				
				#get user cart data.
				$cartData=$this->Api_model->get_cartProduct();
				
				#api response.
				if($cartResult==true){
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Product is added to cart','Payload'=>$cartData));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong','Payload'=>array()));
				}
				
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'This product not found.','Payload'=>array()));
			}
			
		}
	}

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
			
			
	        $wishlistExitProduct=$this->Api_model->get_wishlistExitProduct();
			if(count($wishlistExitProduct)>0)
			{
				$quantity +=$wishlistExitProduct['qty'];
				
				$this->Api_model->setCartId($wishlistExitProduct['id']);
			}
			
			
			$this->Api_model->setProductQuantity($quantity);
			
			
			$arrProduct=$this->Api_model->get_productById();
			if(count($arrProduct)>0)
			{
				#add product in cart.
				 $cartResult=$this->Api_model->add_to_cart();
			
				#get user cart data.
				$cartData=$this->Api_model->get_cartProduct();
				
				#api response.
				if($cartResult==true){
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Product is added to cart','Payload'=>$cartData));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong','Payload'=>array()));
				}
				
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'This product not found.','Payload'=>array()));
			}
			
		}
	}
	
	#THIS FUNCTION IS USING FOR VIEW CART PRODUCT.
	function viewCartProduct_back_post(){
	//	$customer_id = $this->post('customer_id');
	//	$auth_token  = $this->post('auth_token');
	/*	$postdata    = array( 
							'customer_id'  =>  $customer_id,
						//	'auth_token'   =>  $auth_token
							);
	
	    $this->Api_model->setauthtoken($auth_token);
		$errorStr      = $this->checkRequiredParam($postdata);
		$productId     = $this->post('product_id');*/
	//	$checkToken    = $this->Api_model->istokenExists();
	/*	if(!empty($errorStr))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please fill these all mandatory fields '.$errorStr,'Payload'=>array()));
		
		}*/
		
		$customer_id='';
		
		if(!empty($this->input->post('customer_id')))
		{
			$customer_id=$this->input->post('customer_id');
		}
		
		$guestId='';
		if(!empty($this->input->post('guest_id')))
		{
		  $guestId=$this->input->post('guest_id');
		}
		    if(empty($customer_id) && empty($guestId))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please enter guest id/customer id','Payload'=>array()));
		}
		else{

           /* if($checkToken>0)
			{*/
			    #set data.
			    $this->Api_model->setuserid($customer_id);
			    $this->Api_model->setGuestId($guestId);
			
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
							$prod['image']=base_url($arrProdImg[0]['small_image_path']);
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
				if(count($cartData)>0){
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Product is added to cart.','Payload'=>$cartData,'total'=>$total,'sub_total'=>$total,'shipping'=>$shipping));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'You do not have product in cart.','Payload'=>array()));
				}
		/*	}else{
				$this->response(array('Error'=>false,'Code'=>204,'Status'=>0,'Message' => 'You do not have authentication.','Payload'=>''));
			}*/
			
		}
	}

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
							$prod['image']=base_url($arrProdImg[0]['small_image_path']);
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
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Product is added to cart.','Payload'=>$cartData,'total'=>$total,'sub_total'=>$total,'shipping'=>$shipping,'total_item'=>$cart_item));
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
					$prod['image']=base_url($arrProdImg['small_image_path']);
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
	function deleteCartProduct_back_post()
	{
			$customer_id='';
		if(!empty($this->input->post('customer_id')))
		{
			$customer_id=$this->input->post('customer_id');
		}
		
		$guestId='';
		if(!empty($this->input->post('guest_id')))
		{
		  $guestId=$this->input->post('guest_id');
		}
		    if(empty($customer_id) && empty($guestId))
		{
			$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Please enter guest id/customer id','Payload'=>array()));
		}
		else
		{
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setGuestId($guestId);
			$this->Api_model->setProductId($this->post('product_id'));
			
			$cartData=$this->Api_model->get_cartProduct();
			#delete cart product.
			$result=$this->Api_model->delete_cartProduct();
			
			if($result==true)
			{  
			  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Cart product is deleted successfully','Payload'=>$cartData));
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong.','Payload'=>array()));
			}
		}
	}

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
			$customer_id  = $customer['user_id'];
			
			#set data.
			$this->Api_model->setuserid($customer_id);
			$this->Api_model->setProductId($this->post('product_id'));
			
			$cartData=$this->Api_model->get_cartProduct();
			
			#delete cart product.
			$result=$this->Api_model->delete_cartProduct();
			
			if($result==true)
			{  
	
			  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Cart product is deleted successfully','Payload'=>$cartData));
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
				#get customer bill address.
				$result =$this->Api_model->get_billingAddress();
				
				if(count($result)==0)
				{
				  $result = $arrCustomer;
				}
				
				#api response.
				if($result==true)
				{  
				  $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Customer billing address fetched successfully.','Payload'=>$result));
				}else{
					$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'Data not found.','Payload'=>array()));
				}
			}else
			{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'customer not found.','Payload'=>array()));
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
				#get customer bill address.
				$result =$this->Api_model->get_shippingAddress();
				
				if(count($result)==0)
				{
				  $result = $arrCustomer;
				}
				
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
    function addBillingAddress_post(){
		$auth_token = $this->post('auth_token');
		$postdata   = array(
						  'email'                =>  $this->post('email'),
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
			$this->Api_model->setuserName($this->post('email'));
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
			
		    #set data.
			$this->Api_model->setuserid($customer_id); 
			$this->Api_model->setfname($this->post('name'));
			$this->Api_model->setphone($this->post('mobile'));
			$this->Api_model->setCountry($this->post('country'));
			$this->Api_model->setAddressPrimary($isPrimary);
			$this->Api_model->setAddressId($this->post('address_id'));
			$this->Api_model->setAddress1($this->post('address'));
			$this->Api_model->setCity($this->post('city'));
			$this->Api_model->setLocation($this->post('location'));
			
			#set blank data in input fields.
			$this->Api_model->setlname('');
			$this->Api_model->setphonecode('');
			$this->Api_model->setuserName('');
			$this->Api_model->setAddressDetails('');
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
						$this->Api_model->setFeedbackRatting($arrRatting[$i]);
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
								$prod['image']=base_url($arrProdImg[0]['small_image_path']);
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
						$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Product is added to wishlist','Payload'=>$wishlistData));
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
						$prod['image']=base_url($arrProdImg[0]['small_image_path']);
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
				$this->response(array('Error'=>false,'Code'=>200,'Status'=>1,'Message' => 'Wishlist successfully sent','Payload'=>$wishlistData));
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
			
			if($result==true)
			{  
		      $this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Wishlist product is deleted successfully','Payload'=>array()));
			}else{
				$this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' => 'something went wrong.','Payload'=>array()));
			}
		}
	}
	
	
   

    #THIS FUNCTION IS USING FOR SEND OTP EMAIL.
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
		
		$data['msg']=$bodymsg;

		$ci->email->from('info@ziqqi.com', 'Ziqqi');
		$ci->email->to($email);
		$this->email->reply_to('ajathtesting@gmail.com', 'Explendid Videos');
		$ci->email->subject($subject);
		$msg = $this->load->view('otp_mail.php',$data,TRUE);
        $this->email->message($msg);   
	
		$ci->email->send();
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
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							$arrImg[]        = base_url($img['middle_image_path']);
							//$arrProducts['image']  = base_url($img['middle_image_path']);
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
			if(count($arrsimilarProducts))
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
					if(count($arrProdImg)>0)
					{
						foreach($arrProdImg as $img)
						{	
							$arrImg[]        = base_url($img['middle_image_path']);
							//$arrProducts['image']  = base_url($img['middle_image_path']);
						}
						$prod['image']    = $arrImg;
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
				$this->response(array('Error'=>false,'Status'=>1,'Message' => 'Data fatched successfully.','Payload'=>$arrdealsProducts,'Code'=>200));
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
						$arrImg[]        = base_url($img['middle_image_path']);
						//$arrProducts['image']  = base_url($img['middle_image_path']);
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
		$postdata   = array(
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
					$order['pay_mobile']               = '';
					$order['total_amount']             = $cartData['total'];
					$order['shipping_amount']          = $cartData['shipping'];
					$order['discount_amount']          = '';
					$order['coupon_code']              = '';
					$order['payment_gateway']          = 'Zaad';
					$order['status']                   = 'Order Received';
					
					
					$order['payment_status']           = 'unpaid';
					$order['status_detail']            = '';
					$order['payment_currency']         = '';
					$order['authcode']                 = '';
					$order['txnid']                    = uniqid();
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
						$item['product_variation_id']           = $cartProduct['product_variant_id'];
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
				
					
					$this->response(array('Error'=>false,'Code'=>200,'Status'=>0,'Message' => 'Order placed successfully','Payload'=>$arrOrders));
				
				}else{
					 $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' =>'Cart is empty.','Payload'=>array()));
				}
			
			}else
			{
                $this->response(array('Error'=>true,'Code'=>204,'Status'=>0,'Message' =>'Customer not found.','Payload'=>array()));
			}
	    }
	}
	 
}