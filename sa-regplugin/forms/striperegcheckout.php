<?php
	// LOADING ALL THE REQUIRED LIBRARIES
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	$basePath = $_SERVER['DOCUMENT_ROOT'] .'/wp-content/plugins/stripe';
	error_log("INSIDE STRIPECHECKOUT PAGE: Basepath: ".$basePath);
	require_once $basePath."/init.php";
	header('Content-Type: application/json');
	
	use \Stripe\Stripe;
	use \Stripe\Checkout;
	use \Stripe\StripeClient;
	
	global $destinationPage;
	global $wpdb;
	global $total_adultcnt;
	global $total_kidscnt;
	global $total_donationcnt;
	global $user_id;
	global $userLoginID;
	global $userPhone;
	global $userEmail;
	global $membersacnt;
	global $memberFAMILYCOST;
	global $memberINDCOST;
	global $prd_mem_sa;
	global $prd_conv_donate;
	global $prd_conv_adult;
	global $prd_conv_kid;
	global $apikey;

	//testmode data
	$apikey = "sk_test_51MmQvkHn7Uww4RvuWGdrdkXH2qEJBcir4iw5VFXfTVikierK3NRztYKei7vsb3nYgpWJdgftZLZrlEhiGoOjQP0C00NQfMcnws"; //Test Key	
	$prd_conv_adult = "price_1MueyjHn7Uww4RvuOrqH7jaJ";
	$prd_conv_kid = "price_1MsI3CHn7Uww4Rvuwkrm1kWE";
	$prd_mem_sa = "price_1MuyWqHn7Uww4Rvu3W9DSf8U";
	$prd_conv_donate = "price_1MsCooHn7Uww4Rvuab2hpMDM";

	$memberFAMILYCOST = 30;
	$memberINDCOST = 15;
	$membersacnt = 0;

	function initPage() {
		global $userLoginID;
		if(isset($_POST['refid'])){     
			initCheckout($_POST['refid'], $_POST['email']);
		}else {
			$current_user = wp_get_current_user();
			$userLoginID = $current_user->user_login;
			
			error_log("EXISTING LOGIN SESSION>>>>>>  LOGINID: " .$userLoginID);			
			if (empty($userLoginID)) {
				$destinationPage = 'NO_LOGIN_USER';				
			}else {
				if (isCheckoutIDProgress($userLoginID)) {
					redirectToConfirmPage();
				}else {
					$destinationPage = 'PAYMENT_ERROR_PAGE';
				}
			}
		}
	}
	
	if (!function_exists('isCheckoutIDProgress')) {
	function isCheckoutIDProgress($userLoginID) {
		global $wpdb;
		$checkoutID = null;
	
		$table_name = "pc2023_user_info";
		$userSQL = "SELECT user_id FROM wp_users a, pc2023_user_info b WHERE a.user_login = %s and a.id = b.user_id";
		$userResult = $wpdb->get_row($wpdb->prepare($userSQL, $userLoginID));
		$userID = $userResult->user_id;
		
		$userPaymentSQL = "SELECT checkout_id from pc2023_user_payment WHERE user_id = %s";
		$userPaymentSQL = $wpdb->get_row($wpdb->prepare($userPaymentSQL, $userID));
		if (!(empty($userPaymentSQL))) $checkoutID = $userPaymentSQL->checkout_id;
		error_log("IN STRIPE PAYMENT>>> RETRIEVING ANY EXISTING CHECKOUTID>>> " . $checkoutID);
		
		if (empty($checkoutID)) return false;
		
		try {
			$stripe = getStripeClient();
			$checkout_session  = $stripe->checkout->sessions->retrieve($checkoutID, []);
			error_log("CHECKOUT SESSION INFO: " .$checkout_session);
			
			$paymentIntent = $checkout_session['payment_intent'];
			$paymentStatus = $checkout_session['payment_status'];
			$checkoutStatus = $checkout_session['status'];
			$totalAmount = $checkout_session['amount_total'];
			if (!empty($paymentIntent)) {
				error_log("CHECKOUT SESSION EXISTS: SKIPPING CHECKOUT");
				return true;
			}
			
		} catch (Exception $e1) {
			error_log( 'Exception when calling Stripe API: '. $e1->getMessage());
		}
		return false;
	}}
	
	if (!function_exists('getCustomerID')) {
	function getCustomerID() {
		global $wpdb;
		global $user_id;
		global $userPhone;
		global $userEmail;
		global $userLoginID;
		
		$table_name = "pc2023_user_info";
		
		$userSQL = "SELECT user_id, user_login, user_email, stripe_custid, first_name, last_name FROM wp_users a, pc2023_user_info b WHERE user_id = %s and a.user_login = b.login_id";
		$userResult = $wpdb->get_row($wpdb->prepare($userSQL, $user_id));
		//$user_id = $userResult->user_id;
		$userLoginID = $userResult->user_login;
		$userPhone = $userLoginID;
		$userEmail = $userResult->user_email;
		$stripeCustID = $userResult->stripe_custid;
		$userFirstName = $userResult->first_name;
		$userLastName = $userResult->last_name;
		
		error_log("CREATING STRIPE USER DETAILS: LOGIN: ". $userLoginID . " USERID: ". $user_id . " email: " . $userEmail . " phone: " . $userPhone);
		
		if (empty($stripeCustID)) {
			
			$stripe = getStripeClient();
			$cust = $stripe->customers->create([
				'name' => $userFirstName. " ". $userLastName,
				'email' => $userEmail,
				'phone' => $userPhone,
				//'idempotency_key' => $idemKey,
			]);
			$stripeCustID = $cust['id'];
			error_log("Customer ID created: ". $stripeCustID . " Updating...");
			
			$userUpdateArr = [
				"stripe_custid" => $stripeCustID,			
				"updated_by" => "pc2023registration"
			];
			$where = ['user_id' => $user_id] ;
			$wpdb -> update($table_name, $userUpdateArr, $where);			
		}
	
	
		return $stripeCustID;
	}}
	
	
    function initCheckout($refid, $email) {
		global $wpdb;
		global $total_adultcnt;
		global $total_kidscnt;
	    global $total_donationcnt;
		global $user_id;
		global $membersacnt;
		global $memberFAMILYCOST;
		global $memberINDCOST;

		try {
			$userPaymentSQL = "SELECT a.payment_id, a.user_id, a.regprice_id, a.subtotal1, a.subtotal2, b.membership_type FROM pc2023_user_payment a LEFT JOIN pc2023_member_sa b on a.member_sa_id = b.member_sa_id where reference_id = %s";
			$userPaymentResult = $wpdb->get_row($wpdb->prepare($userPaymentSQL, $refid));
			$user_id = $userPaymentResult->user_id;
			$regpriceid = $userPaymentResult->regprice_id;
			$paymentid = $userPaymentResult->payment_id;
			$membershipType = $userPaymentResult->membership_type; 

			$subTotal1 = strval(((float) $userPaymentResult->subtotal1)*100);
			$subTotal2 = strval(((float) $userPaymentResult->subtotal2)*100);	
			error_log("<<<USER ID ".$user_id. "REG ID ". $regpriceid . " Payment ID ". $paymentid. " <<<" . "subTotal1- ". $subTotal1. " subtotal2-" . $subTotal2 . " ReferenceID: ". $refid . " MemershipType: ".$membershipType);
			
			$userRegPriceSQL = "SELECT spouse, kids_youthcnt, kids_adultcnt, guest_cnt, donation_total FROM pc2023_user_regprice WHERE regprice_id = %s";
			$userPaymentResult = $wpdb->get_row($wpdb->prepare($userRegPriceSQL, $regpriceid));
			$spouseCnt = (empty($userPaymentResult->spouse)) ? 0:1;
			$kids_youthcnt = (int)$userPaymentResult->kids_youthcnt;
			$kids_adultcnt = (int)$userPaymentResult->kids_adultcnt;
			$guest_cnt = (int)$userPaymentResult->guest_cnt;
			$total_adultcnt = $spouseCnt + $kids_adultcnt + $guest_cnt + 1;// 1 for the Register member
			$total_kidscnt =  $kids_youthcnt;

			// Remove any membership amount before calculating the actual donation
			$total_donationAmt = (((int)$subTotal2)/100);
			if (!empty($membershipType)) {
				if ($membershipType == 'FAMILY') {
					$membersacnt = 2;
					$total_donationAmt = $total_donationAmt - $memberFAMILYCOST;
				}else {
					$membersacnt = 1;
					$total_donationAmt = $total_donationAmt - $memberINDCOST;
				}
			}

			//$total_donationcnt = floor($total_donationAmt/25);
			$total_donationcnt = $total_donationAmt;//$1 donation - 4/22/2023
			//$total_donationcnt = floor(((int)$userPaymentResult->donation_total)/25);// Increments of $25 is collected
			
			error_log("<<<USER ID ".$user_id. " Adult cnt: " . $total_adultcnt . " Kids cnt: ". $total_kidscnt . " DonationCnt: ". $total_donationcnt . " SA-MemberCnt: " . $membersacnt);			
		}catch(Exception $e) {
			error_log( 'Exception retrieving data ', $e->getMessage());
			exit;
		}

		$checkoutId = "TBD";
		[$checkoutId, $redirectURL, $responseError] = checkoutStripe($refid);
		try {
			// Insert payment details
			updatePaymentDetails($refid, $checkoutId, $responseError);
		} catch (Exception $e) {
			error_log( 'Exception when calling CheckoutApi->createCheckout: '. $e->getMessage());
		}			

		
		// Check for response error and redirect accordingly
		if (empty($responseError)) {
			error_log( ">>>>>>>>>READY TO REDIRECT " . $redirectURL);
			redirect($redirectURL);
		}else {
			error_log( "ERROR WHILE PROCESSING THE PAYMENT<br/><br/>".$responseError);
		}
    }
	
	if (!function_exists('getStripeClient')) {
	function getStripeClient() {
		global $apikey;
		$stripe = new \Stripe\StripeClient($apikey);
		return $stripe;
	}}
	
	if (!function_exists('checkoutStripe')) {
	function checkoutStripe($refid) {
		global $total_adultcnt;
		global $total_kidscnt;
	    global $total_donationcnt;
		global $userPhone;
		global $userEmail;
		global $membersacnt;
		global $prd_mem_sa;
		global $prd_conv_donate;
		global $prd_conv_adult;
		global $prd_conv_kid;
		global $apikey;
		global $user_id;

		$checkout_session = null;
		$idemKey = uniqid();
		$checkoutId = null;
		$redirectURL = null;
		$responseError = null;

		try {
			$lineItemValArray = array();
			
			$adultArray = array('price' => $prd_conv_adult ,'quantity' => $total_adultcnt);
			$lineItemValArray[] = $adultArray;
			
			if ($total_kidscnt > 0) {
				//
				$kidArray = array('price' => $prd_conv_kid , 'quantity' => $total_kidscnt);
				$lineItemValArray[] =  $kidArray;
			}
			
			if ($total_donationcnt > 0) {
				$donArray = array('price' => $prd_conv_donate,'quantity' => $total_donationcnt);
				$lineItemValArray[] = $donArray;
			}
			
			if ($membersacnt > 0) {
				$memsaArray = array('price' => $prd_mem_sa, 'quantity' => $membersacnt );
				$lineItemValArray[] = $memsaArray;
			}

			$lineItemArray = array();
			$lineItemArray['line_items'] = $lineItemValArray;
			
			\Stripe\Stripe::setApiKey($apikey);
			$checkout_session = \Stripe\Checkout\Session::create([
				$lineItemArray,
				'mode' => 'payment',
				'success_url' => 'http://convention.sourashtraassociation.org/regconfirm',
				'cancel_url' => 'http://convention.sourashtraassociation.org/regland',
				'customer' => getCustomerID(),
				//'customer_email' => $userEmail,
				'metadata' => [ 'phone' => $userPhone],
				//'phone_number_collection' => ['enabled' => false],
				'payment_intent_data' => [
					'receipt_email' => $userEmail,
				],				
				//['idempotency_key' => $idemKey],
				//'automatic_tax' => ['enabled' => true],
			]);
			
			$checkoutId = $checkout_session['id'];
			$redirectURL =  $checkout_session->url;
			error_log("CHECKOUT SESSION => UserID: ". $user_id. " CheckoutID: " . $checkoutId);
		} catch (Exception $e) {
			error_log("CHECKOUT SESSION ERROR => UserID: ". $user_id . " ERROR MESSAGE => ". $e->getMessage());
			$responseError = $e->getMessage();
		}
		return	[$checkoutId, $redirectURL, $responseError];	
	}}
	

	
	if (!function_exists('updatePaymentDetails')) {
	function updatePaymentDetails($refid, $checkoutId, $responseError) {
		global $wpdb;
		$table_name = "pc2023_user_payment";
		error_log("UPDATING PAYMENT DETAILS FOR CHECKOUTID ". $checkoutId);
		$paymentArr = [
			"checkout_id" => $checkoutId,			
			"response_error" => $responseError,						
			"updated_by" => "pc2023registration"
		];
		
        $where = [
            'reference_id' => $refid
        ] ;
		$wpdb -> update($table_name, $paymentArr, $where);
	}}
	
    function redirect($url, $permanent = false) {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
        exit();
	}
	
    function redirectToConfirmPage() {
        header('Location: ' . '/regconfirm', true, 302);
        exit();
	}
	
	//header("HTTP/1.1 303 See Other");
	//header("Location: " . $checkout_session->url);

	initPage();
