<?php
/**
 * Simple Form Template
 */
ob_start(); ?>

<div class="give-form-wrapper">
	<form action="{{form_action}}" name="{{form_name}}" method="{{form_method}}" {{form_attributes}}>
		{{form_fields}}
	</form>
</div>

<?php return ob_get_clean(); ?>
