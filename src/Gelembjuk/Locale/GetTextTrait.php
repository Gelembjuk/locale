<?php

/**
* The trait Gelembjuk\Locale\GetTextTrait is used to include a locale and translation functions in different classes with minimum coding
*
* LICENSE: MIT
*
* @category   Localization
* @package    Gelembjuk/Locale
* @copyright  Copyright (c) 2015 Roman Gelembjuk. (http://gelembjuk.com)
* @version    1.0
* @link       https://github.com/Gelembjuk/locale
*/

namespace Gelembjuk\Locale;

trait GetTextTrait {
	/**
	 * Translation object. It must be instance of Gelembjuk\Locale\Translate 
	 * In future we will support other popular locale support classes
	 * 
	 * @var Gelembjuk\Locale\Translate 
	 */
	protected $translation;
	
	/**
	 * Locale, 2 chars language code
	 * 
	 * @var string
	 */
	protected $locale;
	
	/**
	 * Shortcut for getText function
	 * 
	 * @param string $key Key of text to translate
	 * @param string $group Group of keys
	 * 
	 * @return string
	 */
	protected function _($key,$group='', ...$params) 
	{
		return $this->getText($key,$group, ...$params);
	}
	/**
	 * Returns a text by a key/group and current locale.
	 * Text can be formatted string and values $p1,$p2,.... can be inserted 
	 * 
	 * @param string $key Key of text to translate
	 * @param string $group Group of keys
	 * @param string $params Parameters to insert in a text if a value fo a key is formatted string
	 * 
	 * @return string
	 */
	public function getText($key,$group = '', ...$params) 
	{
		
		if (is_object($this->translation)) {
			return $this->translation->getText($key,$group, ...$params);
		}
		
		if (property_exists($this,'context') && is_object($this->context) && method_exists($this->context,'getText')) {
			return $this->context->getText($key,$group, ...$params);
		}
		
		return $key;
	}
	/**
	 * Return current locale
	 * 
	 * @return string
	 */
	public function getLocale() 
	{
		return $this->locale;
	}
	/**
	 * Set locale
	 * 
	 * @param string $locale
	 */
	public function setLocale($locale) 
	{
		$this->locale = $locale;
		
		if ($this->checkTranslateObjectIsSet()) {
			$this->translation->setLocale($this->locale);
		}
	}
	/**
	 * Create translate object 
	 * 
	 * @param Gelembjuk\Locale\Translate $translation
	 */
	public function setTranslation(Translate $translation) 
	{
		$this->translation = $translation;
	}
	/**
	 * Create translate object with gives arguments
	 * 
	 * @param array $options Options for Gelembjuk\Locale\Translate class object
	 */
	protected function initTranslateObject(array $options) 
	{
		$this->translation = new Translate($options);
	}
	/**
	 * Check if translation object exists in a class
	 * 
	 * @return boolean
	 */
	protected function checkTranslateObjectIsSet() 
	{
		return is_object($this->translation);
	}
}
