<?php
/**
 * Stepper Form Template
 */
ob_start(); ?>

<div class="give-form-wrapper">
	<style>
		div.give-stepper-wrapper{
			margin-bottom: 20px;
			color: gray;
		}

		span.give-stepper-circle {
			color: white;
			background: gray;
			border-radius: 30px;
			padding: 5px 10px;
			font-size: 13px;
		}

		.give-stepper-active span.give-stepper-circle{
			background: #68bb6c;
		}

		div.give-stepper-wrapper.give-stepper-active{
			color: black;
		}

		div.give-stepper-col{
			display: inline-block;
			float: left;
			margin-left: 20px;
		}

		div.give-stepper-col:first-child{
			margin-left: 0;
		}
	</style>
	<div class="give-stepper-wrapper give-clearfix">
		<div class="give-stepper-row">
			<div class="give-stepper-col give-stepper-active">
				<span class="give-stepper-circle">1</span>
				<span class="give-stepper-label">Create</span>
			</div>
			<div class="give-stepper-col">
				<span class="give-stepper-circle">2</span>
				<span class="give-stepper-label">Experiment</span>
			</div>
			<div class="give-stepper-col">
				<span class="give-stepper-circle">3</span>
				<span class="give-stepper-label">Ideas</span>
			</div>
			<div class="give-stepper-col">
				<span class="give-stepper-circle">4</span>
				<span class="give-stepper-label">Launch</span>
			</div>
		</div>
	</div>
	<form action="{{form_action}}" name="{{form_name}}" method="{{form_method}}" {{form_attributes}}>
		{{form_fields}}
	</form>
</div>

<?php return ob_get_clean(); ?>
