<?php
Class Api_model extends CI_Model{
    var $CI;
    function __construct(){
        parent::__construct();
        $this->CI =& get_instance();
        $this->CI->load->database(); 
        $this->CI->load->helper('url');
    }
    
    #set username.
    public function setuserName($username) {
        $this->_username = $username;
    }

    #set password.
    public function setpassword($password) {
        $this->_password = $password;
    } 
    
    #set customer first name.
    public function setfname($fname) {
        $this->_fname = $fname;
    } 
    
    #set customer last name.
    public function setlname($lname) {
        $this->_lname = $lname;
    }
    
    #set customer phone number.
    public function setphone($phone) {
        $this->_phone = $phone;
    }
    
    #set customer phone country code.
    public function setphonecode($code) {
        $this->_phonecode = $code;
    }

    #set customer id.
    public function setuserid($user_id) {
        $this->_user_id = $user_id;
    }
    
    #set customer device id.
    public function setDeviceId($deviceId){
        $this->_device_id = $deviceId;
    }
    
    #set customer device type.
    public function setDeviceType($deviceType){
        $this->_device_type = $deviceType;
    }
    
    #set customer facebook login status.
    public function setFacebookLogin($isLoging){
        
        $this->_facebok_login = $isLoging;
    }
    
    #set customer google login status.
    public function setGoogleLogin($isLoging)
    {
        $this->_google_login = $isLoging;
    }
    
    #set customer facebook auth id.
    public function setFacebookOauth($oauthId)
    {
        $this->_facebook_oauth = $oauthId;
    }
    
    #set customer google auth id.
    public function setGoogleOauth($oauthId)
    {
        $this->_google_oauth = $oauthId;
    }
    
    #set customer email verify status.
    public function setEmailVerify($verifyStatus)
    {
        $this->_email_verify = $verifyStatus;
    }
    
    #set customer phone verify status.
    public function setPhoneVerify($verifyStatus)
    {
        $this->_phone_verify = $verifyStatus;
    }
    
    #set customer email token.
    public function setEmailToken($token)
    {
        $this->_email_token = $token;
    }
    
    #set customer phone token.
    public function setPhoneToken($token)
    {
        $this->_phone_token = $token;
    }
    
    #set customer active status.
    public function setUserStatus($status)
    {
        $this->_user_active = $status;
    }
    
    #set gender.
    public function setGender($status)
    {
        $this->_user_gender = $status;
    }
    
    #Set product id.
    public function setProductId($id)
    {
        $this->_product_id = $id;
    }

     #Set product name.
    public function setProductname($productname)
    {
        $this->_productname = $productname;
    }
    
    #Set product type.
    public function setProductType($type)
    {
        $this->_product_type = $type;
    }
    
    #Set brand id.
    public function setBrandId($id)
    {
        $this->_brand_id = $id;
    }
    
    #set OTP.
    public function setotp($otp) {
        $this->_otp = $otp;
    }

    public function setauthtoken($authtoken) {
        $this->_authtoken = $authtoken;
    }

    #Set product variant.
    public function setProductVariant($id)
    {
        $this->_product_variant = $id;
    }
    
    #Set product quantity.
    public function setProductQuantity($id)
    {
        $this->_product_quantity = $id;
    }
    
    #Set guest id.
    public function setGuestId($id)
    {
        $this->_guest_id = $id;
    }

    #Set address status.
	public function setAddressPrimary($status)
    {
        $this->_is_primary = $status;
    }
	
	#Set address status.
	public function setAddressId($id)
    {
        $this->_address_id = $id;
    }
	
	#Set address first line.
	public function setAddress1($data)
    {
        $this->_customer_address1 = $data;
    }
	
	#Set address second line.
	public function setAddress2($data)
    {
        $this->_customer_address2 = $data;
    }
	
	#Set city.
	public function setCity($data)
    {
        $this->_customer_city = $data;
    }
	
	#Set state.
	public function setState($data)
    {
        $this->_customer_state = $data;
    }
	
	#Set state.
	public function setPincode($data)
    {
        $this->_customer_pincode = $data;
    }
	
	#Set country.
	public function setCountry($data)
    {
        $this->_customer_country = $data;
    }
	
	#Set phone provider.
	public function setPhoneProvider($data)
    {
        $this->_customer_phoneprovider = $data;
    }
	
	#Set pay phone.
	public function setPayMobile($data)
    {
        $this->_customer_pay_mobile = $data;
    }
	
	#Set address details.
	public function setAddressDetails($data)
    {
        $this->_customer_address_deatails = $data;
    }
	
	#Set cutsomer location.
	public function setLocation($data)
    {
        $this->_customer_location = $data;
    }
	
	#Set feedback id.
	public function setFeedbackId($id)
    {
        $this->_feedback_id = $id;
    }
	
	#Set customer feedback id.
	public function setCustomerFeedbackId($id)
    {
        $this->_customer_feedback_id = $id;
    }
	
	#Set feedback id.
	public function setFeedbackRatting($ratting)
    {
        $this->_feedback_ratting = $ratting;
    }
   
    #Set order id.
	public function setorderid($id)
    {
        $this->_order_id = $id;
    }
	#Set help id.
	public function sethelpid($id)
    {
        $this->_help_id = $id;
    }
    
    #Set cart id.
    public function setCartId($id)
    {
        $this->_cart_id = $id;
    }
    
    #set country id.
    public function setcountryid($id) {
        $this->_country_id = $id;
    }
	
	#set state id.
    public function setstateid($id) {
        $this->_state_id = $id;
    }
    
    #set otp method.
    public function setotpmethod($method) {
        $this->_otp_method = $method;
    }
    
    #set otp method.
    public function setnewpassword($pass) {
        $this->_new_password = $pass;
    }
    
    
    #THIS FUNCTION IS USING FOR CHECK A VALID EMAIL ID.
    public function isEmail() {
        if (filter_var($this->_username, FILTER_VALIDATE_EMAIL)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    #THIS FUNCTION IS USING FOR CHECK A VALID PHONE NUMBER.
    public function isMobile() {
        if (is_numeric($this->_phone)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    #check user email before signup
    function check_customer_email(){
        $this->db->select('*');
        $this->db->from('customers');
        $this->db->where('email', $this->_username); 
        $count = $this->db->count_all_results();
        
        if ($count > 0){
            return true;
        }else{
            return false;
        }
    }
    
    #check user phone before signup
    function check_customer_phone(){
        $this->db->select('*');
        $this->db->from('customers');
        $this->db->where('mobile', $this->_phone); 
        $count = $this->db->count_all_results();
        
        if ($count > 0){
            return true;
        }else{
            return false;
        }
    }
    
    #save or update user details
    function save()
    {
        $id = $this->_user_id;
        $data = array(
          //  'id'                   => $this->_user_id = NULL ? $this->_user_id : '',
            'password'             => sha1($this->_password),
            'first_name'           => $this->_fname,
            'last_name'            => $this->_lname,
            'email'                => $this->_username,
            'phone_code'           => $this->_phonecode,
            'mobile'               => $this->_phone, 
            'gender'               => $this->_user_gender,
            'reg_date'             => date('Y-m-d'),
            
            
            'is_facebook_login'    => $this->_facebok_login,
            'facebook_oauth_uid'   => $this->_facebook_oauth,
            'is_google_login'      => $this->_google_login,
            'google_oauth_uid'     => $this->_google_oauth,
            'email_token_id'       => $this->_email_token,
            'mobile_token_id'      => $this->_phone_token,
            'is_email_verify'      => $this->_email_verify,
            'is_mobile_verify'     => $this->_phone_verify,
            'is_active'            => $this->_user_active
           
        );
        if ($id)
        {
            $this->db->where('id', $id);
            $this->db->update('customers', $data);
            return $id;
        }else{
            $this->db->insert('customers', $data);
            $id = $this->db->insert_id();
            return $id;
            //$this->db->last_query();
        }
    }
    
    
    #get customer details
    function get_customer()
    {
        $this->db->select('id,first_name,last_name,gender,email,phone_code,mobile,is_active');
        $this->db->from('customers');
        $this->db->where('id', $this->_user_id);
        $query = $this->db->get();
        return $query->row();
    }
    
    #THIS FUNCTION IS USING FOR CUSTOMER LOGIN.
    function customer_login()
    {
        $this->db->select('*');
        $this->db->from('customers');
        $where = '(email="'.$this->_username.'" OR mobile = "'.$this->_username.'")';
        $this->db->where($where); 
        $this->db->where('password', sha1($this->_password));
        //$this->db->where('is_active', 1);
        $query = $this->db->get();
       // $sql = $this->db->last_query();
        $result=$query->row_array();
       
		if(count($result)>0)
		{
			if($result['is_active']==1)
			{
				return $result;
			}else{
				return $result['id'].'#&#'.$result['first_name'].'#&#'.$result['mobile'].'#&#'.$result['phone_code'].'#&#'.$result['email'];
			}
			
		}else{
			return false;
		}
    }
    
    #THIS FUNCTION IS USING FOR GET CUSTOMER BY EMAIL.
    function get_customer_by_email()
    {
        $this->db->select('*');
        $this->db->from('customers');
        $this->db->where('email', $this->_username);
        $query = $this->db->get();
        return $query->row_array();
    }
    
    #THIS FUNCTION IS USING FOR GET CUSTOMER BY PHONE.
    function get_customer_by_phone()
    {
        $this->db->select('*');
        $this->db->from('customers');
        $this->db->where('mobile', $this->_phone);
        $query = $this->db->get();
        return $query->row_array();
    }
    
    #THIS FUNCTION IS USING FOR GET CUSTOMER BY PHONE.
    function get_customer_by_socialId($loginType , $socialId)
    {
        $this->db->select('*');
        $this->db->from('customers');
        if($loginType =='g')
        {
            $this->db->where('google_oauth_uid', $socialId);
        }
        else
        {
            $this->db->where('facebook_oauth_uid', $socialId);
        }
        
        $query = $this->db->get();
        return $query->row_array();
    }
    
    #THIS FUNCTION IS USING FOR RESET PASSWORD.
    function reset_password()
    {
        
      
           
				//Load email library
		
        //$this->load->library('encrypt');
        $customer = $this->get_customer_by_email($this->_username);
        //$customer=1;
        if(count($customer)>0)
        {
    
           if(empty($this->_new_password))
           {
               
               $new_password     = random_string('alnum', 8);
				$sql = "update customers set otp_verification='".$new_password."', password_reset_date='".date('Y-m-d')."' WHERE email='".$this->_username."' LIMIT 1";
				$this->db->query($sql);
			
				if($this->_otp_method==1)
				{
					//$this->sendBSms($customer['mobile'],'<p>Your new password is :<br>'.$new_password.'<br/><br/><br/>From<br/>Team Ziqqi</p>');
					$verification_msg='Your verification code is : '.$new_password;
			 	    $phone='00'.$customer['phone_code'].$customer['mobile'];
					 $sendMob=$this->sendBSms($phone,$customer['phone_code'],$verification_msg);
					 
				}else{
					$to = $this->_username;
					$from='info@idukaan.ae';
					$subject = 'Ziqqi : Password Reset';
					$message = '<p>Your vefication code  is :<br>'.$new_password.'<br/><br/><br/>From<br/>Team Ziqqi</p>';
					// Always set content-type when sending HTML email
				
						$headers = "From: " . strip_tags($from) . "\r\n";
            			$headers .= "Reply-To: ". strip_tags($to) . "\r\n";
            			$headers .= "CC: info@idukaan.ae \r\n";
            			$headers .= "MIME-Version: 1.0\r\n";
            			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		
				    	$send=mail($to,$subject,$message,$headers);
				
				}
               
           }else{
                if($this->_otp == $customer['otp_verification'])
                {
                    $sql = "update customers set password='".sha1($this->_new_password.HASHTOKEN)."', password_reset_date='".date('Y-m-d')."' WHERE email='".$this->_username."' LIMIT 1";
				       $this->db->query($sql);
				       return 'password_changed';
				       exit;
				       
                }else{
                    return 'otp_missmatched';
                    exit;
                }
               
           }
		        
            return true;
            exit;
        }else{
            return false;
            exit;
        }
    }
    
    #THIS FUNCTION IS USING SAVE DEVICE DETAILS.
    function save_deviceDetails()
    {
     
        $this->db->select('id');
        $this->db->from('device_details');
        $this->db->where('customer_id', $this->_user_id);
        $query = $this->db->get();
        $rows= $query->row_array();

		$id='';
		if(count($rows))
		{
			$id=$rows['id'];
		}
        $data = array(
		    'id'           => $id,
            'customer_id'  => $this->_user_id,
            'device_id'    => $this->_device_id,
            'device_type'  => $this->_device_type
        );
        if($id)
        {
            $this->db->where('id', $id);
            $this->db->update('device_details', $data);
            return $id;
        }else
        {
            $this->db->insert('device_details', $data);
            $id = $this->db->insert_id();
            return $id;
        }
    }
    
    #THIS FUNCTION IS USING FOR GET HOME BANNER.
    function get_banners()
    {
        $this->db->select('*');
        $this->db->from('home_slideshow');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    #THIS FUNCTION IS USING FOR GET MAIN CATEGORIES.
    function homeCategory($catId, $subcatId)
    { 

        $this->db->select('*');
        $this->db->from('categories');
        if(!empty($catId) && !empty($subcatId))
        {
            $this->db->where('id', $catId); 
            $this->db->where('parent_category_id', $subcatId);  
        }
        else if(!empty($subcatId))
        {
            $this->db->where('parent_category_id', $subcatId);  
            $this->db->where('menu_display', 0);
           
        }else{
           $this->db->where('parent_category_id', 0);
		   $this->db->where('menu_display', 1);	
		  
           
        }
        
        
        $this->db->where('is_active', 1);
        if($subcatId==6)
        {
             $this->db->order_by('display_order', 'ASC');
        }else{
             $this->db->order_by('id', 'ASC');
        }
        
       
        
        $this->db->limit(10, 0);
        $query = $this->db->get();
        return $query->result_array();
        //$this->db->last_query();
        
    }
    
    #THIS FUNCTION IS USING FOR GET BEST SELLER PRODUCTS.
    function get_bestSellerProductBycatId($catId)
    {
        if(!empty($catId))
        {
            $this->db->select('product.id,product.product_type,product.name,product.ref_no as sku,product.quantity,product.sale_price,product.mrp_price,product.brand_id,product.supplier_id,product.linkhref,product.small_desc,product.display_order,product.is_active,product.is_bestseller,product_categories.product_id,product_categories.categories_id');
            $this->db->from('product');
            $this->db->join('product_categories', 'product.id = product_categories.product_id','INNER');
            $this->db->where('product_categories.categories_id', $catId);
            $this->db->where('product.is_bestseller', 1);
            $this->db->where('product.is_active', 1);
            $this->db->order_by('product.id', 'RANDOM');
            $this->db->order_by('product.display_order', 'ASC');
            $this->db->limit(6, 0);
            $query = $this->db->get();
            $products= $query->result_array();
        }else{
            
            $products= $this->get_bestSellerProduct(0 , 6);
        }
        return $products;   
    }
    
    #THIS FUNCTION IS USING FOR GET CATEGORIES PRODUCTS.
	function get_subcategoryproduct($catId){
		$this->db->select('*');
        $this->db->from('product_categories');
        $this->db->where_in('categories_id', $catId);
		$query = $this->db->get();
		$arrCat= $query->result_array();
		// echo "<pre>";
		// print_r($arrCat);
		// exit;
		$product=array();
		if(count($arrCat)>0)
		{
			foreach($arrCat as $cat)
			{
				
				$this->db->select('id as product_id,product_type,name,ref_no as sku,quantity,sale_price,mrp_price,brand_id,supplier_id,linkhref,small_desc,display_order,is_active,is_bestseller');
				$this->db->from('product');
				$this->db->where('id', $cat['product_id']);
				//$this->db->where('is_bestseller', 1);
                $this->db->where('is_active', 1);
				$query = $this->db->get();
				$arrProd= $query->row_array();
				if(count($arrProd)>0)
				{
					if(count($product)<6)
					{
						$product[]=$arrProd;
					}
					
				}
				
			}
		}
		return $product;
		//return $this->db->last_query();
	}
    #THIS FUNCTION IS USING FOR GET BEST SELLER PRODUCTS.
    function get_productBycatId($catId,$start,$limit)
    {
        $this->db->select('product.id,product.product_type,product.name,product.ref_no as sku,product.quantity,product.sale_price,product.mrp_price,product.brand_id,product.supplier_id,product.linkhref,product.small_desc,product.display_order,product.is_active,product_categories.product_id,product_categories.categories_id');
        $this->db->from('product');
        $this->db->join('product_categories', 'product.id = product_categories.product_id','INNER');
        $this->db->where('product_categories.categories_id', $catId);
        $this->db->where('product.is_active', 1);
        $this->db->limit($limit,$start);
        $this->db->order_by('product.display_order', 'ASC');
        
        $query    = $this->db->get();
        $products = $query->result_array();
        return $products;   
    }

      #THIS FUNCTION IS USING FOR Search.
    function getproductbysearchname()
    {
        $this->db->select('categories.id,categories.name');
        $this->db->from('categories');
        $this->db->like('categories.name', $this->_productname,'both');
        $this->db->where('categories.is_active', 1);
        $this->db->order_by('categories.id', 'ASC');
        $query    = $this->db->get();
        $products = $query->result_array();
        return $products;   
    }

     #THIS FUNCTION IS USING FOR GET BEST SELLER PRODUCTS.
    function getproductbyname()
    {
        $this->db->select('product.id,product.product_type,product.ref_no as sku,product.name');
        $this->db->from('product');
        $this->db->like('product.name', $this->_productname,'both');
        $this->db->where('product.is_active', 1);
        $this->db->order_by('product.display_order', 'ASC');
        $query    = $this->db->get();
        $products = $query->result_array();
        return $products;   
    }
    
    #THIS FUNCTION IS USING FOR GET BEST SELLER PRODUCTS.
    function get_productById()
    {
        $this->db->select('id,product_type,name,ref_no as sku,quantity,sale_price,mrp_price,brand_id,supplier_id,linkhref,small_desc as overview,big_desc as specifications,is_active');
        $this->db->from('product');
        $this->db->where('id', $this->_product_id);
        $this->db->where('is_active', 1);
        $query    = $this->db->get();
        $products = $query->row_array();
        return $products;   
    }
    
       #THIS FUNCTION IS USING FOR GET BEST SELLER PRODUCTS.
    function get_similarproducts($categories_id,$start,$limit)
    {
        $this->db->select('product.id,product.product_type,product.ref_no as sku,product.name,product.brand_id');
        $this->db->from('product');
        $this->db->join('product_categories', 'product_categories.product_id=product.id');
        $this->db->where('product.is_active', 1);
        $this->db->where_in('product_categories.categories_id',$categories_id);
        $this->db->where('product.id !=', $this->_product_id);
        $this->db->limit($limit,$start);
        $this->db->order_by('product.display_order', 'ASC');
        $query    = $this->db->get();
        $products = $query->result_array();
        return $products;   
    }
    
    #THIS FUNCTION IS USING FOR GET BEST SELLER PRODUCTS.
    function get_bestSellerProduct($start,$limit)
    {
        $this->db->select('*');
        $this->db->from('product');
        $this->db->where('is_bestseller', 1);
        $this->db->where('is_active', 1);
        $this->db->order_by('id', 'RANDOM');
        $this->db->order_by('display_order', 'ASC');
		
		 $this->db->limit($limit,$start);
       // $this->db->limit(6, 0);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    
    #THIS FUNCTION IS USING FOR GET BEST DEAL PRODUCTS.
    function get_bestDealProduct()
    {
        $this->db->select('*');
        $this->db->from('product');
        $this->db->where('is_deal_display', 1);
        $this->db->where('is_active', 1);
        $this->db->order_by('deal_display_order', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    #THIS FUNCTION IS USING FOR GET PRODUCT IMAGES.
    function get_productImageById()
    {
        $this->db->select('*');
        $this->db->from('product_image');
        $this->db->where('product_id', $this->_product_id);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    #THIS FUNCTION IS USING FOR GET  BRAND.
    function get_brandById()
    {
        $this->db->select('*');
        $this->db->from('brand');
        $this->db->where('id', $this->_brand_id);
        $query = $this->db->get();
        return $query->row_array();
    }
    
    #THIS FUNCTION IS USING FOR GET PRODUCT PRICE.
    function get_productPriceById()
    {
        $this->db->select('*');
        $this->db->from('product_price');
        $this->db->where('product_id', $this->_product_id);
        $this->db->where('currency_id', 1);
        if($this->_product_type == 1)
        {
            $this->db->where('product_variant_id', 0);
        }
        $this->db->order_by('sale_price');
        $this->db->limit(1, 0);
        $query = $this->db->get();
        return $query->row_array();
        //$this->db->last_query();
    }
    
    #THIS FUNCTION IS USING FOR GET PRODUCT REVIEW.
    function get_productReview()
    {
        $this->db->select('*');
        $this->db->from('product_review');
        $this->db->where('is_active',   1); 
        $this->db->where('product_id', $this->_product_id); 
        $query = $this->db->get();
        return $query->result_array();
        //$this->db->last_query();
 
    }
    
    function getfeedbackmaster()
    {
        $this->db->select('*');
        $this->db->from('feedback_master');
        $query = $this->db->get();
        return $query->result_array();
       
    }

    function getlanguagemaster()
    {
        $this->db->select(array('id','language_name','language_shortname'));
        $this->db->from('language_master');
        $this->db->where('status',1);
        $query = $this->db->get();
        return $query->result_array();
       
    }
    
    function getcurrencymaster()
     {
        $this->db->select(array('id','name','short_name','sign'));
        $this->db->from('currency');
        $this->db->where('is_active',1);
        $query = $this->db->get();
        return $query->result_array();
       
    }

    

    function insertotp()
    {
        
        $this->db->select('id');
        $this->db->from('customer_otp');
        $this->db->where('user_id', $this->_user_id);
        $query = $this->db->get();
        $rows= $query->row_array();
       
          $data = array(
            'user_id'      => $this->_user_id,
            'otp'    => $this->_otp
        );
        if(!empty($rows)){
        $data['id']=$rows['id'];
         $this->db->where('id', $data['id']);
        $this->db->update('customer_otp', $data);
        }else{
        $this->db->insert('customer_otp', $data);
            $id = $this->db->insert_id();
            return $id;    
        }
    }

    function authtoken()
    {
        $uniqueid = uniqid();
        $this->db->select('id,auth_token');
        $this->db->from('authentication_token');
        $this->db->where('user_id', $this->_user_id);
		
        $query  = $this->db->get();
        $rows   = $query->row_array();
        
          $data = array(
            'user_id'       => $this->_user_id,
            'auth_token'    => $uniqueid
        );
        if(!empty($rows))
		{
          return $rows['auth_token'];
        }else
		{
			$this->db->insert('authentication_token', $data);
			return $uniqueid;    
        }
    }

    function updateAuthToken()
	{
		$uniqueid = uniqid();
		$data     = array(
						'user_id'     => $this->_user_id,
						'auth_token'  => $uniqueid
					);
		$this->db->where('user_id', $this->_user_id);
        $this->db->update('authentication_token', $data);
		$this->db->limit(1,0);
		return  $uniqueid;
	}
    function updatecustomer()
    {
          $data = array(
            'is_active'    => 1
        );
         $this->db->where('id', $this->_user_id);
        $this->db->update('customers', $data);
    }

    #THIS FUNCTION IS USING FOR GET CUSTOMER BY Id.
    function getcustomerdetails()
    {
        $this->db->select('*');
        $this->db->from('customers');
        $this->db->where('id', $this->_user_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    function getuserotpdetails()
    {
        $this->db->select('*');
        $this->db->from('customer_otp');
        $this->db->where('user_id', $this->_user_id);
        $query = $this->db->get();
        return $query->row_array();
    }

      #check user email before signup
    function checkexistinguserbyid(){
        $this->db->select('*');
        $this->db->from('customers');
        $this->db->where('id', $this->_user_id); 
        $count = $this->db->count_all_results();
        
        if ($count > 0){
            return true;
        }else{
            return false;
        }
    }

    #THIS FUNCTION IS USING FOR ADD TO CART.
    function add_to_cart()
    {

        $saveData=array(
        'id'                   => $this->_cart_id,
        'customer_id'          => $this->_user_id,
        'guest_id'             => $this->_guest_id,
        'product_id'           => $this->_product_id,
        'product_variant_id'   => $this->_product_variant,
        'qty'                  => $this->_product_quantity,
        );
       
        //$cartData=$this->check_cart_product($saveData);
        
        if($saveData['id'])
        {
            //$saveData['id']=$cartData['id'];
            $this->db->where('id', $saveData['id']);
            $this->db->update('cart', $saveData);
            return $saveData['id'];
			
        }else
        {
            $this->db->insert('cart', $saveData);
            return $id = $this->db->insert_id();
            
        }
    }
    function check_cart_product($data)
    {
        $this->db->select('*');
        $this->db->from('cart');
        if(!empty($data['customer_id']))
        {
          $this->db->where('customer_id',$data['customer_id']);
        }else{
            $this->db->where('guest_id',$data['guest_id']);
        }
 
        $this->db->where('product_id',$data['product_id']);
        $this->db->where('product_variant_id',$data['product_variant_id']);
        $query = $this->db->get();
        return $query->row_array();
    }
    
	function get_cartExitProduct()
	{
		$this->db->select('*');
        $this->db->from('cart');
		$this->db->where('customer_id',$this->_user_id);
        $this->db->where('product_id',$this->_product_id);
        $query = $this->db->get();
        return $query->row_array();
	}
    
    #THIS FUNCTION IS USING FOR GET CART PRODUCT.
    function get_cartProduct(){
    
        $this->db->select('product.id,product.product_type,product.name,product.quantity,product.sale_price,product.mrp_price,product.brand_id,product.supplier_id,product.linkhref,product.small_desc,product.display_order,product.is_active,product.is_bestseller,cart.customer_id,cart.qty,cart.guest_id');
        $this->db->from('cart');
        $this->db->join('product', 'product.id=cart.product_id','left');
        if(!empty($this->_product_id) && empty($this->_user_id))
        {
             $this->db->where('product.id', $this->_product_id);
        }
         $this->db->where('cart.customer_id', $this->_user_id);
        
        $result=$this->db->get();
         return $result->result_array();
       
        
    
    }
    
     #THIS FUNCTION IS USING FOR GET CART PRODUCT.
    function get_productfeatures(){
        $this->db->select('f.name as flabel,fv.value_name as fvalue');
        $this->db->from('product_features as pf');
        $this->db->join('features as f', 'f.id=pf.features_id','left');
        $this->db->join('features_value as fv', 'fv.id=pf.features_value_id','left');
        $this->db->where('pf.product_id', $this->_product_id);
        $result=$this->db->get();
        return $result->result_array();
        
    
    }
    
    #THIS FUNCTION IS USING FOR DETETE CART PRODUCT.
    function delete_cartProduct(){
        if(!empty($this->_product_id) && empty($this->_user_id))
        {
            $this->db->where('product_id', $this->_product_id);
        }else if(!empty($this->_product_id) && !empty($this->_user_id)){
            
            $this->db->where('product_id', $this->_product_id);
            $this->db->where('cart.customer_id', $this->_user_id);
        }
        else
        {
            $this->db->where('cart.customer_id', $this->_user_id);
           
        }
        $this->db->delete('cart');
        return true;
    }
    
    #THIS FUNCTION IS USING FOR DELETE USER CART PRODUCT.
     function delete_customer_cart(){
        
        $this->db->where('cart.customer_id', $this->_user_id);
        $this->db->delete('cart');
        return true;
    }

    #THIS FUNCTION IS USING FOR GET PRODUCT PRICE.
    function get_productvariantById()
    {
        $this->db->select('id,product_id');
        $this->db->from('product_variant');
        $this->db->where('product_id', $this->_product_id);
        $this->db->limit(1, 0);
        $query = $this->db->get();
        $data=$query->row_array();
        if(count($data)>0)
        {
            $this->db->select('*');
            $this->db->from('product_combination');
            $this->db->where('product_variant_id', $data['id']);
            $query = $this->db->get();
            return $query->row_array();
        }else{
            return false;
        }
    }

	//Method to check the auth token exist or not
	public function istokenExists() {
		$this->db->select('*');
		$this->db->from('authentication_token');
		$this->db->where('auth_token', $this->_authtoken); 
		$count = $this->db->count_all_results();
		return $count;
    }
    
    //Method to get customer id with the auth token 
	public function getcustomerid() {
		$this->db->select('*');
		$this->db->from('authentication_token');
		$this->db->where('auth_token', $this->_authtoken); 
	    $query = $this->db->get();
        return $query->row_array();
    }
	#THIS FUNCTION IS USING FOR GET USER BILLING ADDRESSES.
	function get_customerBillingAdress(){
		$this->db->select('*');
		$this->db->from('customers_address');
		$this->db->where('customer_id', $this->_user_id);
		$result=$this->db->get();
		return $result->result_array();
	}
	
	#THIS FUNCTION IS USING FOR GET SHIPPING ADDRESSES.
	function get_customerShippingAdress(){
		$this->db->select('*');
		$this->db->from('cust_ship_address');
		$this->db->where('customer_id', $this->_user_id);
		$result=$this->db->get();
		return $result->result_array();
	}
	#THIS FUNCTION IS USING FOR GET CUSTOMER BILL ADDRESS.
	function get_billingAddress(){
		$this->db->select('*');
		$this->db->from('customers_address');
		if(!empty($this->_is_primary))
		{
			$this->db->where('is_primary', $this->_is_primary);
		}
		if(!empty($this->_address_id))
		{
			$this->db->where('id', $this->_address_id);
		}
		$this->db->where('customer_id', $this->_user_id);
		$this->db->order_by('id','DESC');
		//$this->db->limit(1,0);
		$result=$this->db->get();
		return $result->row_array();
	}
	
	#THIS FUNCTION IS USING FOR GET CUSTOMER BILL ADDRESS.
	function get_shippingAddress(){
		$this->db->select('*');
		$this->db->from('cust_ship_address');
		if(!empty($this->_is_primary))
		{
			$this->db->where('is_primary', $this->_is_primary);
		}
		if(!empty($this->_address_id))
		{
			$this->db->where('id', $this->_address_id);
		}
		$this->db->where('customer_id', $this->_user_id);
		//$this->db->limit(1,0);
		$result=$this->db->get();
		return $result->row_array();
	}
	
	#THIS FUNCTION IS USING FOR SAVE CUSTOMER ADDRESS.
    function saveCustomerAddress()
    {
        $data = array(
		
		    'id'                   => $this->_address_id,
            'customer_id'          => $this->_user_id,
            'first_name'           => $this->_fname,
            'last_name'            => $this->_lname,
            'email'                => $this->_username,
            'mobile'               => $this->_phone, 
			'address1'             => $this->_customer_address1,
			'address2'             => $this->_customer_address2,
			'city'                 => $this->_customer_city,
			'state'                => $this->_customer_state,
			'country'              => $this->_customer_country, 
			'pincode'              => $this->_customer_pincode, 
			'phonecode'            => $this->_phonecode,
			'phoneprovider'        => $this->_customer_phoneprovider, 
			'pay_mobile'           => $this->_customer_pay_mobile, 
			'address_details'      => $this->_customer_address_deatails, 
			'location'             => $this->_customer_location, 
			'is_primary'           => $this->_is_primary      
        );
        
        if ($data['id'])
        {
            $this->db->where('id', $data['id']);
            $this->db->update('customers_address', $data);
            return $data['id'];
        }else{
            $this->db->insert('customers_address', $data);
            $id = $this->db->insert_id();
            return $id;
           // $this->db->last_query();
        }
    }
	
	#THIS FUNCTION IS USING FOR SAVE CUSTOMER SHIPPING ADDRESS.
    function saveShippingAddress()
    {
        $data = array(
		    'id'                   => $this->_address_id,
            'customer_id'          => $this->_user_id,
            'first_name'           => $this->_fname,
            'last_name'            => $this->_lname,
            'email'                => $this->_username,
            'mobile'               => $this->_phone, 
			'address1'             => $this->_customer_address1,
			'address2'             => $this->_customer_address2,
			'city'                 => $this->_customer_city,
			'state'                => $this->_customer_state,
			'country'              => $this->_customer_country, 
			'pincode'              => $this->_customer_pincode, 
			'phonecode'            => $this->_phonecode,
			'phoneprovider'        => $this->_customer_phoneprovider, 
			'pay_mobile'           => $this->_customer_pay_mobile, 
			'address_details'      => $this->_customer_address_deatails, 
			'location'             => $this->_customer_location, 
			'is_primary'           => $this->_is_primary       
        );
        // echo "<pre>";
		// print_r($data);
		// exit;
        if ($data['id'])
        {
            $this->db->where('id', $data['id']);
            $this->db->update('cust_ship_address', $data);
            return $data['id'];
        }else{
            $this->db->insert('cust_ship_address', $data);
            $id = $this->db->insert_id();
            return $id;
            // $this->db->last_query();
        }
    }
	
	#THIS FUNCTION IS USING FOR GET CUSTOMER BILL ADDRESS BY ID.
	function get_billingAddressById(){
		$this->db->select('*');
		$this->db->from('customers_address');
		if(!empty($this->_address_id))
		{
		   $this->db->where('id', $this->_address_id);
		}
		$this->db->where('customer_id', $this->_user_id);
		$this->db->limit(1,0);
		$result=$this->db->get();
		return $result->row_array();
	}
	
	#THIS FUNCTION IS USING FOR GET CUSTOMER SIPPING ADDRESS BY ID.
	function get_shippingAddressById(){
		$this->db->select('*');
		$this->db->from('cust_ship_address');
		if(!empty($this->_address_id))
		{
		   $this->db->where('id', $this->_address_id);
		}
		$this->db->where('customer_id', $this->_user_id);
		$this->db->limit(1,0);
		$result=$this->db->get();
		return $result->row_array();
	}
	
	#this function is using for update primary address status.
	function updatePrimaryBillingAddrss($data){
		$this->db->where('id', $data['id']);
        $this->db->update('customers_address', $data);
        return $data['id'];
	}
	
	#this function is using for update primary address status.
	function updatePrimaryShippingAddrss($data){
		$this->db->where('id', $data['id']);
        $this->db->update('cust_ship_address', $data);
        return $data['id'];
	}
	
	#THIS FUNCTION IS USING FOR SAVE CUSTOMER FEEDBACK.
	function saveCustomerFeedback()
	{
		 $data = array(
		    'id'                   => $this->_customer_feedback_id,
            'customer_id'          => $this->_user_id,
			'feedback_id'          => $this->_feedback_id,
			'ratting'              => $this->_feedback_ratting,   
        );
		
        if ($data['id'])
        {
            $this->db->where('id', $data['id']);
            $this->db->update('feedbacks', $data);
            return $data['id'];
        }else{
            $this->db->insert('feedbacks', $data);
            $id = $this->db->insert_id();
            return $id;
            // $this->db->last_query();
        }
	}
	
	#THIS FUNCTION IS USING FOR GET ALL CUSTOMER FEEDBACK BY FEEDBACK ID.
	function get_customerFeedbckById(){
		$this->db->select('*');
		$this->db->from('feedbacks');
		$this->db->where('feedback_id', $this->_feedback_id);
		$this->db->where('customer_id', $this->_user_id);
		$this->db->limit(1,0);
		$result=$this->db->get();
		return $result->row_array();
	}
	
	#THIS FUNCTION IS USING FOR GET ALL CUSTOMER FEEDBACK.
	function get_customerFeedback(){
		$this->db->select('*');
		$this->db->from('feedbacks');
		$this->db->where('customer_id', $this->_user_id);
		$this->db->order_by('id', 'DESC');
		$result=$this->db->get();
		return $result->result_array();
	}
	
	#THIS FUNCTION IS USING FOR GET FEEDBACK.
	function get_feedback(){
		$this->db->select('*');
		$this->db->from('feedback_master');
		$this->db->where('id', $this->_feedback_id);
		$this->db->order_by('id', 'DESC');
		$result=$this->db->get();
		return $result->row_array();
	}
	
	#THIS FUNCTION IS USING FOR ADD PRODUCT TO WISHLIST.
	#THIS FUNCTION IS USING FOR ADD TO CART.
	function add_to_wishlist()
	{

		$saveData=array(
						'id '                  => '',
						'customers_id'         => $this->_user_id,
						'product_id'           => $this->_product_id
		               );
		
	    #check product in wishlist.
		$cartData=$this->check_wishlist_product($saveData);
	
		if(count($cartData)>0)
		{
			$saveData['id']=$cartData['id'];
			$this->db->where('id', $saveData['id']);
            $this->db->update('wishlist', $saveData);
            return $saveData['id'];
		}else
		{
			$this->db->insert('wishlist', $saveData);
            $id = $this->db->insert_id();
            return $id;
		}
	}
	
	#THIS FUNCTION IS USING FOR CHECK PRODUCT IN WISHLIST.
	function check_wishlist_product($data)
	{

	    $this->db->select('*');
        $this->db->from('wishlist');
        $this->db->where('customers_id',$data['customers_id']);
		$this->db->where('product_id',$data['product_id']);
        $query = $this->db->get();
        return $query->row_array();
	}
	
	#THIS FUNCTION IS USING FOR GET WISHLIST PRODUCT.
	function get_wishlistProduct(){
	
		$this->db->select('product.id,product.product_type,product.name,product.quantity,product.sale_price,product.mrp_price,product.brand_id,product.supplier_id,product.linkhref,product.small_desc,product.display_order,product.is_active,product.is_bestseller,wishlist.customers_id,wishlist.product_id');
		$this->db->from('wishlist');
		$this->db->join('product', 'product.id=wishlist.product_id','left');
		$this->db->where('wishlist.customers_id', $this->_user_id);
		if(!empty($this->_product_id))
		{
			$this->db->where('wishlist.product_id', $this->_product_id);
		}
		$result=$this->db->get();
		return $result->result_array();
	
	}
	
	
	#THIS FUNCTION IS USING FOR COUNT WISHLIST.
	function count_wishlist()
	{
		$this->db->select('*');
        $this->db->from('wishlist');
        $this->db->where('product_id', $this->_product_id); 
		 $this->db->where('customers_id', $this->_user_id);
        $count = $this->db->count_all_results();
        if ($count > 0){
            return $count;
        }else{
            return false;
        }
	}
	
	#THIS FUNCTION IS USING FOR COUNT CART ITEMS.
	function count_cart_item()
	{
		$this->db->select('*');
        $this->db->from('cart');
		$this->db->where('customer_id', $this->_user_id);
        $count = $this->db->count_all_results();
        return $count;
	}
	
	#THIS FUNCTION IS USING FOR COUNT CART ITEMS.
	function count_wishlist_item()
	{
		$this->db->select('*');
        $this->db->from('wishlist');
		$this->db->where('customers_id', $this->_user_id);
        $count = $this->db->count_all_results();
        return $count;
	}
	
	#THIS FUNCTION IS USING FOR DETETE WISHLIST PRODUCT.
	function delete_wishlistProduct(){
		$this->db->where('customers_id', $this->_user_id);
		if(!empty($this->_product_id))
		{
			$this->db->where('product_id', $this->_product_id);
		}
		$this->db->delete('wishlist');
		return true;
	}
	
	#THIS FUNCTION USING GET USER USER DEVICE DETAILS.
	function get_deciceDetailsByUserId()
	{
		$this->db->select('*');
		$this->db->from('device_details');
		$this->db->where('customer_id', $this->_user_id);
		$result=$this->db->get();
		return $result->row_array();
	}
	
	function getcategoryimage($subcatId)
	{
		$this->db->select('category_image');
		$this->db->from('category_images');
		$this->db->where('category_id', $subcatId);
		$result=$this->db->get();
		return $result->row_array();
	}
	
	function getproductcategory()
	{
		$this->db->select('categories_id');
		$this->db->from('product_categories');
		$this->db->where('product_id', $this->_product_id);
		$result=$this->db->get();
		return $result->result_array();
	}
	
	#THIS FUNCTION IS USING FOR GET BEST SELLER PRODUCTS.
    function getfeaturedproducts($start,$limit)
    {
        $this->db->select('product.id,product.product_type,product.ref_no as sku,product.name,product.brand_id');
        $this->db->from('product');
        $this->db->where('product.is_active', 1);
        $this->db->where('product.is_featured', 1);
        $this->db->limit($limit,$start);
        $this->db->order_by('product.display_order', 'ASC');
        $query    = $this->db->get();
        $products = $query->result_array();
        return $products;   
    }
	
	#THIS FUNCTION IS USING FOR GET CUSTOMER ORDERS.
	function get_customerOrders()
    {
        $this->db->select('orders.id,orders.order_datetime,orders.customers_id,orders.total_amount,orders.shipping_amount,orders.discount_amount,orders.grand_total,orders.payment_gateway,orders.status,orders.payment_status,order_item.orders_id,order_item.product_id,order_item.product_name,order_item.price,order_item.qty');
        $this->db->from('orders');
        $this->db->join('order_item', 'orders.id = order_item.orders_id','INNER');
        $this->db->where('orders.customers_id', $this->_user_id);
        $query = $this->db->get();
        return $query->result_array();
    }
	
	#THIS FUNCTION IS USING FOR GET CUSTOMER ORDERS.
	function get_orderDetails()
    {
        $this->db->select('orders.id,orders.order_datetime,orders.customers_id,orders.total_amount,orders.shipping_amount,orders.discount_amount,orders.grand_total,orders.sls_grand_total,orders.baddress1,orders.bcity,orders.payment_gateway,orders.status,order_item.orders_id,order_item.product_id,order_item.product_name,order_item.price,order_item.qty');
        $this->db->from('orders');
        $this->db->join('order_item', 'orders.id = order_item.orders_id','INNER');
        $this->db->where('orders.id', $this->_order_id);
        $query = $this->db->get();
        return $query->result_array();
        //$this->db->last_query();
    }
	
	function get_helpCenter()
    {
        $this->db->select('*');
        $this->db->from('help_center');
		$this->db->where('status', 1);
        $query = $this->db->get();
        return $query->result_array();
       
    }
	
	function get_helpCenterById()
    {
        $this->db->select('*');
        $this->db->from('help_center_details');
		$this->db->where('help_id', $this->_help_id);
		$this->db->where('status', 1);
        $query = $this->db->get();
        return $query->result_array();
		
    }
	
	#THIS FUNCTION IS USING FOR SAVE ORDERS.
	function save_order($data){
		if ($data['id'])
        {
            $this->db->where('id', $data['id']);
            $this->db->update('orders', $data);
            return $data['id'];
        }else{
            $this->db->insert('orders', $data);
            $id = $this->db->insert_id();
            return $id;
           // $this->db->last_query();
        }
	}
	
	#THIS FUNCTION IS USING FOR SAVE ORDER ITEMS.
	function order_items($data){
		if ($data['id'])
        {
            $this->db->where('id', $data['id']);
            $this->db->update('order_item', $data);
            return $data['id'];
        }else{
            $this->db->insert('order_item', $data);
            $id = $this->db->insert_id();
            return $id;
           // $this->db->last_query();
        }
	}
	
	#THIS FUNCTION IS USING FOR GET BEST preference.
    function get_preference()
    {
        $this->db->select('*');
        $this->db->from('communication_preferences');
		$this->db->where('status', 1);
        $query = $this->db->get();
        return $query->result_array();   
    }
	
	function update_recomdation($data){
		if($data['id'])
        {
            $this->db->where('id', $data['id']);
            $this->db->update('recommendations', $data);
            return $data['id'];
        }else
        {
            $this->db->insert('recommendations', $data);
            $id = $this->db->insert_id();
            return $id;
        }
	}
	
	function get_recommendation()
    {
        $this->db->select('*');
        $this->db->from('recommendations');
        $query = $this->db->get();
        return $query->result_array();   
    }
	#THIS FUNCTION USING FOR GET CUSTOMER FEEDBACKS.
	// function get_customerFeedbacks(){
		// $this->db->select('id');
        // $this->db->from('feedbacks');
        // $this->db->where('customer_id', $this->_user_id);
        // $query = $this->db->get();
        // $rows= $query->row_array();
		// return $rows;
	// }
	
	#THIS FUNCTION IS USING FOR UPDATE USER PROFILE.
	function upadate_profile()
    {
        $id = $this->_user_id;
        $data = array(
            'first_name'           => $this->_fname,
            'last_name'            => $this->_lname,
            'phone_code'           => $this->_phonecode,
            'gender'               => $this->_user_gender
        );
        $this->db->where('id', $id);
        $this->db->update('customers', $data);
        return $id;
    }
	
	#THIS FUNCTION IS USING FOR GET COUNTARY.
    function  get_country()
	{
		$this->db->select('*');
        $this->db->from('country');
        $query = $this->db->get();
        return $query->result_array(); 
	}
	
	#THIS FUNCTION IS USING FOR GET STATE.
    function  get_state()
	{
		$this->db->select('*');
        $this->db->from('states');
		$this->db->where('country_id', $this->_country_id);
        $query = $this->db->get();
        return $query->result_array(); 
	}
	
	#THIS FUNCTION IS USING FOR GET CITY.
    function get_city()
	{
		$this->db->select('*');
        $this->db->from('cities');
		$this->db->where('state_id', $this->_state_id);
        $query = $this->db->get();
        return $query->result_array(); 
	}
	
	#THIS FUNCTION IS USING GET ORDER STATUS.
	function get_orderStatus()
	{
		$this->db->select('*');
        $this->db->from('order_history');
		$this->db->where('orders_id', $this->_order_id);
		$this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->row_array(); 
	}

    #THIS FUNCTION IS USING FOR GET USD TO SLS VALUE.
    function get_usd_to_sls()
	{
		$this->db->select('*');
        $this->db->from('site_setting');
		$this->db->where('name', 'usd_to_sls_conversion_rate');
        $query = $this->db->get();
        return $query->row_array(); 
	}
	

	function sendBSms($mobileno,$code,$message)
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
	
}