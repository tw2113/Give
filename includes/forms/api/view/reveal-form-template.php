<?php
/**
 * Reveal Form Template
 */
ob_start(); ?>

<div class="give-form-wrapper give-display-style-reveal">
	<form action="{{form_action}}" name="{{form_name}}" method="{{form_method}}" {{form_attributes}}>
		{{form_fields}}
	</form>
	{{continue_button}}
</div>

<?php return ob_get_clean(); ?>