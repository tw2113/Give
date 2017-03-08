<?php
/**
 * Simple Form Template
 */
ob_start(); ?>

<form action="{{form_action}}" name="{{form_name}}" method="{{form_method}}" {{form_attributes}}>
	{{form_fields}}
</form>

<?php return ob_get_clean(); ?>
