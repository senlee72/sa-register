<?php
	
    //Variable declaration
	$reg_errors;
	global $uname;
	global $email;
	global $ERROR_fname;
	global $ERROR_lnam;
	global $ERROR_dname;
	global $ERROR_byear;
	global $ERROR_mb;
	global $ERROR_cnt;
	global $ERROR_st;
	global $ERROR_CAPTCHA;
    global $wpdb;
    global $destinationPage;
    global $userid;
    global $refid;
    global $webRoot;
    global $regFeeTotal;
    global $contribTotal;
    global $MEMBERSHIP_COST_INDIVIDUAL;

    $MEMBERSHIP_COST_INDIVIDUAL = '15';//TO BE UPDATED WHERE SA MEMBERSHIP PRICE CHANGES
    
    $fname = $lname = $dname = $byear = $mb = $st = $cnt = '';   
   
    //initialize this page
	if (!function_exists('initRegPage')) {
	function initRegPage() {
		global $ERROR_CAPTCHA;
		global $uname;
        global $email;
        global $destinationPage;
        global $webRoot;
        global $userid;

		$current_user = wp_get_current_user();
		$uname = $current_user->user_login;
        $email = $current_user->user_email;
        $webRoot =  parse_url(get_site_url(), PHP_URL_PATH);

        $ctx = "REGISTRATION SUBMIT PAGE >  LOGIN ID ". $uname;
        error_log($ctx);
        $userid = getUserProfile();
        $regid = null;

        $addMemberArr = null;
        $regprice = null;
        if (isset($_POST['SUBMIT_REGISTRATION'])) {
            foreach ($_POST as $key => $value) {
                //echo '<p><strong>' . $key.':</strong> '.$value.'</p>';
                if ($key == 'addMemberArr') {
                    $escValue1 = stripcslashes($value);
                    $addMemberArr = json_decode($escValue1, true);
                    // print_r($arr);
                }
                // if ($key == 'regprice') {
                //     $escValue2 = stripcslashes($value);
                //     $regprice = json_decode($escValue2, true);
                //     // print_r($arr);
                // }                
            }
        }else {
            $url = get_site_url();
            error_log($ctx. " USER SUBMITTED FROM OTHER PAGE ". $url );
            $destinationPage = "REG_SUBMIT_ERROR_PAGE";
            return;
        }
    
        if (!empty($userid)) {
            //update personal details
            updatePersonalDetails();
        }else {
            // insert personal details
            insertPersonalDetails();
        }

        $regid = getRegistration();
        if (!empty($regid)) {//user already registered
            error_log($ctx. " USER IS REGISTERED ALREADY");
            $destinationPage = "REG_SUBMIT_RESUBMITERROR_PAGE";
        }else {// not registered - proceed with registration
            error_log($ctx. " USER REGISTRATON: BEGIN");
            registerUser($addMemberArr, $regprice, $userid);
            $destinationPage = "REG_SUBMIT_PAGE";
        }
    }}

    if (!function_exists('isUserRegistered')) {
        function isUserRegistered() {
         global $wpdb;
         global $uname;
         $sql = "select count(*) from sa_user_info where login_id = '$uname'";
         $rowcount = $wpdb->get_var($sql);
         if ($rowcount < 1) {
             return false;
         }else {
             return true;
         }
    }}
 
    if (!function_exists('getUserProfile')) {
         function getUserProfile() {
          global $wpdb;
          global $uname;
          $sql = "select user_id from sa_user_info where login_id = '$uname' and isactive = 1 ";
          $result = $wpdb->get_var($sql);
          return $result;
    }}
 
      
    if (!function_exists('getMemberSAIfExists')) {
    function getMemberSAIfExists() {
        global $wpdb;
        global $userid;
        $sql = "select member_sa_id from sa_member_sa where user_id = %s";
        $result = $wpdb->get_var($wpdb->prepare($sql, $userid));
    }}

    if (!function_exists('getRegistration')) {
     function getRegistration() {
         global $wpdb;
         global $userid;
 
         $sql = "select reg_id from sa_user_registration where user_id =%s  and status NOT IN ('CANCELLED')";
         $result = $wpdb->get_var($wpdb->prepare($sql, $userid));
         return $result;
    }}

    //Insertion into database 
    if (!function_exists('registerUser')) {
    function registerUser($addMemberArr, $regprice, $userid) {
        try {
            
            if ((float)$_POST['donation'] > 0) {
                error_log("INSERTING DONATION DETAILS ");
                $rDonationID = insertDonationDetails();
            }

            // if (isItemwiseSponsorExist()) {
            //     error_log("INSERTING ITEMWISE SPONSORSHIP DETAILS ");
            //     $rItemSponsorID = insertItemSponsorDetails();
            // }

            //addd for SA membership
            // if ($_POST['membersa']) {
            //     $rMemberID = getMemberSAIfExists($userid);
            //     if (empty($rMemberID)) {
            //         error_log("INSERTING SA MEMBERSHIP DETAILS ");
            //         $rMemberID = insertMemberSADetails($userid);
            //     }else {
            //         error_log("!!!!!USER ". $userid. " IS ALREADY A SA MEMEBR!!!!!");
            //     }
            // }

            $donationID = null;
            $itemSponsorID = null;
            $memberID = null;
            if (!empty($rDonationID) && $rDonationID !='0') $donationID =  $rDonationID;
            if (!empty($rItemSponsorID) && $rItemSponsorID !='0') $itemSponsorID =  $rItemSponsorID;
            if (!empty($rMemberID))  $memberID = $rMemberID;


            error_log("INSERTING REGISTRATION DETAILS ");
            $regid = insertRegistration();
            
            error_log("INSERTING PRICE DETAILS ");
            //$paymentid = insertRegistrationPrice($regprice, $regid, $donationID, $itemSponsorID, $memberID);

            error_log("INSERTING MEMBER DETAILS with Payment ID ". $paymentid);
            insertAddMembers($addMemberArr, $paymentid);

            logActivity("REGISTRATION", $regid, "USER INPUT COMPLETED");
        }catch(Exception $e) {
            echo 'Exception when calling CheckoutApi->createCheckout: ', $e->getMessage(), PHP_EOL;
            logActivity("REGISTRATION", $regid, "USER INPUT FAILED - ".$e->getMessage());
            exit;
        }
    }}


    if (!function_exists('insertPersonalDetails')) {
    function insertPersonalDetails() {
        global $wpdb;
        global $uname;
        global $userid;
        $table_name = "sa_user_info";
        $format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');

        $perArray = [
            "login_id" => $uname,
            "first_name" => $_POST['firstname'],
            "last_name" => $_POST['lastname'],
            "gher_nav" => $_POST['ghernav'],
            "gender" => $_POST['gender'],
            "birth_year" => $_POST['birthyear'],
            "contact_mobile" => $_POST['mobileno'],
            "contact_state" => $_POST['state'],
            "contact_country" => $_POST['country'],
            //"registration_amt" => $_POST['totalRegCost'],
            "created_by" => "pc2023registration"
        ];

        $wpdb -> insert($table_name, $perArray, $format);
        $userid = $wpdb->insert_id;
    }}

    if (!function_exists('updatePersonalDetails')) {
        function updatePersonalDetails() {
            global $wpdb;
            global $uname;
            global $userid;
            $table_name = "sa_user_info";
            $format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s');
            $where = ["user_id" => $userid];
            $perArray = [
                "first_name" => $_POST['firstname'],
                "last_name" => $_POST['lastname'],
                "gher_nav" => $_POST['ghernav'],
                "gender" => $_POST['gender'],
                "birth_year" => $_POST['birthyear'],
                "contact_state" => $_POST['state'],
                "contact_country" => $_POST['country'],
                "updated_by" => "pc2023registration"
            ];
    
            $wpdb -> update($table_name, $perArray, $where);
        }}

    if (!function_exists('insertAddMembers')) {
        function insertAddMembers($addMemberArr, $paymentid) {
            global $wpdb;
            global $userid;
            $table_name = "sa_user_members";
            $format = array('%s','%s','%s','%s','%s','%s','%s');
            // print_r($addMemberArr);
            foreach ($addMemberArr as $mem) {
                // print_r($mem);
                $memArray = [
                    "user_id"           => $userid,
                    "payment_id"        => $paymentid,
                    "m_first_name"      => $mem['fname'],
                    "m_last_name"       => $mem['lname'],
                    "m_relationship"    => $mem['relationship'],
                    "m_birthyear"       => $mem['birthyear'],
                    "m_gender"          => $mem['gender'],
                    "created_by"        => "pc2023registration"
                ];
                $wpdb -> insert($table_name, $memArray, $format);
            }
    }}
    
    if (!function_exists('insertDonationDetails')) {
        function insertDonationDetails() {
            global $wpdb;
            global $userid;
            $table_name = "sa_user_donation";
            $format = array('%s','%s','%s');
    
            $donArray = [
                "user_id" => $userid,
                "donation_amt" => $_POST['donation'],
                "created_by" => "pc2023registration"
            ];
            $wpdb -> insert($table_name, $donArray, $format);
            return $wpdb->insert_id;
    }}

    if (!function_exists('isItemwiseSponsorExist')) {
        function isItemwiseSponsorExist () {
            $itemPost = [
                $_POST['sponsorFoodDay1EveSnack'],
                $_POST['sponsorFoodDay1Dinner'],
                $_POST['sponsorFoodDay1Dessert'],
                $_POST['sponsorFoodDay2MornSnack'],
                $_POST['sponsorFoodDay2Lunch'],
                $_POST['sponsorFoodDay2Dessert'],
                $_POST['sponsorBrochurePrint'],
                $_POST['sponsorSouvenier'],
                $_POST['sponsorVideoPhoto']
            ];
            foreach($itemPost as $item) {
                if ((float)$item > 0.0) return true;
            }
            return false;
        }
    }

    if (!function_exists('insertItemSponsorDetails')) {
        function insertItemSponsorDetails() {
            global $wpdb;
            global $userid;
            $table_name = "sa_user_item_sponsorship";
            $format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');
    
            $evntSponsorArray = [
                "user_id" => $userid,
                "day1EveSnack" => $_POST['sponsorFoodDay1EveSnack'],
                "day1dinner" => $_POST['sponsorFoodDay1Dinner'],
                "day1dessert" => $_POST['sponsorFoodDay1Dessert'],
                "day2MorSnack" => $_POST['sponsorFoodDay2MornSnack'],
                "day2lunch" => $_POST['sponsorFoodDay2Lunch'],
                "day2dessert" => $_POST['sponsorFoodDay2Dessert'],
                "brochure_printing" => $_POST['sponsorBrochurePrint'],
                "souvenir" => $_POST['sponsorSouvenier'],
                "videophoto" => $_POST['sponsorVideoPhoto'],
                "created_by" => "pc2023registration"
            ];
            $wpdb -> insert($table_name, $evntSponsorArray, $format);
            return $wpdb->insert_id;            
    }}    

    //added for SA Membership
    if (!function_exists('insertMemberSADetails')) {
        function insertMemberSADetails() {
            global $wpdb;
            global $userid;
            global $MEMBERSHIP_COST_INDIVIDUAL;

            $table_name = "sa_member_sa";
            $format = array('%s','%s','%s');

            $membership_cost = $_POST['membersa'];
            $membership_type = 'FAMILY';

            if ($membership_cost == $MEMBERSHIP_COST_INDIVIDUAL) {
                $membership_type = 'INDIVIDUAL';
            }

            $donArray = [
                "user_id" => $userid,
                "membership_type" => $membership_type,
                "created_by" => "pc2023registration"
            ];
            $wpdb -> insert($table_name, $donArray, $format);
            return $wpdb->insert_id;
    }}

    

    if (!function_exists('insertRegistrationPrice')) {
        function insertRegistrationPrice($regprice, $regid, $donationID, $itemSponsorID,$memberID ) {
            global $wpdb;
            global $userid;
            $table_name = "sa_user_regprice";
            $format = array('%s','%s','%s','%s');
            $regpriceid = null;

            $regpriceArray = [
                "user_id" => $userid,
                "reg_id" => $regid,
                "base_price" => $regprice['baseRegPrice'],
                "personal" => $regprice['registrantPrice'],
                "spouse" => $regprice['spousePrice'],
                "kids_cnt" => $regprice['kidCnt'],
                "kids_childcnt" => $regprice['childCnt'],
                "kids_youthcnt" => $regprice['kidsYouthCnt'],
                "kids_adultcnt" => $regprice['adultKidCnt'],
                "kids_price" => $regprice['kidsPrice'],
                "kids_5belowPrice" => $regprice['kids5BelowPrice'],
                "kids_18belowPrice" => $regprice['kids18BelowPrice'],
                "kids_18abovePrice" => $regprice['kids18AbovePrice'],
                "guest_cnt" => $regprice['guestCnt'],
                "guest_price" => $regprice['guestPrice'],
                "subtotal1_orig" => $regprice['subTotal1Orig'],
                "subtotal1" => $regprice['subTotal1'],
                "donation_refid" => $donationID,
                "donation_total" => $regprice['donation'],
                "itemsponsor_refid" => $itemSponsorID,
                "member_sa_refid" => $memberID,
                "member_sa_amt" => $regprice['membersa'],
                // "discount" => $regprice[''],
                "subtotal2" => $regprice['subTotal2'],
                "total" => $regprice['total'],
                "created_by" => "pc2023registration"
            ];
            $wpdb -> insert($table_name, $regpriceArray, $format);
            $regpriceid = $wpdb->insert_id;
            insertPaymentDetails($regpriceid, $regprice['subTotal1'], $regprice['subTotal2'], $donationID, $itemSponsorID, $memberID); 
    }}   

    if (!function_exists('insertRegistration')) {
        function insertRegistration() {
            global $wpdb;
            global $userid;
            $table_name = "sa_user_registration";
            $format = array('%s','%s','%s','%s');
            
            $hearaboutus = $_POST['hearabout'];
            if (empty($hearaboutus)) $hearaboutus = "";

            $datareuse = "NO";
            if (isset($_POST['datareuse'])) {
                $datareuse = "YES";
            }
            
            //error_log(">>>>>>>>>HEARABOUT ".$_POST['hearabout']);
            //error_log(">>>>>>>>>DATA RESUSE CONSENT ".$_POST['datareuse']);

            //EVENT TYPES: REGISTRATION, PAYMENT, PREFERENCE, CANCELLATION
             // STATUS: INITIATED, INPROGRESS, COMPLETED
            $statusArray = [
                "user_id" => $userid,
                "hearaboutus" => $hearaboutus,
                "datareuse" => $datareuse,
                "status" => "PENDING_PAYMENT",
                "created_by" => "pc2023registration"
            ];
            $wpdb -> insert($table_name, $statusArray, $format);
            return $wpdb->insert_id;
    }}    

    if (!function_exists('logActivity')) {
        function logActivity($event, $refid, $comment) {
            global $wpdb;
            global $userid;
            $table_name = "sa_user_activity_log";
            $format = array('%s','%s','%s','%s');
    
            //EVENT TYPES: REGISTRATION, PAYMENT, PREFERENCE, CANCELLATION
             // STATUS: INITIATED, INPROGRESS, COMPLETED
            $statusArray = [
                "user_id" => $userid,
                "event" => $event,
                "ref_id" => $refid,
                "comment" => $comment,
                "created_by" => "pc2023registration"
            ];
            $wpdb -> insert($table_name, $statusArray, $format);
    }}        

	/** **/
	if (!function_exists('validate')) {
	function validate($uname, $email, $fname, $lname, $dname, $byear, $mb, $st, $cnt)  
	{
		$reg_errors = new WP_Error;
		global $ERROR_fname;
		global $ERROR_lnam;
		global $ERROR_dname;
		global $ERROR_byear;
		global $ERROR_mb;
		global $ERROR_cnt;
		global $ERROR_st;

		$res = captchaResponse();
		if (empty($res) || !$res->success) {
			$reg_errors->add('field', 'Please click Captcha before submission');
		}
		
		//echo $uname . " " . $email  . " " . $fname  . " " . $lname . " " . $dname . " " . $byear . " " . $mb  . " " . $st . " " . $cnt; 
		if (empty($fname) ) {
			$reg_errors->add('field', 'First Name is missing');
			$ERROR_fname = "color:red";
		}
		if (empty($lname) ) {
			$reg_errors->add('field', 'Last Name is missing');
			$ERROR_lname = "color:red";
		}
		if (empty($dname) ) {
			$reg_errors->add('field', 'Display Name is missing');
			$ERROR_dname = "color:red";
		}
		if (empty($byear)) {
			$reg_errors->add('field', 'Birth Year is missing');
			$ERROR_byear = "color:red";
		}
		if (empty($mb) ) {
			$reg_errors->add('field', 'Mobile# is missing');
			$ERROR_mb = "color:red";
		}
		if (empty($st) ) {
			$reg_errors->add('field', 'State is missing'); 
			$ERROR_st = "color:red";
		}
		if (!validate_username($uname) ) {
			$reg_errors->add('username_invalid', 'Sorry, the username you entered is not valid');
		}
		if (!is_email($email)) {
			$reg_errors->add('email_invalid', 'Email is not valid');
		}
		if (is_wp_error($reg_errors) && ! empty($reg_errors->errors)) {
			foreach ( $reg_errors->get_error_messages() as $error ) {
				echo '<div style="color:red">';
				echo '<strong>ERROR</strong>:';
				echo $error . '<br/>';
				echo '</div>';
			}
			return false;
		}
		return true;
	}
	}

    if (!function_exists('insertPaymentDetails')) {
	function insertPaymentDetails($regpriceid, $subtotal1, $subtotal2, $donationid, $itemid, $memberSAID) {
        global $wpdb;
        global $uname;
        global $userid;
        global $refid;
        global $regFeeTotal;
        global $contribTotal;        

        $regFeeTotal = $subtotal1;
        $contribTotal = $subtotal2;        

        $refid = hash('ripemd160','pc2023PalkaR#'.$uname.date("l jS \of F Y h:i:s A"). $userid);
        $table_name = "sa_user_payment";
		$format = array('%s','%s','%s','%s','%s','%s');

		$paymentArr = [
			"user_id" => $userid,
			"subtotal1" => $subtotal1, 
			"subtotal2" => $subtotal2,
            "reference_id" => $refid,
            "donation_id" => $donationid,
            "item_id" => $itemid,
            "member_sa_id" => $memberSAID,
            "status" => "PENDING",
            "regprice_id" => $regpriceid, 					
			"created_by" => "pc2023registration"
		];
        $wpdb -> insert($table_name, $paymentArr, $format);
        return $wpdb->insert_id;
	}}

    if (!is_user_logged_in()) {
        $destinationPage = "NO_LOGIN_PAGE";
    }else {
        initRegPage();
    }  
	
