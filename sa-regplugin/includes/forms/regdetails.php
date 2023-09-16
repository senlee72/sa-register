<form id="regFormPage">
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
                data: form.serialize()
            });
        })
    }); 
</script>