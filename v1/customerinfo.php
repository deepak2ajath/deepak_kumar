<?php /*@@@Tikam*/
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

 $host="localhost";
 //$dbname="ziqqi_newzqdb";
 $dbname="ziqqi_12019";
 $username="ziqqi_zuser";
 $password="09hPM+AeI_MT";

 $zqdb1 = mysql_connect($host,$username,$password);
 mysql_select_db($dbname,$zqdb1);

  

 if($_POST['ordid'] && $_POST['customer'] && $_POST['notes'])
 {
  $cus = $_POST['customer'];
  $ooid = $_POST['ordid'];
  $not = $_POST['notes'];
  
  $ins = mysql_query('INSERT INTO customer_call_enq SET customer_id = '.$cus.'. , orderid = '.$ooid.' , notes = "'.$not.'" ');
  if($ins)
  
   
   $res ='<tr> 
      <td data-title="Order Date">'.date("H:i").'</td>
      <td data-title="Order Time">'.date("Y-m-d").'</td>
      <td data-title="Order ID">#'.$ooid.'</td>
      <td data-title="Notes">'.$not.'</td>
    </tr>' ;

    echo $res ; 
  
  
  exit ;
 }

  $customers_arr = array();
  $customers_arr['records'] = array();
  $mobile = $_GET['phone'] ;
  $mobiles = '' ;
   $customer_sql = "SELECT * FROM `orders` WHERE 
                    bmobile = '$mobile' OR pickup_mobile = '$mobile' 
                    OR  pay_mobile  = '$mobile' ORDER BY id DESC LIMIT 1 " ;
  $customers_query = mysql_query($customer_sql);
  $num_customers = mysql_num_rows($customers_query);
  if($num_customers > 0)
  {
    while ($customers_rows = mysql_fetch_assoc($customers_query)) 
    {
       extract($customers_rows);

       if($pickup_mobile==$mobile)
       {
          $name =  $bfname.' '.$blname ;  
          $mobiles = $pickup_mobile;
       }
       elseif($bmobile==$mobile)
       {
         $name =  (empty($bfname)) ? $pickup_name : $bfname.' '.$blname ;
         $mobiles = $bmobile;
       }
       elseif($pay_mobile==$mobile)
       {
          $name = (empty($pickup_name)) ?  $bfname.' '.$blname : $pickup_name ;
          $mobiles = $pay_mobile;
       }
       else 
       {
         $error = 'The record doesnt exist.';
       }

        $customers_row=array( 'data' => array(
            "cust_id" => $customers_id,
            "name" => $name,
            "email" => $bemail,
            "mobile" => $mobiles
          )
        );

     // $customers_row = $customers_rows ;
      //  array_push($customers_arr["records"], $customers_row);

    }

    


     http_response_code(200);
    // show products data in json format
    //echo json_encode($customers_row);
 
   $customersInfo = (object) $customers_row['data'] ;
   $customers_id = $customersInfo->cust_id ; 
   $customers_name = $customersInfo->name ; 
   $customers_r_phone = $customersInfo->name ; 
   $customers_email = $customersInfo->email ; 
  //print_r($customersInfo) ;  

  //Customers Details 
    $cd = mysql_query("SELECT * FROM `customers` WHERE id = $customers_id") ;
    $cd_fetch = mysql_fetch_object($cd);
   // print_r($cd_fetch) ; 
    $cd_arr = array(
                  "Customer Name" => $cd_fetch->first_name.' '. $cd_fetch->last_name,
                  "Customer Email" => $cd_fetch->email,
                  "Registered Mobile" => $cd_fetch->phone_code.'-'.$cd_fetch->mobile,
                  "Registration Date" => $cd_fetch->reg_date
    ) ; 
    
    //Customers Billing Information 

     $cb = mysql_query("SELECT * FROM `customers_address` WHERE customer_id = $customers_id order by id desc LIMIT 1") ;
     $cb_fetch = mysql_fetch_object($cb);
     // print_r($cb_fetch) ; 
    $cb_arr = array(
                  "Billing Name" => $cb_fetch->first_name.' '. $cd_fetch->last_name,
                  "Billing Country" => $cb_fetch->country,
                  "Billing address1" => $cb_fetch->address1,
                  "Billing address2" => $cb_fetch->address2,
                  "Billing Email" => $cb_fetch->email,
                  "Billing Mobile" => $cb_fetch->mobile,
                  "Address Details" => $cb_fetch->address_details
                  
    ) ; 

//Customers Orders Details 
    $co_arr = array() ; 
    $co = mysql_query("SELECT * FROM `orders` WHERE customers_id = $customers_id order by id desc") ;

    $co1_fetch = mysql_fetch_object($co);
//    print_r($co1_fetch);
    $i = 0 ;  
     while($co_fetch = mysql_fetch_object($co))
     {  $i++ ; 
       $co_arr[$i]['orderid'] =  $co_fetch->id ; 
       $co_arr[$i]['orderdate'] =  $co_fetch->order_datetime ; 
       $co_arr[$i]['orderstatus'] =  $co_fetch->status ; 
       $co_arr[$i]['ordertotal'] =  $co_fetch->grand_total ; 
     }


  //Pay Phone number Details 
     $cp =  mysql_query("SELECT DISTINCT(pay_mobile), bphoneprovider , bphonecode FROM orders WHERE customers_id = $customers_id AND bphoneprovider !='' AND pay_mobile !='' ORDER BY id DESC") ;
     $provider_zaad = array();
     $provider_edhab = array();
     $za = 0 ;
    while ($cp_fetch = mysql_fetch_object($cp) ) {
      $za++;
      $provider = $cp_fetch->bphoneprovider;
      if($provider=='ZAAD')
      {
        $provider_zaad[$za]=  $cp_fetch->pay_mobile ; 
      }
      else
      {
        $provider_edhab[$za]=  $cp_fetch->pay_mobile ;
      }

       
    }
    
   //Reasons for call 

    //Customers Orders Details 
    $re_arr =  array() ; 
    $re = mysql_query("SELECT * FROM `reason_call` WHERE status = 1 ") ;

    $i = 0 ;  
     while($re_fetch = mysql_fetch_object($re))
     {  $i++ ; 
       $re_arr[$i]['id'] =  $re_fetch->id ; 
       $re_arr[$i]['reasons'] = $re_fetch->reason ;
     }

     //Customer Notes 

    $call_enq =  mysql_query("SELECT * FROM `customer_call_enq`  WHERE customer_id = $customers_id") ;
     $no = 0 ;
     $call_arr = array() ; 
    while ($call = mysql_fetch_object($call_enq) ) {
      $no++;
     
      $call_arr[$no]['orderid'] = $call->orderid ; 
      $call_arr[$no]['notes'] = $call->notes ; 
      $call_arr[$no]['date'] = date('Y-m-d' , strtotime($call->date_added)) ; 
      $call_arr[$no]['time'] = date('H:i' , strtotime($call->date_added)) ; 

       
    }


       /* echo "<pre>" ; 
        print_r($call_arr);
        echo "</pre>" ;*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
<!-- Basic page needs -->
<meta charset="utf-8">
<!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <![endif]-->
<meta http-equiv="x-ua-compatible" content="ie=edge">
<title>Ziqqi - Customer Support</title>
<meta name="description" content="">

<!-- Mobile specific metas  -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
<link rel="stylesheet" href="style.css">
</head>
<body>
  
<script type="text/javascript" src="js/jquery.min.js"></script> 
<script type="text/javascript" src="js/bootstrap.min.js"></script> 

<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
<style>
.dataTables_wrapper .col-sm-6 {
    width: 100%;
}
.form-inline .form-control{
    width: 100% !important;
}
.dataTables_filter label{
    width: 100% !important;
}
.dataTables_wrapper .col-sm-7{
    width: 100% !important;
}
.pagination > .active > a, .pagination > .active > a:focus, .pagination > .active > a:hover, .pagination > .active > span, .pagination > .active > span:focus, .pagination > .active > span:hover{
    background-color:#d9f3d0 !important;
    border-color:#d9f3d0 !important;
    }
.pagination > li > a:focus, .pagination > li > a:hover, .pagination > li > span:focus, .pagination > li > span:hover{
    color:#000 !important;
    }
    .pagination > li > a, .pagination > li > span{
        color:#000 !important;
    }
</style>
</body>
<!--  Main Container -->
<section class="main-container">
  <div class="main container-fluid">
    <div class="row-fluid">
      <div class="col-main col-sm-4 col-xs-12">
        <div class="my-account">
          <div class="page-title">
            <h2>Orders</h2>
          </div>


          <div class="orders-list table-responsive"> 
              <!--orders list table-->
              <table id="orderlist"  class="table table-bordered cart_summary table-striped">
                <thead>
                  <tr> 
                    <!--titles for td-->
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                 
                  <?php foreach($co_arr as $ord) : ?>
                    <tr> 
                    <td data-title="Order ID">#<?php echo $ord['orderid']; ?></td>
                    <td data-title="Order Date"><?php echo $ord['orderdate']; ?></td>
                    <td data-title="Order Status"><?php echo $ord['orderstatus']; ?></td>
                    <td data-title="Total"><span class="order-total">$<?php echo $ord['ordertotal']; ?></span></td>
                   </tr>
                   <?php endforeach;  ?>
                 
                  
                </tbody>
              </table>
            </div>
        </div>
      </div>

     

      <div class="col-main col-sm-4 col-xs-12">
        <div class="my-account">
          <div class="page-title">
            <h2>Customer Details</h2>
          </div>
          <div class="block-content">
            <table>
            <?php
              foreach ($cd_arr as $key => $data) {
                echo '<tr><td><b>'.$key.'</b></td><td> : '.$data ."</td></tr>" ;   
              }
             ?>
             </table>
        </div>
        </div>

        <div class="my-account">
          <div class="page-title">
            <h2>Notes for this call</h2>
          </div>
          <div class="block-content">
          <h5>REASON FOR CALL</h5>
          <span id="msgg" style="color: red"></span>
          <div class="form-selector">
          <label>Order</label>
          <select class="form-control" name="ordid" id="ordid">
            <option value="">Please Select order id </option>
            <?php foreach ($co_arr as  $arr) { ?>
              <option value="<?php echo $arr['orderid']; ?>"><?php echo $arr['orderid']; ?></option>
           <?php } ?>
          </select>
        </div>

        <div class="form-selector">
          <label>Reasons</label>
          <select class="form-control" name="ressons" id="reasons">
           <option value="">Please Select </option>
            <?php foreach ($re_arr as  $arr) { ?>
              <option value="<?php echo $arr['reasons']; ?>"><?php echo $arr['reasons']; ?></option>
           <?php } ?>
          </select>
        </div>


        <div class="form-selector">
          <label>Notes</label>
          <textarea class="form-control input-sm" rows="2" id="notes"></textarea>
        </div>
          <button class="button" id="save"><span>SAVE</span></button>
        </div>
        </div>

        <div class="my-account">
          <div class="page-title">
            <h2>Notes for old calls</h2>
          </div>
          <div class="orders-list table-responsive"> 
              <!--orders list table-->
              <table class="table table-bordered cart_summary table-striped" id="notes_list">
                <thead>
                  <tr> 
                    <!--titles for td-->
                    <th>Date</th>
                    <th>Time</th>
                    <th>Order ID</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($call_arr as $call) : ?>
                   
                    <tr> 
                    <td data-title="Order Date"><?php echo $call['date']; ?></td>
                    <td data-title="Order Time"><?php echo $call['time']; ?></td>
                    <td data-title="Order ID">#<?php echo $call['orderid']; ?></td>
                    <td data-title="Notes"><?php echo $call['notes']; ?></td>
                  </tr>
                      
                  <?php endforeach;  ?>
                 
                </tbody>
              </table>
            </div>
        </div>

      </div>


      <div class="col-main col-sm-4 col-xs-12">
        <div class="my-account">
          <div class="page-title">
            <h2>Addresses</h2>
          </div>
          <div class="block-content">
          <h5>BILLING ADDRESS</h5>
          <table>
          <?php
              foreach ($cb_arr as $key => $data) {
                echo '<tr><td><b>'.$key.'</b></td><td> : '.$data ."</td></tr>" ;   
              }
             ?>
             </table>
                   </div>
        </div>


        <div class="my-account">
          <div class="page-title">
            <h2>Phone number used for payments</h2>
          </div>
          <div class="block-content">
          <div class="row">
                    <div class="col-sm-6">
                    <h5>ZAAD</h5>
                      <ul>
                         <?php foreach ($provider_zaad as $key => $data) { echo '<li>+252 '.$data ."</li>" ;  } ?> 
                      </ul>
                    </div>
                    <div class="col-sm-6">
                    <h5>EDAHAB</h5>
                      <ul>
                        <?php foreach ($provider_edhab as $key => $data) { echo '<li>+252 '.$data ."</li>" ;    } ?> 
                     </ul>
                    </div>
                    </div>
                   </div>
        </div>


      </div>

    </div>
  </div>
</section>
<!--  End Main Container -->
</html>

 <script type="text/javascript">
        $(document).ready(function() {
           $('#orderlist').DataTable( 
           { "paging": true, "info": false,  "lengthChange":false  } );
       } );

     $(document).ready(function(){
            $("#reasons").on('change',function(){
                   var rsn = $(this).val();
                   $('#notes').val(rsn);
            });
        });


     $(document).ready(function(){
            $("#save").on('click',function(){
                 
                 var ordid = $('#ordid').val();    
                 var notes = $('#notes').val();    
                 var customer = "<?php echo $customers_id;  ?>"; 

                  if(customer && notes && ordid)
                  {
                    $.ajax({
                      type: "POST",
                      url: "<?php echo $_SERVER['SCRIPT_URI'] ;  ?>",
                      data: {ordid: ordid,
                              customer: customer,
                             notes: notes},
                      success: function(msg){

                        if(msg)
                        {

                          $( "#notes_list" ).append( msg );

                         $('#notes').val('');
                         $('#ordid').val('');
                         $('#reasons').val('');
                         $('#msgg').html('Records updated successfully.');
                        }

                          },
                        });
                  } 
                  else
                  {
                    alert('Missing fields') ;
                  }

               

            });
        });

     

        </script>



   <?php

}
    else
    {
       
       echo 'The record doesnt exist.' ; 

       $customers_row=array( 'data' => array(
            "error" => 'The record doesnt exist.'
          )
        );
    }