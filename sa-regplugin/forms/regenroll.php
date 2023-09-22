<?php
            echo '<br/>';
            global $uname;
            global $email;
            global $wpdb;
            global $destinationPage;
            global $userid;
            global $webRoot;
            global $price;
            global $ctx;
			
            if (!function_exists('initRegPage')) {
            function initRegPage() {
                global $uname;
                global $email;
                global $destinationPage;
                global $webRoot;
                global $ctx;

                $current_user = wp_get_current_user();
                $uname = $current_user->user_login;
                $email = $current_user->user_email;

                $webRoot =  parse_url(get_site_url(), PHP_URL_PATH);

                $userid = getUserProfile();
                $ctx = "REGISTRATION ENROLLEMENT PAGE USERID > ". $userid;
                error_log($ctx);

                $regid = getRegistration();
                if (empty($regid)) {
                    getRegPrice();
                    $destinationPage = "REG_ENROLL_PAGE";
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


            if (!function_exists('getRegistration')) {
            function getRegistration() {
                global $wpdb;
                global $userid;

                $sql = "select reg_id from sa_user_registration where user_id =%s";
                $result = $wpdb->get_var($wpdb->prepare($sql, $userid));
                return $result;
            }}

            if (!function_exists('getRegPrice')) {
            function getRegPrice() {
                global $wpdb;
                global $price;
                global $ctx;
                $curDate = date('Y-m-d');

                $sql = "select * from sa_regprice_model where valid_until > %s";

                $price = $wpdb->get_row($wpdb->prepare($sql, $curDate), ARRAY_A);
                error_log($ctx. $wpdb->last_query);
                if (empty($price)) {
                    $destinationPage = '';
                }

            }}

            if (!is_user_logged_in()) {
				$destinationPage = "SESSION_EXPIRED";
                //echo 'Please login';exit;
            }else {
                initRegPage();
            }	
               
    ?>

    <?php if ($destinationPage =='REG_ENROLL_PAGE') { ?>
        <script type="text/javascript">// <![CDATA[
        //window.location.replace("../regenroll");
        jQuery(function ($) {
            
            //Pricing Cost
            var adultPrice = <?=$price['adult_price']?>;
            var childPrice = <?=$price['child_price']?>;
            var childAgeLimit = <?=$price['child_agelimit']?>;
            var kidPrice = <?=$price['kid_18below_price']?>;
            var kidAgeLimit = <?=$price['kid_agelimit']?>;;
            var baseRegPrice = <?=$price['base_reg_price']?>;
            var discountrate = <?=$price['discount_rate']?>;
            var discountDesc = '<?=$price['discount_desc']?>';
            var validUntil = <?=$price['valid_until']?>;
            var kid18BelowPrice = <?=$price['kid_18below_price']?>;

            var pc2023Year= 2023;
            var sponsorItem = [
				 ["General Donation", "donation"]
                ,["Day1 Evening Snack", "sponsorFoodDay1EveSnack"]
                ,["Day1 Dinner", "sponsorFoodDay1Dinner"]
                ,["Day1 Dessert", "sponsorFoodDay1Dessert"]
                ,["Day2 Morning Snack", "sponsorFoodDay2MornSnack"]
                ,["Day2 Lunch", "sponsorFoodDay2Lunch"]
                ,["Day2 Dessert", "sponsorFoodDay2Dessert"]
                ,["Brochure Printing", "sponsorBrochurePrint"]
                ,["Souvenier", "sponsorSouvenier"]
                ,["Video & Photography", "sponsorVideoPhoto"] 
				,["Sourastra Assoc Membership", "membersa"]			
                ];

            $("#modal-regcost").html("<b>0</b>");

            // Prevent page refresh
            if($("refreshAllow").val() =="no") {
                //console.log("refresh on refresh: "+$("refreshAllow").val());
                location.reload();
            }

            
            // Additional Member - Add Member Button
            $("#add-more").click(function(e) {
                e.preventDefault();
                $("#add-more").prop('disabled','true');

                // retrieving the card index details
                var cardArr = getCardArr();
                var lastindex = cardArr[cardArr.length-1];
                if ( cardArr.length > 8) {
                    $("#addMoreError").text("You can only register upto 8 members.");
                    $("#addMoreError").show();
                    return;
                }else{
                    $("#addMoreError").hide();
                }
                for (i=0;i < cardArr.length; i++) {
                    // console.log("Disabling btn "+cardArr[i]);
                    $("#btnEdit"+cardArr[i]).hide();
                }
                var rowindex = lastindex +1;
                cardArr.push(rowindex);

                var tmplCard = $("#divCard0");
                var clnCard = tmplCard.clone(true);
                clnCard.attr("id", "divCard"+ rowindex);
                //console.log("Setting the new crd as "+rowindex);
                
                //resetting the Title
                var clnCard_btnTitle = clnCard.find("#cardTitle0");
                clnCard_btnTitle.attr("id", "cardTitle"+rowindex);
                clnCard_btnTitle.text('Family Member '+rowindex);
                //console.log("Setting the title1: "+clnCard_btnTitle.text());

                //resetting the body attributes
                var clnCard_body = clnCard.find("div.card-body");
                clnCard_body.attr("id", "divCardBody"+rowindex);
                
                // resetting the form attributes
                var gendername = clnCard.find("#gender0 option:checked");
                // console.log("Option value "+gendername.val("D"));
                gendername.attr("value", "");

                var birthyear = clnCard.find("#relationship0 option:checked");
                // console.log("Option value "+gendername.val("D"));
                birthyear.attr("value", ""); 

                ["fname", "lname", "relationship", "gender", "birthyear" ].forEach(function(elem, index) {
                    var formelement = clnCard.find("#"+elem+"0");
                    //console.log("ATTRIBUTE NAME for " +elem+" " + formelement.attr("name"));
                    formelement.attr("name", elem + rowindex);
                    formelement.attr("id", elem + rowindex);
                    formelement.attr("value", "");
                });                          

                    //resetting the id for Edit button
                var clnCard_btnedit = clnCard.find("button.edit"); 
                clnCard_btnedit.attr("id", "btnEdit"+ rowindex);
                clnCard_btnedit.attr("name", "btnEdit"+ rowindex);
                clnCard_btnedit.attr("data-target", "#divCardBody"+ rowindex);
                clnCard_btnedit.hide();

                //resetting the id for Save button
                var clnCard_btnsave = clnCard.find("button.save");
                clnCard_btnsave.attr("id", "btnSave"+ rowindex);
                clnCard_btnsave.attr("name", "btnSave"+ rowindex);

                //resetting the id for Delete button
                var clnCard_btndel = clnCard.find("button.del");
                clnCard_btndel.attr("id", "btnDel"+ rowindex);
                clnCard_btndel.attr("name", "btnDel"+ rowindex);

                //resetting the id for Error Message
                var clnCard_errorLabel = clnCard.find("div.error");
                clnCard_errorLabel.attr("id", "addMemError"+ rowindex);

                // setting the card index details
                $("#divCard"+lastindex).after(clnCard);

                //perform validation on keypress
                clnCard.find("#divCardBody"+rowindex+ " .form-control").each(function(index, formElement) {
                    $(this).on('keyup mouseup', function (event) {
                        if (!formElement.checkValidity()) {
                            formElement.classList.remove("is-valid");
                            formElement.classList.add("is-invalid");
                        }else {
                            formElement.classList.remove("is-invalid");
                            formElement.classList.add("is-valid");
                        }
                        formElement.classList.add("was-validated");
                    });
                });
                setCardArr(cardArr);

                $("#add-more").prop('disabled','true');
                $("#btn-preview").prop('disabled', 'true');
                $("#btn-pg2Prev").prop('disabled', 'true');
                $("#btn-pg2Next").prop('disabled', 'true');

                $("#fname"+rowindex).focus();
                // scroll to the newly crated card
                $("html, body").stop().animate({scrollTop: $("#divCard"+rowindex).offset().top}, 500);
            });
        
            $("select[id^='relationship']").on("change", function(e) {
                var str = $(this).attr("id");
                var index = str.substring(str.length - 1, str.length);
                if ($("#relationship"+index).val() == 'Spouse') {
                    if ($("#gender").val() == 'Male') {
                        $("#gender"+index).val('Female');
                    }else $("#gender"+index).val('Male');

                    $("#gender").prop("disabled", true);
                    $("#gender"+index).prop("disabled", true);
                }else {
                    $("#gender"+index).prop("disabled", false);
                }
		if ($("#relationship"+index).val() != 'Child') {
			$("#birthyear").attr("pattern", "(19[2-8][0-9]|199[0-9]|20[01][0-9]|202[0-3])");
		}else {
			$("#birthyear").attr("pattern", "(19[2-8][0-9]|199[0-9]|200[05])");
		}
            });

            // Additional Member - Save event - Triggered when Save button is clicked
            $("button[id^='btnSave']").on("click", function(e) {
                var str = $(this).attr("id");
                var index = str.substring(str.length - 1, str.length);
                //console.log("string str: "+str+" index: "+index);

                var newMemberFirstName;
                var newMemberLastName;
                var isFormValid = true;
                
                $("#addMemError"+index).removeClass("d-none");
                $("#divCardBody"+index+ " .form-control").each(function(inx, formElement) {
                    var tmp = $(this).val();
                    if (tmp) tmp = tmp.toLowerCase();
                    else tmp = "";
                    //console.log("<<<<<FORM ELEMENT>>>>"+tmp);
                    isFormValid = formElement.checkValidity(); 
                    if (!isFormValid) { // in case of invalid entries, highlight red in html elements
                        formElement.classList.remove("is-valid");
                        formElement.classList.add("is-invalid");
                    }else {
                        formElement.classList.remove("is-invalid");
                        formElement.classList.add("is-valid");
                    }
                    formElement.classList.add('was-validated');
                    if (!isFormValid) {
                        $("#addMemError"+index).text("Please fix the highlighted error. Incorrect value: "+tmp);                    
                        $("#addMemError"+index).removeClass("d-none");
                        return false;
                    }
                });

                if (!isFormValid) {
                    return; 
                }

                // check whether duplicate spouse check needs to be performed
                var checkForSpouse = false;
                if ($("#relationship"+index).val() == 'Spouse') {
                        checkForSpouse = true;
                }

                //check for duplicate spouse, firstname+lastname combo
                var firstLastArr = [];
                var duplicateSpouse = false;
                var duplicateFirstLastName =false;

                getCardArr().forEach(function(elem, ind) {
                    var tmp = $("#fname"+elem).val() + $("#lname"+elem).val();
                    if ($.inArray(tmp.toLowerCase(), firstLastArr) != -1) {
                    // if (firstLastArr.includes(tmp.toLowerCase())) {
                        duplicateFirstLastName = true;
                    }else firstLastArr.push(tmp);

                    if (checkForSpouse) {
                        if (elem == index) {
                            return;
                        }else {
                            if (($("#relationship"+elem).val() == 'Spouse')) {
                                duplicateSpouse = true;
                            }
                        }
                    }
                });

                if (duplicateSpouse) {
                    $("#addMemError"+index).text("You cannot register more than one Spouse! Please choose a different relationship.");                   
                    $("#addMemError"+index).removeClass("d-none");
                    return;
                }else {
                    $("#addMemError"+index).addClass("d-none");
                }


                if (duplicateFirstLastName) {
                    $("#fname"+index).removeClass('is-valid');
                    $("#fname"+index).addClass('is-invalid');
                    $("#lname"+index).removeClass('is-valid');
                    $("#lname"+index).addClass('is-invalid');
                    $("#addMemError"+index).text("First/Last Name already exists. Please re-enter");                    
                    $("#addMemError"+index).removeClass("d-none");
                    return;
                }else {
                    $("#fname"+index).addClass('is-valid');
                    $("#fname"+index).removeClass('is-invalid');
                    $("#lname"+index).addClass('is-valid');
                    $("#lname"+index).removeClass('is-invalid');                        
                    $("#addMemError"+index).addClass("d-none");
                }

                // Set the Title of Add Member Line item
                $("#cardTitle"+index).text($("#fname"+index).val() + " - " +  $("#lname"+index).val() + " - " + $("#relationship"+index + " option:selected").text() + " - " + $("#gender"+index + " option:selected").text() + " - " + $("#birthyear"+index).val());
                
                var cardArr = getCardArr();
                for (i=0;i < cardArr.length; i++) {
                    $("#btnEdit"+cardArr[i]).show();
                }
                $("#divCardBody"+index).toggle();//collapses the current card-body
                $("#add-more, #btn-preview, #btn-pg2Prev, #btn-pg2Next").removeAttr('disabled');// Remove the other disabled buttons
                if (checkForSpouse) {
                    $("#gender"+index).prop("disabled", false);
                }
                cobj = calculateCost();
                $("#modal-regcost").html("<b>"+ cobj.total +"</b>");
            });

            // Additional Member - Delete event - Triggered when Delete button is clicked
            $("button[id^='btnDel']").on("click", function(e) {
                var str = $(this).attr("id");
                var index = str.substring(str.length - 1, str.length);
                //console.log("string str: "+str+" index: "+index);

                if ($("#relationship"+index).val() == 'Spouse') {
                    $("#gender").prop("disabled", false);
                }
                
                $("#divCard"+index).remove();
                //Remove the card index from cardArr
                var cardArr = getCardArr();
                for( var i = 0; i < cardArr.length; i++) { 
                    if ( cardArr[i] === parseInt(index,10)) {
                        cardArr.splice(i, 1); 
                        i--;
                    }
                }
                setCardArr(cardArr);
                for (i=0;i < cardArr.length; i++) {
                    $("#btnEdit"+cardArr[i]).show();
                }
                $("#add-more, #btn-preview,  #btn-pg2Prev, #btn-pg2Next").removeAttr("disabled");
                $("#addMoreError").hide();            
                cobj = calculateCost();
                $("#modal-regcost").html("<b>"+ cobj.total +"</b>");                
            });        

            // Additional Member - Edit event - Triggered when Edit button is clicked
            $("button[id^='btnEdit']").on("click", function(e) {
                var str = $(this).attr("id");
                var index = str.substring(str.length - 1, str.length);
                //console.log("string str: "+str+" index: "+index);

                $("#divCardBody"+index).show();
                var cardArr = getCardArr();

                //console.log("ADDDDDDD Index "+index+ " Spouse PRESENT "+$("#gender"+index).val());
                if ($("#relationship"+index).val() == 'Spouse') {
                    $("#gender"+index).prop("disabled", true);
                }

                // while in edit mode, disable Add Member and Other Edit menus
                $("#add-more , #btn-preview,  #btn-pg2Prev, #btn-pg2Next").prop('disabled','true');
                for (i=0;i < cardArr.length; i++) {
                        $("#btnEdit"+cardArr[i]).hide();
                }    
            });

            // Personal Page Form validation - Validate when user enters the value
            $("#card-personal .form-control").each(function(index, formElement) {
                $(this).on('keyup mouseup', function (event) {
                    // $(this).parent().parent().find("p .help-block").removeAttr("hidden");
                    //console.log($(this).parent().next());
                    if (!formElement.checkValidity()) {
                        formElement.classList.remove("is-valid");
                        formElement.classList.add("is-invalid");
                    }else {
                        formElement.classList.remove("is-invalid");
                        formElement.classList.add("is-valid");
                    } 
                    formElement.classList.add("was-validated");
                });
            });

            // Full Form - Validation on Submission
            $("form").each(function(index, form) {
                // var $form = $(this);
                $(this).on("submit", function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).find('input:invalid, select:invalid').eq(0).focus();                                
                        $("html, body").stop().animate({scrollTop: $("#card-personal").offset().top}, 500);
                    }else {
                        $("refreshAllow").val('no');
                        //console.log("No Eror condition in form submission refresh: "+$("refreshAllow").val());
                    }
                    form.classList.add("was-validated");
                });
            });

            // Personal Page - NEXT button action
            $("#btn-pg1Next").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                var isValid = true;
                $("#modal-regcost").html("<b>"+ (baseRegPrice + adultPrice) +"</b>");

                $("#divCardBody"+" .form-control").each(function(index, formElement) {
                    if (!formElement.checkValidity()) {
                        formElement.classList.remove("is-valid");
                        formElement.classList.add("is-invalid");
                        isValid = false;
                    }else {
                        formElement.classList.remove("is-invalid");
                        formElement.classList.add("is-valid");
                    }
                    formElement.classList.add('was-validated');
                });
                
                if (isValid) { 
                    //console.log("FORM SUBMISSION IS VALID");           
                    $('#card-personal').addClass("d-none");
                    $('#card-member').removeClass("d-none");
                }
            });

            // Additional Page Previous button action
            $("#btn-pg2Prev").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $form = $("form")[0];
                if (!$form.checkValidity()) {
                    $('#validerror').addClass("show");
                    $('form').find('input:invalid, select:invalid').eq(0).focus();                                
                    $("html, body").stop().animate({scrollTop: $("#card-personal").offset().top}, 500);
                    //console.log("INVALID FORM SUBMISSION");
                }else {                        
                    $('#card-personal').removeClass("d-none");
                    $('#card-member').addClass("d-none");
                }
                $("#addMoreError").hide();
            });

            // Additional Page - NEXT button action
            $("#btn-pg2Next").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#card-member').addClass("d-none");
                $('#card-sa').removeClass("d-none");
                var cobj = calculateCost();
                $("#modal-regcost").html("<b>"+cobj.total+"</b>");
                $("#addMoreError").hide();
            });

            // SA Page Previous button action
            $("#btn-pg3Prev").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#card-member').removeClass("d-none");
                $('#card-sa').addClass("d-none");
            });

            // SA Page  - NEXT button action
            $("#btn-pg3Next").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                var isValid = true;
                $("#divCardSA"+" .form-control").each(function(index, formElement) {
                    if (!formElement.checkValidity()) {
                        formElement.classList.remove("is-valid");
                        formElement.classList.add("is-invalid");
                        isValid = false;
                    }else {
                        formElement.classList.remove("is-invalid");
                        formElement.classList.add("is-valid");
                    }
                    formElement.classList.add('was-validated');
                });
                
                if (isValid) {                
                    $('#card-sa').addClass("d-none");
                    $('#card-sponsor').removeClass("d-none");
                    //$(window).scrollTop();
                    $("html, body").stop().animate({scrollTop: 0}, "slow");// Works perfectly
                    $('#card-sponsor').focus();
                    var cobj = calculateCost();
                    $("#modal-regcost").html("<b>"+cobj.total+"</b>");   
                }
                
            });

            // Item Sponsor Page - Previous button action
            $("#btn-pg4Prev").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#card-sa').removeClass("d-none");
                $('#card-sponsor').addClass("d-none");
            });

            // Item Sponsor Page - Preview button action
            $("#btn-preview").on("click", 
            function(e) {
                $("#donationError").addClass("d-none");
                e.preventDefault();
                e.stopPropagation();

                var $form = $("form")[0];
                if (!$form.checkValidity()) {
                        $('#validerror').addClass("show");
                        $('form').find('input:invalid, select:invalid').eq(0).focus();                         
                        //$("html, body").stop().animate({scrollTop: $("#card-personal").offset().top}, 500);
                        //console.log("INVALID FORM SUBMISSION");
                        $form.classList.add("was-validated");
                }else {
                    $donAmt = $("#donation").val();
                    $donAmtInt = parseInt( $donAmt );
                    if ($donAmtInt < 0) {
                        $("#donation").val("");
                        $("#donationError").removeClass("d-none");
                        $("#donationError").text("Please enter amount equal or greater than zero.");
                        $("#donation").focus();
                        $("#donationError").focus();
                        //console.log("DONATION AMOUNT NOT VALID ");
                    }else {
                        //console.log("No Error condition in PREVIEW");
                        populatePersonalDetails();
                        
                        // Display the generated Map in the Summary Page
                        populateAddMemberSummary();

                        // Display contributions
                        populateContribSummary();

                        // Display registration pricing information
                        populateRegistrationCost();

                        $('#card-sponsor').addClass("d-none");

                        //hide the main card and show the summary card 
                        $('#summary-pg').removeClass("d-none");
                        $('#main-pg').addClass("d-none");
                    }
                }

                $("html, body").stop().animate({scrollTop: 0});
                $("#gender").prop("disabled", false);
            });

            // Summary Page - Populate Additional Member - Generate Member Array
            function generateMapFromCardArr() {
                map1 = [];
                getCardArr().forEach(function(item){
                    if (item!=0) {
                        a = {};
                        ['fname', 'lname', 'relationship', 'gender', 'birthyear' ].forEach(function(elem) {
                            a[elem] = $('#'+elem+item).val();
                        });
                        map1.push(a);
                    }   
                });
                //console.log("SETTING ADDL MEMBER ARRAY "+map1.toString());
                return map1;
            }

            function populatePersonalDetails() {
                    // Popoulate the Personal Details section
                    [["First Name","firstname"],["Last Name","lastname"],["Gher Nav","ghernav"]
                    ,["Gender","gender"],["Birth Year","birthyear"],["Mobile #","mobileno"]
                    ,["Country","country"],["State","state"]].forEach(setSummary);
            }

            // Summary Page - Populate Additional Member - Display Member Details - Main Function
            function populateAddMemberSummary() {
                // Generate a map from hidden card member array
                var map1 = generateMapFromCardArr();
                $("#addMemberArr").attr("value", JSON.stringify(map1));
                if (map1.length > 0) {
                        var $memCard = $("#summary-addmem");
                        $memCard.removeClass("d-none");
                        $memCard.find('.card-body').not(':first').remove();
                }else {
                    return;
                }            
                // console.log("LOGGING THE JSON OBJECT " + JSON.stringify(map1) );

                var $selCard = $("#summary-addmem #summary-addmem-card0");
                var rowindex=1;
                map1.forEach(function(obj) {
                    var $clnCard = $selCard.clone();
                    $clnCard.attr("id", "summary-addmem-card"+ rowindex++);
                    ["fname", "lname", "relationship", "gender", "birthyear"].forEach(function(elem) {
                        $clnCard.find("#s"+elem).html("<b>"+obj[elem]+"</b>");
                    });
                    $clnCard.appendTo($selCard.parent());
                    $clnCard.removeClass("d-none");
                });
            }
            
            // Summary Page - Populate Item Sponsorship Details - Main Function
            function populateContribSummary() {// Display contributions
                var arrContrib = [];
                sponsorItem.forEach(function(arr) {
                    var v = $('#'+arr[1]).val(); 
                    if ( v.trim() != "" && v!="0") {
                        var tmpArr = [];
                        tmpArr.push(arr[0]);
                        tmpArr.push(v);
                        // console.log("CONTRIBUTION: "+arr[0]+" VALUE: "+v);
                        arrContrib.push(tmpArr);
                    };
                });

                var $contribCard = $("#summary-contrib");
                if (arrContrib.length > 0) {
                        $contribCard.removeClass("d-none");
                        $contribCard.find('.card-body').not(':first').remove();
                }else {
                    $contribCard.addClass("d-none");
                    return;
                }
                
                var $selCard = $("#summary-contrib #summary-contrib-card0");
                $selCard.not(':first').remove();
                
                var rowindex=1;
                var $clncard;
                var colindex = 1;
                arrContrib.forEach(function(obj) {
                    if (colindex == 1) {
                        $clnCard = $selCard.clone();
                        $clnCard.attr("id", "summary-contrib-card"+ rowindex++);
                    }
                    $clnCard.find("#contribName"+colindex).html("<b>"+obj[0]+"</b>");
                    $clnCard.find("#contribValue"+colindex).html("<b>"+obj[1]+"</b>");
                    if (colindex == 2) {
                        $clnCard.find("#summary-contrib-cardcol2").removeClass("d-none");
                    }
                    if (colindex == 1) {
                        $clnCard.appendTo($selCard.parent());
                        $clnCard.removeClass("d-none");
                        colindex++;
                    }else colindex--;                
                });
            }

            // Summary Page - populate registration cost
            function populateRegistrationCost() {
                // Calculate the registration price
                var cobj = calculateCost();
                var arrPrice = [];
                $("#modal-regcost").html("<b>"+cobj.total+"</b>");

                [["Primary Member"                              , cobj.registrantPrice]
                ,["Spouse"                                  , cobj.spousePrice]
                ,["Kids - "+ cobj.kidCnt                    , (cobj.kids5BelowPrice + cobj.kids18BelowPrice + cobj.kids18AbovePrice)]
                ,["Guest (Adults) - "+ cobj.guestCnt                , cobj.guestPrice]
                ,["Sub Total (Registration)"                     , cobj.subTotal1Orig]
                ,["Sub Total (Registration w/ discounts) - Members "  , cobj.subTotal1]
                ].forEach(function(elem){
                    if (elem[0].startsWith('Kids')) {
                        if (cobj.kidCnt != 0) arrPrice.push(elem);
                        return;
                    }
                    if (elem[0].startsWith('Spouse')) {
                        if (cobj.spousePrice > 0) arrPrice.push(elem);
                        return;
                    }      
                    if (elem[0].startsWith('Guest')) {
                        if (cobj.guestCnt != 0) arrPrice.push(elem);
                        return;
                    }                                    
                    arrPrice.push(elem);
                });

                sponsorItem.forEach(function(elem){
                    var v = cobj[elem[1]]; 
                    if ( v > 0) arrPrice.push([elem[0], v]);
                });

                [["Sub Total (Others)", cobj.subTotal2]
                ,["Grand Total", cobj.total]
                ].forEach(function(elem){
                    if (elem[1] > 0) arrPrice.push(elem);
                });

                // Display logic
                var $priceCard = $("#summary-regcost");
                $priceCard.removeClass("d-none");
                $priceCard.find('.card-body').not(':first').remove();
                
                var $selCard = $("#summary-regcost #summary-regcost-card0");
                $selCard.not(':first').remove();
                var rowindex=1;
                
                //console.log("PRICING ARRAY "+arrPrice);
                arrPrice.forEach(function(obj) {
                    var $clnCard = $selCard.clone();
                    $clnCard.attr("id", "summary-regcost-card"+ rowindex++);
                    $clnCard.find("#priceName").html("<b>"+obj[0]+"</b>");
                    $clnCard.find("#priceValue").html("<b>"+obj[1]+"</b>");
                    
                    $clnCard.appendTo($selCard.parent());
                    $clnCard.removeClass("d-none");
                });            
            }

            // Summary Page - Populate Field Values - Main Function
            function setSummary(arr) {
                $('#s'+arr[1]).html('<b>'+$('#'+arr[1]).val()+'</b>');
            }
            
            // Utility function to get Additional Member Array
            function getCardArr() {
                var cardArrVal =  $("#cardArr").val();
                //console.log("cardArrVal: "+cardArrVal);

                var cardArr = [];
                if (cardArrVal == "") {
                    cardArr.push(0);
                }else {
                    cardArr = cardArrVal.split(",").map(function(item) {
                                return parseInt(item, 10);
                                });              
                }
                return cardArr;
            }

            // Utility function to set Additional Member Array
            function setCardArr(cardArr) {
                $("#cardArr").val(cardArr.toString());
            }
            
            // Summary Page - Edit Button
            $("#btn-edit").on("click", function(e) {
                $('#summary-pg').addClass("d-none");
                $('#main-pg').removeClass("d-none");
                $('#card-personal').removeClass("d-none");
                getCardArr().forEach(function(elem, ind) {
                    if ($("#gender"+elem).val()) {
                        $("#gender").prop("disabled","true");
                    }
                });
            });

            // Personal Page - Country State population
            $("#country").on("change", function(e) {
                console.log('STATE VALUE CHANGE CAPTURED::::');
                var states = [];
                console.log('STATE VALUE::::'+"#state_"+$(this).val());
                $("#state_"+$(this).val());
                
                switch($(this).val()) {
                    case 'US':
                    vals = [{"name":"Choose...","abbreviation":""},{"name":"Alabama","abbreviation":"AL"},{"name":"Alaska","abbreviation":"AK"},{"name":"AmericanSamoa","abbreviation":"AS"},{"name":"Arizona","abbreviation":"AZ"},{"name":"Arkansas","abbreviation":"AR"},{"name":"California","abbreviation":"CA"},{"name":"Colorado","abbreviation":"CO"},{"name":"Connecticut","abbreviation":"CT"},{"name":"Delaware","abbreviation":"DE"},{"name":"DistrictOfColumbia","abbreviation":"DC"},{"name":"FederatedStatesOfMicronesia","abbreviation":"FM"},{"name":"Florida","abbreviation":"FL"},{"name":"Georgia","abbreviation":"GA"},{"name":"Guam","abbreviation":"GU"},{"name":"Hawaii","abbreviation":"HI"},{"name":"Idaho","abbreviation":"ID"},{"name":"Illinois","abbreviation":"IL"},{"name":"Indiana","abbreviation":"IN"},{"name":"Iowa","abbreviation":"IA"},{"name":"Kansas","abbreviation":"KS"},{"name":"Kentucky","abbreviation":"KY"},{"name":"Louisiana","abbreviation":"LA"},{"name":"Maine","abbreviation":"ME"},{"name":"MarshallIslands","abbreviation":"MH"},{"name":"Maryland","abbreviation":"MD"},{"name":"Massachusetts","abbreviation":"MA"},{"name":"Michigan","abbreviation":"MI"},{"name":"Minnesota","abbreviation":"MN"},{"name":"Mississippi","abbreviation":"MS"},{"name":"Missouri","abbreviation":"MO"},{"name":"Montana","abbreviation":"MT"},{"name":"Nebraska","abbreviation":"NE"},{"name":"Nevada","abbreviation":"NV"},{"name":"NewHampshire","abbreviation":"NH"},{"name":"NewJersey","abbreviation":"NJ"},{"name":"NewMexico","abbreviation":"NM"},{"name":"NewYork","abbreviation":"NY"},{"name":"NorthCarolina","abbreviation":"NC"},{"name":"NorthDakota","abbreviation":"ND"},{"name":"NorthernMarianaIslands","abbreviation":"MP"},{"name":"Ohio","abbreviation":"OH"},{"name":"Oklahoma","abbreviation":"OK"},{"name":"Oregon","abbreviation":"OR"},{"name":"Palau","abbreviation":"PW"},{"name":"Pennsylvania","abbreviation":"PA"},{"name":"PuertoRico","abbreviation":"PR"},{"name":"RhodeIsland","abbreviation":"RI"},{"name":"SouthCarolina","abbreviation":"SC"},{"name":"SouthDakota","abbreviation":"SD"},{"name":"Tennessee","abbreviation":"TN"},{"name":"Texas","abbreviation":"TX"},{"name":"Utah","abbreviation":"UT"},{"name":"Vermont","abbreviation":"VT"},{"name":"VirginIslands","abbreviation":"VI"},{"name":"Virginia","abbreviation":"VA"},{"name":"Washington","abbreviation":"WA"},{"name":"WestVirginia","abbreviation":"WV"},{"name":"Wisconsin","abbreviation":"WI"},{"name":"Wyoming","abbreviation":"WY"}];
                    break;
                    case 'CA':
                    vals = [{"name":"Choose...","abbreviation":""},{"name":"Alberta","abbreviation":"AB"},{"name":"British Columbia","abbreviation":"BC"},{"name":"Manitoba","abbreviation":"MB"},{"name":"New Brunswick","abbreviation":"NB"},{"name":"Newfoundland and Labrador","abbreviation":"NL"},{"name":"Nova Scotia","abbreviation":"NS"},{"name":"Northwest Territories","abbreviation":"NT"},{"name":"Nunavut","abbreviation":"NU"},{"name":"Ontario","abbreviation":"ON"},{"name":"Prince Edward Island","abbreviation":"PE"},{"name":"Quebec","abbreviation":"QC"},{"name":"Saskatchewan","abbreviation":"SK"},{"name":"Yukon","abbreviation":"YT"}];
                    break;
                    case '':
                    vals = [{"name":"Choose...","abbreviation":""}]
                }
                var $st = $("#state");
                $st.empty();
                $.each(vals, function(index, obj) {
                    $st.append("<option value='"+obj.abbreviation+"'>" + obj.name + "</option>");
                });
            });

            // Pricing calculations
            function calculateCost() {
                calcObj = {};

                calcObj.isSpouseAdded = false;
                calcObj.isKidsAdded = false;
                calcObj.isGuestAdded = false;
                calcObj.donationAmt= 0;

                calcObj.subTotal1 = 0;
                calcObj.subTotal2 = 0;

                calcObj.spouseCnt = 0;
                calcObj.kidCnt = 0;
                calcObj.guestCnt=  0;
                calcObj.adultKidCnt = 0;
                calcObj.childCnt = 0;
                var tmp = '5';

                getCardArr().forEach(function(curVal) {
                    if (curVal !=0) {
                        var memberType = $("#relationship"+curVal).val();
                        var memberAge =  $("#birthyear"+curVal).val();
                        
                        var isMemberAdult = ((pc2023Year - Number(memberAge)) >= 12);
                        var isMemberChild = !((pc2023Year - Number(memberAge)) >= 5);
                       
                        switch(memberType) {
                                case 'Spouse': calcObj.isSpouseAdded = true; calcObj.spouseCnt++;break;
                                case 'Child': calcObj.isKidsAdded = true; calcObj.kidCnt++; break;
                                case 'Others': calcObj.isGuestAdded = true; calcObj.guestCnt++;break;
                                default: 
                        };
                        // Kids above 12+ should be treated as Adult
                        if (isMemberAdult && (memberType == "Child")) {
                            calcObj.adultKidCnt++;
                        }
                        // Kids above 18+ should be treated as Adult
                        if (isMemberChild && (memberType == "Child")) {
                            calcObj.childCnt++;
                        }
                    }
                });
                

                calcObj.baseRegPrice = baseRegPrice; // Convention base price
                calcObj.registrantPrice = adultPrice; // Person registering
                calcObj.spousePrice = adultPrice * calcObj.spouseCnt; //spouse price

                calcObj.kidsYouthCnt = calcObj.kidCnt - (calcObj.adultKidCnt + calcObj.childCnt)
                calcObj.kids5BelowPrice  = childPrice * calcObj.childCnt; // kid - Added as kid but calculated as child
                calcObj.kids18AbovePrice = adultPrice * calcObj.adultKidCnt; // kid - Added as kid but calculated as Adult
                calcObj.kids18BelowPrice = kidPrice   * calcObj.kidsYouthCnt ; // kid - Added as kid but calculated as Kid

                calcObj.kidsPrice = calcObj.kids5BelowPrice + calcObj.kids18BelowPrice + calcObj.kids18AbovePrice;
                calcObj.guestPrice = adultPrice * calcObj.guestCnt; // guest price

                calcObj.subTotal1Orig = calcObj.baseRegPrice + calcObj.registrantPrice + calcObj.spousePrice + calcObj.kidsPrice + calcObj.guestPrice;
                calcObj.subTotal1 = calcObj.subTotal1Orig * discountrate;
                //console.log("SUBTOTAL1>>>>"+ calcObj.subTotal1 + " spousecnt "+calcObj.spouseCnt + " kidcnt "+calcObj.kidCnt + "  AdultKidCnt "+calcObj.adultKidCnt+ " GuesCnt "+calcObj.guestCnt);
                
                sponsorItem.forEach(function(elem) {
                    var tmp = $("#"+elem[1]).val();
                    if (tmp && tmp != "") {
                        var v = Number(tmp.trim());
                        if (v > 0) {
                            calcObj[elem[1]] =  v;
                            calcObj.subTotal2 = calcObj.subTotal2 + v;
                        }else {
                            calcObj[elem[1]] =  0;
                        }
                    }
                });
                //console.log("SUBTOTAL2>>>>"+ calcObj.subTotal2);

                calcObj.total = calcObj.subTotal1 + calcObj.subTotal2;
                //console.log ("TOTAL "+calcObj.total);

                $("#regprice").val(JSON.stringify(calcObj));
                return calcObj;
            }

            // Registration Price Breakdown - Popup Page
            $("#costModalLink").on("click", function(e) {
                var cObj = calculateCost();
                var memberRates = new Map([['Spouse','(Spouse)'],['Child','(Kids)'],['Others','(Guest)']]);
                var $addMemberList = $("#costModalSummary ul:eq(1) li");
                var $donationList = $("#costModalSummary ul:eq(4)");
                
                if ($addMemberList.length > 0) {
                    // console.log("Cost Modal BEFORE memberlength: "+$addMemberList.length);
                    // $("#costModalSummary ul:eq(1) li:not(:first)").remove();
                    $addMemberList.not(':first').remove();
                    // console.log("Cost Modal AFTER memberlength: "+$addMemberList.length);
                    
                }
                $donationList.empty();

                if (cObj.isSpouseAdded) {additionMemberUpdate("Spouse" ,cObj.spouseCnt);}
                if (cObj.isKidsAdded)   {additionMemberUpdate("Child"  ,cObj.kidCnt);}
                if (cObj.isGuestAdded)  {additionMemberUpdate("Others" ,cObj.guestCnt);}

                $("#costModalSummary ul:eq(2) li").html("Sub Total (Registration) <span>$"+ cObj.subTotal1Orig + "</span>");
                //$("#costModalSummary ul:eq(4) li").html("Sub Total (after discount) <span>$"+ cObj.subTotal1 + "</span>");

                var spArr = [];
                var isDonationExists = false;
                sponsorItem.forEach(function(elem){
                    var v = cObj[elem[1]];
                    if (v > 0) {
                        setDonAmt(elem[0], v);
                        isDonationExists = true;
                    }
                });

                if (!isDonationExists) {
                    setDonAmt("Donation/Sponsorship/Membership", 0);
                }
                
                $("#costModalSummary ul:eq(5) li").html("Sub Total (Others) <span>$" + cObj.subTotal2 + "</span>");
                $("#costModalSummary ul:eq(6) li").html("Grand Total <span>$" + cObj.total + "</span>");

                function setDonAmt(dName, amt) {
                    //console.log("DONATION AMT "+ amt + " for "+dName+ " HTML "+$addMemberList.last().html());
                    var $newItem = $addMemberList.last().clone();
                    $newItem.html(dName+" <span class=''>"+ amt +"</span>");
                    $donationList.prepend($newItem);
                    return parseInt(amt);
                }

                function additionMemberUpdate(lookupvar, memberCnt) {
                    //console.log("COST MODAL: Adding "+lookupvar+ " Count: "+memberCnt+ " Inner: "+ $addMemberList.last().html());
                    var $costSummaryItem = $addMemberList.last();
                    var $newCostSummaryItem = $costSummaryItem.clone();

                    $newCostSummaryItem.html("Additional Member "+memberRates.get(lookupvar)+" <span class='badge badge-primary badge-pill'>"+memberCnt+"</span>");
                    $newCostSummaryItem.insertAfter($addMemberList);
                }            
            });
        });
        // ]]></script>
        
        <style>
        ::placeholder {
            opacity: 3;
            font-style: italic;
            font-size: 12px;
            color:red
        }

        .alignleft {
            float: left;
        }

        div#divCard0 {
            display: none;

        }

        button.edit {
            float: right;
        }

        .form-group.required .control-label:after {
            content:" * ";
            color:red;
        }    
        .modal-dialog {
            padding-top: 15%;
        }        
        </style>

        <!-- TOP NAVIGATION BAR: START -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Registration Page</a>             
                <ul class="navbar-nav nav-item active">
                    <button type="button" id="costModalLink" class="nav-link btn btn-primary" data-toggle="modal" data-target="#costModal">
                        <span class="oi oi-cart"></span>
                        <label id="modal-regcost" class="oi oi-dollar px-3">0.0</label>
                    </button>         
                </ul>
            </div>
        </nav>
        <!-- TOP NAVIGATION BAR: EMD -->

        <!-- TOP NAVIGATION SERVER ERROR MESSAGE: START -->
        <div id="validerror" class="alert alert-primary alert-dismissible" role="alert">
            <strong>Please fill in the required fields below. Information is not saved until its submitted. </strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>   
        <!-- TOP NAVIGATION SERVER ERROR MESSAGE: EMD -->

        <form id="regform" action="<?=$webRoot?>/regsubmit" method="post" class="container needs-validation" novalidate>

        <!-- MAIN PAGE: START  -->
        <div id="main-pg">

        <div id="card-personal" class="card mb-4">
            <div class="card-header">
                <p class="h4">Personal Details</p>
                <p class="h6 text-secondary small font-italic">Tell us about yourself</p>
            </div>

            <div class="card-body" id="divCardBody">
            <div class="form-row mb-3">       
                <div class="form-group col-md-6 pr-4 pb-1 required">                  
                    <label class="control-label" for="firstname">First Name</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-person"></span></div>                    
                        <input type="text" class="form-control" id="firstname" name="firstname" maxlength="20"  pattern="[a-zA-Z ]*" placeholder="enter first name" value="" required>
                    </div>
                    <!--p class="help-block text-secondary small italic">First Name</p-->
                </div>
                <div class="form-group col-md-6 pr-4 required">
                    <label  class="control-label" for="lastname">Last Name</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-person"></span></div>                    
                        <input type="text" class="form-control" id="lastname" name="lastname" maxlength="30" pattern="[a-zA-Z ]*" placeholder="enter last name" value="" required>
                    </div>
                </div>
            </div>
            <div class="form-row mb-3">
                <div class="form-group col-md-5 pb-1 pr-3 required">
                    <label  class="control-label" for="ghernav">Gher Nav</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-home"></span></div>                    
                        <input type="text" class="form-control" id="ghernav" name="ghernav" maxlength="20" pattern="[a-zA-Z ]*" placeholder="enter gher nav" value="" required>
                    </div>
                </div>
                <div class="form-group col-md-4 pb-3 pr-4 required">
                    <label class="control-label" for="gender">Gender</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-info"></span></div>                    
                        <select id="gender" name ="gender" class="form-control" required>
                            <option value="" selected>Choose...</option>
                            <option value="Male" >Male</option>
                            <option value="Female" >Female</option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-3 required">
                    <label  class="control-label" for="birthyear">Birth Year (18+)</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-calendar"></span></div>                    
                        <input type="text" maxlength="4" size="4" pattern="(19[2-8][0-9]|199[0-9]|200[0-5])" class="form-control" id="birthyear" name="birthyear" placeholder="Ex: 1980" value="" required>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4 pb-3 pr-3 required">
                    <label  class="control-label" for="mobileno">Mobile#</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-phone"></span></div>                    
                        <input type="tel" class="form-control" id="mobileno" name="mobileno" maxlength="10" pattern="[0-9]{10}" size="10" placeholder="enter mobile#" value="" required>
                    </div>
                </div>
                <div class="form-group col-md-3 pr-4 pb-3 required">
                    <label  class="control-label" for="country">Country </label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-location"></span></div>                    
                        <select id="country" name="country" class="form-control" required>
                            <option value="" selected>Choose..</option>
                            <option value="US">USA</option>
                            <option value="CA">CANADA</option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-5 required">
                    <label class="control-label" for="state">State / Province</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text"><span class="oi oi-location"></span></div>                    
                        <select id="state" name="state" class="form-control" required>
                            <option value="" selected>Choose...</option>
                        </select>
                    </div>
                </div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-5 required">
					<label class="control-label" for="hearabout0">How did you hear about us?</label>
					<div class="input-group">
						<select id="hearabout" name="hearabout" class="form-control" required>
							<option selected value="NONE">...</option>
							<option value="SA">Existing Sourashtra Member</option>
							<option value="Email">Email</option>
							<option value="Email">WhatsApp</option>
							<option value="Email">Facebook</option>
							<option value="Email">Friends & Relatives</option>
							<option value="Email">Poltam</option>
							<option value="Email">Association Events</option>
						</select> 							
					</div>
				</div>					
            </div>
			<div class="form-row">
				<div class="form-group col-md-10 form-switch">
                    <input class="form-check-input" type="checkbox" name="datareuse" id="datareuse" checked>
                    <label class="form-check-label" for="flexSwitchCheckChecked">I Consent to Sourashtra Association request to reuse this information (optional)</label>
				</div>					
            </div>            
        </div>
        <div class="d-flex justify-content-end mb-4 mr-4">
                <button id="btn-pg1Next" name="btn-pg1Next" class="btn btn-primary" >Next</button>
        </div>
        </div>

        <div id="card-member" class="card mb-5 d-none">
            <div class="card-header">
                <p class="h4">Family Members Details (Other than yourself)</p>
                <p class="h6 text-secondary small font-italic">Tell us who is accompanying you</p>
            </div>

            <div id="memberCardBody" class="card-body">
            <p> Please add all members who'll be accompanying with you for the convention by clicking the button below. Multiple members can be added by clicking the same button. Total members are limited to 8.</p>

            <button type="button" class="btn btn-primary mt-3 mb-3" id="add-more" name="add-more"><i class="fa fa-plus"></i> Add Member</button><div id="addMoreError" class="alert alert-danger error ml-3" style="display:none" role="alert"></div>
                    
            <div class="card mb-1"  id="divCard0">
                <div class="card-header">
                        <label id="cardTitle0"> <h5>Family Member-0</h5></label>
                        <button type="button" id="btnEdit0" name="btnEdit0" class="edit btn btn-primary" title="Edit" data-toggle="show" data-target="#divCardBody0" aria-expanded="true" aria-controls="fm_b">Edit</button>                
                </div>
                <div id="divCardBody0" class="card-body collapse multi-collapse show">
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 pr-3 pb-3 required">
                            <label class="control-label" for="fname0">First Name </label>
                            <div class="input-group">
                                <div class="input-group-prepend input-group-text"><span class="oi oi-person"></span></div>                              
                                <input type="text" class="form-control" id="fname0" name="fname0" maxlength="30"  pattern="[a-zA-Z ]*" placeholder="enter first name" value="D" required/>     
                            </div>                            
                        </div>
                        <div class="form-group col-md-6 pr-3 pb-3 required">
                            <label class="control-label" for="lname0">Last Name </label>
                            <div class="input-group">
                                <div class="input-group-prepend input-group-text"><span class="oi oi-person"></span></div>                              
                                <input type="text" class="form-control" id="lname0" name="lname0" maxlength="30"  pattern="[a-zA-Z ]*" placeholder="enter last name" value="D" required/>     
                            </div>                            
                        </div>                 
                    </div>

                    <div class="form-row mb-3">                              
                        <div class="form-group col-md-6 pr-4 pb-3 required">
                            <label class="control-label" for="relationship0">Relationship</label>
                            <div class="input-group">
                                <div class="input-group-prepend input-group-text"><span class="oi oi-link-intact"></span></div>
                                <select id="relationship0" name="relationship0" class="form-control" required>
                                    <option selected value="D">Choose...</option>
                                    <option value="Spouse">Spouse</option>
                                    <option value="Child">Child</option>
                                    <option value="Others">Guest (Adults)</option>
                                </select>                            
                            </div>                                
                        </div>
                        <div class="form-group col-md-3 required">
                            <label class="control-label" for="gender0">Gender</label>
                            <div class="input-group">
                                <div class="input-group-prepend input-group-text"><span class="oi oi-info"></span></div>                              
                                <select id="gender0" name="gender0" class="form-control" required>
                                    <option selected value="D">Choose...</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>                                                          
                        </div>
                        <div class="form-group col-md-3 required">
                            <label class="control-label" for="birthyear0">Birth Year</label>
                            <div class="input-group">
                                <div class="input-group-prepend input-group-text"><span class="oi oi-calendar"></span></div>                              
                                <input type="text" maxlength="4" size="4" pattern="(19[2-8][0-9]|199[0-9]|20[01][0-9]|202[0-3])" class="form-control" id="birthyear0" name="birthyear0" value="2000" placeholder="Ex: 1981" required>
                            </div>
                        </div>
                    </div>
					
                    <div class="form-row">
                        <button type="button" id="btnSave0" name="btnSave0" class="save btn btn-primary col-md-1 mb-3 mr-3" >Save</button>
                        <button type="button" id="btnDel0" name="btnDel0" class="del btn btn-primary col-md-1 mb-3" >Del</button>
                        <div id="addMemError0" class="alert alert-danger error ml-3 d-none" role="alert"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center mb-4">
                <button id="btn-pg2Prev" name="btn-pg2Prev" class="btn btn-primary mr-3" >Back</button>
                <button id="btn-pg2Next" name="btn-pg2Next" class="btn btn-primary mb-10" >Next</button>
        </div>
        </div>

        <div id="card-sa" class="card mb-3 d-none">
            <div class="card-header">
                <p class="h4">Sourastra Association Membership Details<span class="small text-secondary">&nbsp;&nbsp;(optional)</span></p>
                <p class="h6 text-secondary small font-italic">Sourashtra Association is the brainchild of all the past conventions.</p>
            </div>        
            <div id="divCardSA" class="card-body">
                <b>Our Mission</b>
                <p>Sourashtra Association is a charitable, not-for-profit 501(c)(3) corporation, formed to serve the needs of the Sourashtra community in the United States and Canada.Our Mission is to Unite all Sourashtra families in the United States and Canada under one organization, instilling a sense of community and togetherness and preserve Sourashtra identity, language, culture and heritage for the current and future generations by actively interacting and engaging through various events, activities, and community projects and by creating common interest groups within the community.</p>
                <p>Please consider joining/renewing your Sourashtra Association membership and support our mission!</p>
                <b>Family Membership - $30/year (Recommended)</b>
                <p>Family membership will include you, your spouse and/or any children under the age of 18 from now until the end of the current year. A separate individual or family membership will be required for children over the age 18.<br/>
                    <ul class="list-group">
                        <li class="list-group-item list-group-item-info">Avail member only benefits like Sourashtra language classes for kids and adults, Youth Affairs for kids, Youth volunteer credits etc</li>
                        <li class="list-group-item list-group-item-info">2 adults from your family will be eligible to vote for the Association election</li>
                        <li class="list-group-item list-group-item-info">You may run for the association office</li>
                    </ul>
                </p>
                <b>Individual Membership - $15/year</b>
                <p>Individual membership includes you from now until the end of the current year. Your spouse and children will not be included. All member only benefits will apply only to you.</p>
                    <ul class="list-group"> 
                        <li class="list-group-item list-group-item-info">Avail member only benefits like Sourashtra language classes for adults</li>
                        <li class="list-group-item list-group-item-info">You will be eligible to vote for the Association election</li>
                        <li class="list-group-item list-group-item-info">You may run for the association office</li>
                    </ul>
                </p>
                <div class="row form-group align-items-center mt-5 required">
                    <div class="col-md-3">
                        <label class="label-primary" for="member">Membership Type:</label>
                    </div>
                    <div class="input-group col-md-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <select id="membersa" name="membersa" class="form-control" required>
                            <option selected value="0">...</option>
                            <option value="30">Family Membership</option>
                            <option value="15">Individual Membership</option>
                        </select> 
                    </div>
                </div>       
            </div>
            <div class="d-flex justify-content-center mt-5 mb-4">
                    <button id="btn-pg3Prev" name="btn-pg3Prev" class="btn btn-primary mr-3" >Back</button>
                    <button id="btn-pg3Next" name="btn-pg3Next" class="btn btn-primary" >Next</button>
            </div>      
        </div>

        <div id="card-sponsor" class="card mb-3 d-none">
            <div class="card-header">
                <p class="h4">Donation <span class="small text-secondary">(optional)</span></p>
                <p class="h6 text-secondary small font-italic">Tell us what you can contribute</p>
            </div>        
            <div id="divCardDonation" class="card-body">
            <p>Please consider donating generously.  Every contribution you make will help us provide you better experience and services while you are here at the convention. Sourashtra Association Inc is a registered charitable, not-for-profit 501(c)(3) corporation and donations are eligible for tax deductions.</p>
            <div class="row form-group align-items-center mt-5 required">
                <div class="col-md-3">
                    <label class="label-primary" for="donation">Donation Amount</label>
                </div>
                <div class="input-group col-md-4">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    
                    <input id="donation" name="donation" type="text" maxlength="5" size="5" pattern="[0-9]{0,5}" class="form-control"  value="0" placeholder="enter amount value" aria-label="Amount ($25 increments)" required>
                    
                    <div class="input-group-append">
                        <span class="input-group-text">.00</span>
                    </div>
                </div>
            </div>
            <div id="donationError" class="alert alert-danger error ml-3 d-none" role="alert">NO ERROR</div>       
            </div>		
            <div class="card-header">
                <p class="h4">Itemwise Sponsorship<span class="small text-secondary">(optional)</span></p>
                <p class="h6 text-secondary small font-italic">Tell us how you would like help us</p>
            </div>
            <div class="card-body">
                <div class="mb-3"><p> Please consider sponsoring for one or more of the items listed below. We appreciate every form of your sponsorship.</p></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorFoodDay1EveSnack">Day1 Evening Snack Sponsorship</label>
                        </div>
                        <select id="sponsorFoodDay1EveSnack" name="sponsorFoodDay1EveSnack" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorFoodDay2MornSnack">Day2 Morning Snack Sponsorship</label>
                        </div>
                        <select id="sponsorFoodDay2MornSnack" name="sponsorFoodDay2MornSnack" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>                
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorFoodDay1Dinner">Day1 Dinner Sponsorship</label>
                        </div>
                        <select id="sponsorFoodDay1Dinner" name="sponsorFoodDay1Dinner" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorFoodDay2Lunch">Day2 Lunch Sponsorship</label>
                        </div>
                        <select id="sponsorFoodDay2Lunch" name="sponsorFoodDay2Lunch" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>                
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorFoodDay1Dessert">Day1 Dessert Sponsorship</label>
                        </div>
                        <select id="sponsorFoodDay1Dessert" name="sponsorFoodDay1Dessert" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorFoodDay2Dessert">Day2 Dessert Sponsorship</label>
                        </div>
                        <select id="sponsorFoodDay2Dessert" name="sponsorFoodDay2Dessert" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>                
                </div> 
                <div class="row">
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorBrochurePrint">Brochure Printing Sponsorship</label>
                        </div>
                        <select id="sponsorBrochurePrint" name="sponsorBrochurePrint" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>
                    <div class="col-md-6 form-group">
                        <div>
                            <label class="label-primary" for="sponsorSouvenier">Souvenier Sponsorship</label>
                        </div>
                        <select id="sponsorSouvenier" name="sponsorSouvenier" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>                
                </div>
                <div class="row">
                    <div class="col form-group align-items-center">
                        <div>
                            <label class="label-primary" for="sponsorVideoPhoto">Video & Photography Sponsorship</label>
                        </div>
                        <select id="sponsorVideoPhoto" name="sponsorVideoPhoto" class="form-control" required>
                                <option selected value="0">...</option>
                                <option value="100">$100</option>
                                <option value="50">$50</option>
                                <option value="25">$25</option>
                        </select>    
                    </div>            
                </div>                                                                                                             
            </div>
            <div class="d-flex justify-content-center mb-5">
                <button id="btn-pg4Prev" name="btn-pg4Prev" class="btn btn-primary mr-3">Back</button>
                <button id="btn-preview" name="btn-preview" class="btn btn-primary">Preview</button>
            </div>
        </div>
            
        </div>
        <!-- MAIN PAGE: END -->

        <!-- SUMMARY PAGE: BEGIN -->
        <div id="summary-pg" class="d-none">
        <div id="summary-personal-pg"  class="card mb-0"><!-- Summary - Personal Details-->
            <h3 class="card-header">Personal Details</h3><div class="card-body" id="divCardBody">
            <div class="row mb-3">       
                <div class="col-md-5 pr-4 pb-1">                  
                    <label class="control-label pr-4" for="sfirstname">First Name</label>
                    <label id="sfirstname" ></label>
                        
                </div>
                <div class="col-md-5 pr-4 pb-1">                  
                    <label class="control-label pr-4" for="slastname">Last Name</label>
                    <label id="slastname" ></label>
                </div>
            </div>    
            <div class="row mb-3">
                <div class="col-md-5 pb-1 pr-3">
                    <label  class="control-label pr-4" for="sghernav">Gher Nav</label>
                    <label id="sghernav" ></label>
                </div>
                <div class="col-md-4 pb-3 pr-4">
                    <label class="control-label pr-4" for="sgender">Gender</label>
                    <label id="sgender" ></label>       
                </div>
                <div class="col-md-3">
                    <label class="control-label pr-4" for="sbirthyear">Birth Year</label>
                    <label id="sbirthyear" ></label>
                </div>        
            </div>
            <div class="row mb-3">
                <div class="col-md-5 pb-1 pr-3">
                    <label  class="control-label pr-4" for="smobileno">Mobile#</label>
                    <label id="smobileno" ></label>
                </div>
                <div class="col-md-4 pb-3 pr-4">
                    <label class="control-label pr-4" for="scountry">Country</label>
                    <label id="scountry"></label>
                </div>
                <div class="col-md-3">
                    <label class="control-label pr-4" for="sstate">State / Province</label>
                    <label id="sstate" ></label>
                </div>        
            </div>
            </div>
        </div>

        <div id="summary-addmem" class="card mb-0 d-none"><!-- Summary - Additional Member Details-->
            <h3 class="card-header">Family / Guest Members</h3>
            <div id="summary-addmem-card0" class="card-body d-none"><!-- Duplicate from here for every additional member-->
                <div class="row mb-3">       
                    <div class="col-md-5 pr-4 pb-1">                  
                        <label class="control-label pr-4" for="sfname">First Name</label>
                        <label id="sfname" ></label>
                            
                    </div>
                    <div class="col-md-5 pr-4 pb-1">                  
                        <label class="control-label pr-4" for="slname">Last Name</label>
                        <label id="slname" ></label>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5 pb-1 pr-3">
                        <label  class="control-label pr-4" for="srelationship">Relation</label>
                        <label id="srelationship" ></label>
                    </div>
                    <div class="col-md-4 pb-3 pr-4">
                        <label class="control-label pr-4" for="sgender">Gender</label>
                        <label id="sgender" ></label>       
                    </div>
                    <div class="col-md-3">
                        <label class="control-label pr-4" for="sbirthyear">Birth Year</label>
                        <label id="sbirthyear" ></label>
                    </div>        
                </div>
            </div>   
        </div>

        <div id="summary-contrib" class="card mb-0 d-none"><!-- Summary - Donation/Sponsorship-->
            <h3 class="card-header">Donations / Sponsorship / Membership (in US $)</h3>
            <div id="summary-contrib-card0" class="card-body d-none"><!-- Duplicate from here for every type of contribution-->
                <div class="row mb-6">
                    <div id="summary-contrib-cardcol1" class="col-md-6 pb-1">                  
                        <label id="contribName1" class="control-label pr-4">Donation</label>
                        <label id="contribValue1">$0</label>
                    </div>
                    <div id="summary-contrib-cardcol2" class="col-md-6 pb-1 d-none">                  
                        <label id="contribName2" class="control-label pr-4">Item Sponsorship</label>
                        <label id="contribValue2">$0</label>
                    </div>                
                </div>                     
            </div>        
        </div>

        <div id="summary-regcost" class="card mb-0 d-none"><!-- Summary - Registration Price-->
            <h3 class="card-header">Cost Summary(in US $)</h3>
            <div id="summary-regcost-card0" class="card-body d-none"><!-- Duplicate from here for registration-->
                <div class="row mb-6">
                    <div class="col pr-4 pb-1 input-group">                  
                        <label id="priceName" class="control-label pr-4 align-top">Item</label>
                        <span class="control-label oi oi-dollar input-group-prepend pr-2"></span>   
                        <label id="priceValue" class="control-label" >0.00</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-inline p-2">
            <button type="button" id="btn-edit" name="btn-edit" value="Edit" class="btn btn-primary btn-lg">Edit</button>
        </div>
        <div class="d-inline p-2">
            <input type="submit" name="SUBMIT_REGISTRATION" value="Submit" class="btn btn-primary btn-lg"/>                                    
        </div>
        </div>
        <!-- SUMMARY PAGE: EMD -->

        <!-- MODAL Registration cost breakdown: BEGIN -->
        <div class="modal fade" id="costModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"><div class="modal-dialog">
            <div class="modal-content" role="document">
                <div class="modal-header">
                    <h4 class="modal-title">Registration Cost Breakdown (in US Dollars)</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div id="costModalSummary" class="modal-body">
                    <ul class="list-group"><!--ul: 0-->
                            <li class="list-group-item  d-flex justify-content-between align-items-center list-group-item-primary">Person <span class="badge badge-primary">Count</span></li>   
                    </ul>

                    <ul class="list-group"><!--ul: 1-->                    
                        <li class="list-group-item  d-flex justify-content-between align-items-center">Primary Adult <span class="badge badge-primary badge-pill">1</span></li>
                    </ul>

                    <ul class="list-group"><!--ul: 2-->
                        <li class="list-group-item  d-flex justify-content-between align-items-center list-group-item-primary">Sub Total (Person) <span class="">$60</span></li>
                    </ul>

                    <ul class="pt-3 list-group"><!--ul: 3-->
                        <li class="list-group-item  d-flex justify-content-between align-items-center list-group-item-primary">Donation / Sponsorship / Membership</li>   
                    </ul>

                    <ul class="list-group"><!--ul: 4-->                              
                    </ul>

                    <ul class="pt-3 list-group"><!--ul: 5-->
                        <li class="list-group-item  d-flex justify-content-between align-items-center list-group-item-primary"></li>
                    </ul>
                    
                    <ul class="pt-3 list-group"><!--ul: 6-->
                        <li class="list-group-item  d-flex justify-content-between align-items-center list-group-item-primary"></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div></div>
        <!-- Registration cost breakdown: EMD -->

        <!-- HIDDEN FIELDS: BEGIN -->
        <input type="hidden" id="cardArr" name="cardArr" value="0"/>
        <input type="hidden" id="addMemberArr" name="addMemberArr" value="0"/>    
        <input type="hidden" id="regprice" name="regprice" value="0"/>
        <input type="hidden" id="email" name="<?=$email?>"/>
        <input type="hidden" id="refreshAllow" value="yes">
        <!-- HIDDEN FIELDS: END -->
        </form>

	<?php } elseif ($destinationPage =='SESSION_EXPIRED') { ?>
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">INVALID USER SESSION</a>             
                </div>
            </nav>
            <!-- TOP NAVIGATION BAR: EMD -->
            
            <div id="card-personal" class="card mb-4">
                <div class="card-header">
                    <p class="h4">Oops!!!</p>
                    <p class="h6 text-secondary small font-italic">Your session has either expired or you haven't signed In.</p>
                </div>
            </div>   
            <div class="d-inline p-2">
                <a type="button" id="btn-edit" name="btn-edit" value="Back" class="btn btn-primary btn-lg" href="<?=$webRoot?>/login">Home</a>
            </div>   	

    <?php } else {?>
            <!-- TOP NAVIGATION BAR: START -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Registration - USER ALREADY REGISTERED</a>             
                </div>
            </nav>
            <!-- TOP NAVIGATION BAR: EMD -->
            
            <div id="card-personal" class="card mb-4">
                <div class="card-header">
                    <p class="h4">Oops!!!</p>
                    <p class="h6 text-secondary small font-italic">You have reached the page in error.</p>
                </div>
            </div>   
            <div class="d-inline p-2">
                <a type="button" id="btn-edit" name="btn-edit" value="Back" class="btn btn-primary btn-lg" href="<?=$webRoot?>/regland">Home</a>
            </div>                     
    <?php }?><!-- REGISTRATION ENROLLMENT PAGE v4.6.6-->