<?php echo Form::select($name, array(
	$true	=> $label_true,
	$false	=> $label_false,
), $value == $label_true ? $true : $false, $attributes + array('id' => 'field-'.$name)); ?>