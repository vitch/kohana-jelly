<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Handles "slugs".
 *
 * Slugs are automatically converted.
 *
 * A valid slug consists of lowercase alphanumeric characters, plus
 * underscores, dashes, and forward slashes.
 *
 * @package    Jelly
 * @author     Jonathan Geiger
 * @copyright  (c) 2010-2011 Jonathan Geiger
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Jelly_Core_Field_Slug extends Jelly_Field_String {

	/**
	 * @var  string  separator value in slug
	 */
	public $separator = '-';

	/**
	 * Converts a slug to value valid for a URL.
	 *
	 * @param   mixed  value
	 * @return  mixed
	 */
	public function set($value)
	{
		list($value, $return) = $this->_default($value);
		
		if ( ! $return)
		{
			// Only allow dashes, and lowercase letters
			$value = preg_replace('/[^a-z0-9-]/', $this->separator, strtolower($value));

			// Strip multiple dashes
			$value = preg_replace('/-{2,}/', $this->separator, $value);

			// Trim an ending or starting dashes
			$value = trim($value, $this->separator);
		}

		return $value;
	}

} // End Jelly_Core_Field_Slug