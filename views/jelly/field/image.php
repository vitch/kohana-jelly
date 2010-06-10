<?php
	echo Form::file($name, $attributes + array('id' => 'field-'.$name));

	// If there is an uploaded image then display it.
	// Default to the first thumbnail if thumbnails exist
	// (on the assumption this one is smaller and more suitable
	// for displaying here)
	if ($value != '' && !is_array($model->{$field->name})):
		if (count($field->thumbnails)) {
			$img_path = $field->thumbnails[0]['path'];
		} else {
			$img_path = $field->path;
		}
		echo Html::image(str_replace(DOCROOT.DIRECTORY_SEPARATOR, '', $img_path).$model->{$field->name});
	endif;
?>