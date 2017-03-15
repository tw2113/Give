<?php
/**
 * Stepper Form Template
 */
ob_start(); ?>

<div class="give-form-wrapper give-display-style-stepper">
	<div class="give-stepper-wrapper give-clearfix">
		<div class="give-form-progress-bar">
			<hr class="all_steps">
			<hr class="current_steps">
		</div>
	</div>
	<form action="{{form_action}}" name="{{form_name}}" method="{{form_method}}" {{form_attributes}}>
		{{form_fields}}
	</form>
</div>

<?php return ob_get_clean(); ?>
