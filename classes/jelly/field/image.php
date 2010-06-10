<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Handles image uploads and optionally creates thumbnails of different sizes from the uploaded image
 * (as specified by the $thumbnails array).
 *
 * Each thumbnail is specified as an array with the following properties: path, resize_type, dest_w, dest_h.
 *
 * For example:
 *
 *     "thumbnails" => array (
 *         // 1st thumbnail
 *         array(
 *             'path' => DOCROOT.'upload/images/my_thumbs/', // where to save the thumbnails
 *             'resize_type' => Jelly_Field_Image::RESIZE_TYPE_CROP, // crop the image
 *             'dest_w' => 100, // to 100px wide
 *             'dest_h' => 100, // and 100px high
 *         ),
 *         // 2nd thumbnail
 *         array(
 *             'path' => DOCROOT.'upload/images/medium/', // where to save the thumbnails
 *             'resize_type' => Jelly_Field_Image::RESIZE_TYPE_FIT, // fit the image within the specified dimensions
 *             'dest_w' => 250, // to 250px wide
 *             'dest_h' => NULL, // maintaining aspect ratio
 *         ),
 *     )
 *
 * @author   Kelvin Luck
 * @package  Jelly
 */
abstract class Jelly_Field_Image extends Field_File
{
	/**
	 * @const string Image will be cropped to the passed size (via Image->crop);
	 */
	const RESIZE_TYPE_CROP = 'RESIZE_TYPE_CROP';
	/**
	 * @const string Image will be resized to fit in the passed size (via Image->resize);
	 */
	const RESIZE_TYPE_FIT = 'RESIZE_TYPE_FIT';
	/**
	 * @const string Image will be resized and cropped to fit exactly in the passed size while retaining as much of the image as possible (via Image->resize_and_crop);
	 */
	const RESIZE_TYPE_EXACT_FIT = 'RESIZE_TYPE_EXACT_FIT';
	/**
	 * @const string Image will not be cropped or resized at all
	 */
	const RESIZE_TYPE_NONE = 'RESIZE_TYPE_NONE';

	/**
	 * @var  array  Specifications for all of the thumbnails that should be automatically generated when a new image is uploaded.
	 *  
	 */
	public $thumbnails = array();

	/**
	 * Ensures there we have validation rules restricting file types to valid image filetypes and
	 * that the paths for any thumbnails exist and are writable
	 *
	 * @param  array  $options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Check that all thumbnail directories are writable...
		foreach ($this->thumbnails as $thumbnail) {
			$thumbnail['path'] = $this->_check_path($thumbnail['path']);
		}

		if (!isset($this->rules['Upload::type'])) {
			$this->rules['Upload::type'] = array(array('jpg', 'gif', 'png', 'jpeg'));
		}
	}

	/**
	 * Logic to deal with uploading the image file and generating thumbnails according to
	 * what has been specified in the $thumbnails array.
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
				$file_has_changed = $old_filename != $this->default;
				if ($file_has_changed AND file_exists(realpath($this->path).DIRECTORY_SEPARATOR.$old_filename)) {
					unlink(realpath($this->path).DIRECTORY_SEPARATOR.$old_filename);
				}

				// generate any thumbnails
				$source_file = $this->path.DIRECTORY_SEPARATOR.$value;
				foreach ($this->thumbnails as $thumbnail) {
					$dest_path = realpath($thumbnail['path']).DIRECTORY_SEPARATOR;
					// Delete the old file
					if ($file_has_changed AND file_exists($dest_path.$old_filename)) {
						unlink($dest_path.$old_filename);
					}

					$image = Image::factory($source_file);
					$w = $thumbnail['dest_w'];
					$h = $thumbnail['dest_h'];
					switch ($thumbnail['resize_type']) {
						case Jelly_Field_Image::RESIZE_TYPE_CROP:
							$image->crop($w, $h);
							break;
						case Jelly_Field_Image::RESIZE_TYPE_FIT:
							$image->resize($w, $h);
							break;
						case Jelly_Field_Image::RESIZE_TYPE_EXACT_FIT:
							$image->resize($w, $h, Image::INVERSE)->crop($w, $h);
							break;
						case Jelly_Field_Image::RESIZE_TYPE_NONE:
							// Do nothing - copy of the image will be saved below
							break;
					}
					$image->save($dest_path.$value);
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
}
