<div id="formsuccess" style="background: green; color:#fff"/>
<div id="formerror" style="background:red; color:#fff"/>

<form id="regFormPage">
    <?php wp_nonce_field('wp_rest');?>
    <label>Name</label>
    <input type="text" name="name"> <br/>
    <button type="submit">Submit Form</button>
</form>
<script>
    jQuery(document).ready(function($) {
        $("#regFormPage").submit( function (e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                type: "POST",
                url: "<?php echo get_rest_url( null, "v1/regformapi/submit" );?>",
                data: form.serialize(),
                success: function() {
                    $("#formsuccess").html("Your message was sent").fadeIn();
                },
                error: function() {
                    $("#formerror").html("There was an error").fadeIn();
                }
            });
        })
    }); 
</script>