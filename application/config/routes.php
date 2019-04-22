<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['feedback_master']	         = 'Ziqqi_api/feedback_master';
$route['language_master']	         = 'Ziqqi_api/language_master';
$route['currency_master']	         = 'Ziqqi_api/currency_master';
$route['signup']	                 = 'Ziqqi_api/signup';
$route['resendotp']	                 = 'Ziqqi_api/resendotp';
$route['verifyotp']	                 = 'Ziqqi_api/verifyotp';
$route['socialSignup']	             = 'Ziqqi_api/socialSignup';
$route['login']	                     = 'Ziqqi_api/login';
$route['forgot_password']	         = 'Ziqqi_api/forgot_password';
$route['home_banners']	             = 'Ziqqi_api/home_banners';
$route['home_categories']	         = 'Ziqqi_api/home_categories';
$route['getcategoryProduct']	     = 'Ziqqi_api/getcategoryProduct';
$route['productDetails']	         = 'Ziqqi_api/productDetails';
$route['categorysearch']	         = 'Ziqqi_api/categorysearch';
$route['homesearch']	             = 'Ziqqi_api/homesearch';
$route['similar_products']	         = 'Ziqqi_api/similar_products';
$route['addTowishlist']	             = 'Ziqqi_api/addTowishlist';
$route['veiwWishlistProduct']	     = 'Ziqqi_api/veiwWishlistProduct';
$route['deleteWishlistProduct']	     = 'Ziqqi_api/deleteWishlistProduct';
$route['deals']	                     = 'Ziqqi_api/deals';
$route['addTocart']	                 = 'Ziqqi_api/addTocart';
$route['viewCartProduct']	         = 'Ziqqi_api/viewCartProduct';
$route['deleteCartProduct']	         = 'Ziqqi_api/deleteCartProduct';
$route['getBillingAddress']	         = 'Ziqqi_api/getBillingAddress';
$route['getShippingAddress']	     = 'Ziqqi_api/getShippingAddress';
$route['addBillingAddress']	         = 'Ziqqi_api/addBillingAddress';
$route['addShippingAddress']	     = 'Ziqqi_api/addShippingAddress';
$route['addCustomerFeedback']	     = 'Ziqqi_api/addCustomerFeedback';
$route['getCustomerFeedback']	     = 'Ziqqi_api/getCustomerFeedback';
$route['bestSellerProduct']	         = 'Ziqqi_api/bestSellerProduct';
$route['getMyOrders']	             = 'Ziqqi_api/getMyOrders';
$route['getorderDetails']	         = 'Ziqqi_api/getorderDetails';
$route['getHelpCenters']	         = 'Ziqqi_api/getHelpCenters';
$route['getHelpCenterById']	         = 'Ziqqi_api/getHelpCenterById';
$route['placeOrder']	             = 'Ziqqi_api/placeOrder';
$route['updateCustomerProfile']	     = 'Ziqqi_api/updateCustomerProfile';
$route['country_master']	         = 'Ziqqi_api/country_master';
$route['getState']	                 = 'Ziqqi_api/getState';
$route['getCity']	                 = 'Ziqqi_api/getCity';


$route['default_controller'] = 'Ziqqi_api';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
