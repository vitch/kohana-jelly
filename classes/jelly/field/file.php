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
	 * @var  boolean  Whether we should leave the old file name in the field when save() is called and no new file is uploaded
	 */
	public $retain_value_on_empty_save = FALSE;

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
		$old_filename = $this->default;
		if ($this->retain_value_on_empty_save AND $loaded) {
			$old_filename = Jelly::select($this->model, $model->id())->get($this->name);
		}

		// Upload a file?
		if (is_array($value) AND Upload::valid($value)) {
			if (FALSE !== ($filename = Upload::save($value, NULL, $this->path)))
			{
				// Chop off the original path
				$value = str_replace(realpath($this->path).DIRECTORY_SEPARATOR, '', $filename);

				// Ensure we have no leading slash
				if (is_string($value))
				{
					$value = trim($value, DIRECTORY_SEPARATOR);
				}

				// delete the old file
				if ($old_filename != $this->default && file_exists(realpath($this->path).DIRECTORY_SEPARATOR.$old_filename)) {
					unlink(realpath($this->path).DIRECTORY_SEPARATOR.$old_filename);
				}
			}
			else
			{
				$value = $old_filename;
			}
		}
		else
		{
			$value = $old_filename;
		}

		return $value;
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
		// Ensure we have path to save to
		if (empty($path) OR !is_writable($path))
		{
			throw new Kohana_Exception(get_class($this).': must have a `path` property set that points to a writable directory');
		}

		// Make sure the path has a trailing slash
		return rtrim(str_replace('\\', '/', $this->path), '/').'/';
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
		if ($file['error'] == UPLOAD_ERR_INI_SIZE) {
			$array->error($field, 'file_too_big', array('param1' => ini_get('upload_max_filesize')));
		}
	}
}