?>

<?php if ($destinationPage == 'PAYMENT_ERROR_PAGE') { ?>
    <!-- TOP NAVIGATION BAR: START -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Registration Payment Error</a>             
        </div>
    </nav>
    <!-- TOP NAVIGATION BAR: EMD -->
	
	<div id="card-personal" class="card mb-4">
        <div class="card-header">
            <p class="h4">Submission Error</p>
            <p class="h6 text-secondary small font-italic">Unable to process the payment. Please try later</p>
        </div>
    </div>
<?php } elseif ($destinationPage == 'NO_LOGIN_USER'){?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">USER NOT LOGGED IN</a>             
        </div>
    </nav>
    <!-- TOP NAVIGATION BAR: EMD -->
	
	<div id="card-personal" class="card mb-4">
        <div class="card-header">
            <p class="h4">USER LOGIN ERROR</p>
        </div>
    </div>
<?php } else {?>
    <!-- TOP NAVIGATION BAR: START -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ERROR PAGE</a>             
        </div>
    </nav>
    <!-- TOP NAVIGATION BAR: EMD -->
	
	<div id="card-personal" class="card mb-4">
        <div class="card-header">
            <p class="h4">Oops!!!</p>
            <p class="h6 text-secondary small font-italic">You have reached the page in error. Please click here for <a href="/regland">Registration Home Page</a></p>
        </div>
    </div>            
<?php }?><!-- REGISTRATION STRIPEPAYMENT PAGE v3.1-->