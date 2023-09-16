<form id="regFormPage">
    <label>Name</label>
    <input type="text" name="name"> <br/>
    <button type="submit">Submit Form</button>
</form>
<script>
    jquery(d).ready(function($) {
        $("#regFormPage").submit(
            function (e) {
                 e.preventDefault();
                 alert('Form submited');
            }
        )
    }); 
</script>