?><?php if ($destinationPage =='REG_SUBMIT_PAGE') { ?>
<!-- TOP NAVIGATION BAR: START -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Registration Payment</a>
    </div>
</nav>
<!-- TOP NAVIGATION BAR: EMD -->

<div id="card-personal" class="card mb-4">
    <div class="card-header">
        <p class="h6 text-secondary small font-italic"><b>Please use the 'Pay Now' button to proceed to payment. Please DO NOT use Browser back button to edit the registration. It won't be saved. Instead please use the Edit options available in Convention Registration Home Page</b></p>
    </div>

    <div class="card-body" id="divCardBody">
            <p>You have successfully entered the registration details! </p>
            <p>Your Payment Details are listed below. <b>All amounts are listed in US Dollars.</b></p>
            <p class="mr-3">Total Registration Fee<label id="modal-regcost" class="oi oi-dollar px-3"><b><?=$regFeeTotal?></b></label></p>
            <p class="mr-3">Total Contribution<label id="modal-regcost" class="oi oi-dollar px-3"><b><?=$contribTotal?></b></label></p>

            <p><h3>Instructions on Payment - Please read this carefully</h3></p>
            <p>On clicking the 'Pay Now' button you'll be redirected to the payment page hosted by Stripe.com. Stripe payment site will be collecting the credit card payment details and processing the transaction. You DON'T need to have a Stripe account to complete this transaction. The registration price and donation amounts are shown as separate line items in the payment page. Please verify them before submitting the payment.</p>
            <p>Please remember that if you can't complete the transaction for any reason, please revisit the Registration page to complete any pending transaction.</p>
            <p>After submitting your credit card details, you'll be taken to a confirmation page displaying your transaction details. Please save the information for future reference.</p>
        <div>
            <form action="<?=$webRoot?>/striperegcheckout.php" method="post" class="form-inline mb-4">
                <input type="hidden" name="email" value="<?=$email?>"/>
				<button  class="btn btn-outline-success my-sm-0" type="submit" name="refid" value="<?=$refid?>">Pay Now</button>
            </form>      
        </div>
    </div>
</div>
<?php } else if ($destinationPage =='REG_SUBMIT_ERROR_PAGE') { ?>
        <!-- TOP NAVIGATION BAR: START -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Registration - SUBMISSION FAILURE</a>             
            </div>
        </nav>
        <!-- TOP NAVIGATION BAR: EMD -->
        
        <div id="card-personal" class="card mb-4">
            <div class="card-header">
                <p class="h4">Oops!!!</p>
                <p class="h6 text-secondary small font-italic">You submission didn't go through. Please retry again. If you see this failure multiple times, please reach out to pc2023 support team.</p>
            </div>
        </div>   
        <div class="d-inline p-2">
            <a type="button" id="btn-edit" name="btn-edit" value="Back" class="btn btn-primary btn-lg" href="<?=$webRoot?>/regland">Home</a>
        </div>        
<?php } else if ($destinationPage =='REG_SUBMIT_RESUBMITERROR_PAGE') { ?>
        <!-- TOP NAVIGATION BAR: START -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Registration - USER HAS ALREADY SUBMITTED THE REGISTRATION DETAIL!!!</a>             
            </div>
        </nav>
        <!-- TOP NAVIGATION BAR: EMD -->
        
        <div id="card-personal" class="card mb-4">
            <div class="card-header">
                <p class="h4">Oops!!!</p>
                <p class="h6 text-secondary small font-italic">You may get this error if you're trying to resubmit the registration detail. Please go to Convention home page to update the details.</p>
            </div>
        </div>   
        <div class="d-inline p-2">
            <a type="button" id="btn-edit" name="btn-edit" value="Back" class="btn btn-primary btn-lg" href="<?=$webRoot?>/regland">Home</a>
        </div>    
<?php } else if($destinationPage == 'NO_LOGIN_PAGE') {?>
    <!-- TOP NAVIGATION BAR: START -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">LOGIN - ERROR</a>             
        </div>
    </nav>
    <!-- TOP NAVIGATION BAR: EMD -->
    
    <div id="card-personal" class="card mb-4">
        <div class="card-header">
            <p class="h4">Oops!!!</p>
            <p class="h6 text-secondary small font-italic">You have reached the page in error. Either you aren't logged in or the session has timed out. Please click here to re-login</p>
        </div>
        <div class="d-inline p-2">
            <a type="button" id="btn-edit" name="btn-edit" value="Back" class="btn btn-primary btn-lg" href="<?=$webRoot?>/login">Home</a>
        </div>
    </div>

<?php } else {?>
    <!-- TOP NAVIGATION BAR: START -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Registration - ERROR</a>             
        </div>
    </nav>
    <!-- TOP NAVIGATION BAR: EMD -->
    
    <div id="card-personal" class="card mb-4">
        <div class="card-header">
            <p class="h4">Oops!!!</p>
            <p class="h6 text-secondary small font-italic">You have reached the page in error. Please click here for <a href="../regland">Registration Home Page</a></p>
        </div>
    </div>  
<?php }?><!-- REGISTRATION SUBMISSION PAGE v5.3.1-->