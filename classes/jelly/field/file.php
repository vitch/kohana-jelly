<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Handles files and file uploads.
 *
 * If a valid upload is set on the field, the upload will be saved
 * automatically to the $path set and the value of the field will
 * be the filename used.
 *
 * @package  Jelly
 */
abstract class Jelly_Field_File extends Jelly_Field
{
	/**
	 * @var  boolean  Whether or not to delete the old file when a new file is added
	 */
	public $delete_old_file = TRUE;

	/**
	 * Ensures there is a path for saving set
	 *
	 * @param  array  $options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
		$this->path = $this->_check_path($this->path);
	}

	/**
	 * Uploads a file if we have a valid upload
	 *
	 * @param   Jelly  $model
	 * @param   mixed  $value
	 * @param   bool   $loaded
	 * @return  string|NULL
	 */
	public function save($model, $value, $loaded)
	{
		$original = $model->get($this->name, FALSE);

		// Upload a file?
		if (is_array($value) AND Upload::valid($value))
		{
			if (FALSE !== ($filename = Upload::save($value, NULL, $this->path)))
			{
				// Chop off the original path
				$value = str_replace($this->path, '', $filename);

				// Ensure we have no leading slash
				if (is_string($value))
				{
					$value = trim($value, '/');
				}

				 // Delete the old file if we need to
				if ($this->delete_old_file AND $original != $this->default)
				{
					$path = $this->path.$original;

					if (file_exists($path))
					{
						unlink($path);
					}
				}
			}
			else
			{
				$value = $this->default;
			}
		}

		return $value;
	}

	/**
	 * Deletes the actual uploaded file.
	 *
	 * @param   Jelly_Model  $model
	 * @return  void
	 */
	public function delete($model)
	{
		$file = $model->get($this->name, FALSE);
		
		if ($file != $this->default) {
			
			$path = $this->path.$file;
			if (file_exists($path))
			{
				unlink($path);
			}
		}
	}

	/**
	 * Checks that a given path exists and is writable and that it has a trailing slash.
	 *
	 * (pulled out into a method so that it can be reused easily by image subclass)
	 *
	 * @param  $path
	 * @return string The path - making sure it has a trailing slash
	 */
	protected function _check_path($path)
	{
		$path = realpath(str_replace('\\', '/', $path));
		// Ensure we have path to save to
		if (empty($path) OR !is_writable($path))
		{
			throw new Kohana_Exception(get_class($this).': must have a `path` property set that points to a writable directory');
		}

		// Make sure the path has a trailing slash
		return rtrim(str_replace('\\', '/', $path), '/').'/';
	}

	/**
	 * Function to be used as a callback to validate that the uplaoded file isn't
	 * bigger that upload_max_filesize (which fails silently otherwise).
	 *
	 * Add to the callbacks array like so:
	 *
	 *     'check_size'=>array('Field_File', '_check_filesize')
	 *
	 * @see $callbacks
	 * @param Validate $array
	 * @param  string $field
	 * @return void
	 */
	public static function _check_filesize(Validate $array, $field)
	{
		$file = $array[$field];
		if (isset($file['error']) && $file['error'] == UPLOAD_ERR_INI_SIZE) {
			$array->error($field, 'file_too_big', array('param1' => ini_get('upload_max_filesize')));
		}
	}
}
