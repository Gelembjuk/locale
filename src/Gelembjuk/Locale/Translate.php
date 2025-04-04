<?php

/**
* Localization/Internationalization support class. Helps to translate text keys for different locales.
* Data must be in folders names with locale names. For example, in a folder 'lang' you need to have 
* lang/en, lang/ge, lang/fr  and in each folder you need to a have a file default.txt with key/value pairs
* default.txt
* key1=Text1
* key2=Text2
* .....
* 
* It is possible to have other files which will be separate groups. 
*
* LICENSE: MIT
*
* @category   Localization
* @package    Gelembjuk/Locale
* @copyright  Copyright (c) 2015 Roman Gelembjuk. (http://gelembjuk.com)
* @version    1.2
* @link       https://github.com/Gelembjuk/locale
*/

namespace Gelembjuk\Locale;

class Translate {
	/**
	 * Path to a languages/locales folder
	 * 
	 * @val string
	 */
	protected string $localespath;
	/**
	 * Current locale
	 * 
	 * @var string
	 */
	protected string $locale;
	/**
	 * Internal data cache to speed up
	 * 
	 * @var array
	 */
	protected array $cache = [];
	/**
	 * Marker for a mode to return big texts file name
	 * instead of file contents. 
	 * Such mode is needed for management of translations
	 * and is not used on production
	 * 
	 * @var string
	 */
	protected bool $returnfilename;
	
	/**
	 * Constructor. Initializes a translation object
	 * 
	 * @param array $options Settings
	 */
	public function __construct(string $localespath = '', string $locale = '', bool $returnfilename = false) 
	{
		$this->cache = [];

		$this->localespath = $localespath;
		$this->locale = $locale;
		$this->returnfilename = $returnfilename;
	}

	public function withLocalePath(string $path)
	{
		$this->localespath = $path;
		return $this;
	}
	public function withLocale(string $locale)
	{
		$this->locale = $locale;
		return $this;
	}
	public function doReturnFileName()
	{
		$this->returnfilename = true;
		return $this;
	}
	/**
	 * Set locale
	 * 
	 * @param string $locale
	 */
	public function setLocale($locale) 
	{
		$this->cache = [];
		$this->locale = $locale;
	}
	/**
	 * Returns text by a key/group and inserts data if it is formatted string
	 * 
	 * @param string $key Key of text to translate
	 * @param string $group Group of keys
	 * @param string $p1 Variable 1 to insert in a text if a value fo a key is formatted string
	 * @param string $p2 Variable 2
	 * @param string $p3 Variable 3
	 * @param string $p4 Variable 4
	 * @param string $p5 Variable 5
	 * 
	 * @return string
	 */
	public function getText($key, $group = '', ...$params) : string
	{
		
		if (empty($this->locale)) {
			// if locale is not set then can not find something, so just return a text
			return $key;
		}
		
		if (!is_array($this->cache[$group] ?? null)) {
			$groupkeys = $this->loadDataForGroup($group);
			
			if ($groupkeys == null) {
				return $key;
			}
			
			$this->cache[$group] = $groupkeys;
		}
		
		if (!isset($this->cache[$group][$key])) {
			// key not found
			return $key;
		}
		// check if data are in a file
		if (strpos($this->cache[$group][$key],'file:') === 0 && !$this->returnfilename) {
			$filname = substr($this->cache[$group][$key],5);
			$file_path = $this->localespath.$this->locale.'/files/'.$filname;
			
			$contents = '';

			try {
				if (@file_exists($file_path)) {
					$contents = file_get_contents($file_path);

					if (empty($contents)) {
						return $key;
					}
					return sprintf($contents, ...$params);
				}
			} catch (\Throwable $e) {
				return $key;
			}
		}
		return sprintf($this->cache[$group][$key], ...$params);
	}
	/**
	 * Loads data (keys,values) for a group from file
	 * 
	 * @param string $group Group name
	 * 
	 * @return array
	 */
	protected function loadDataForGroup($group, $includeemptykeys = false) 
	{
		// load strings to cache
		$file_path = $this->getGroupFile($group);
		
		if (!file_exists($file_path)) {	
			// translation file not found
			return null;
		}
		
		$lines = '';

		try {
			$lines = @file_get_contents($file_path);

			if ($lines == '') {
				return null;
			}
		} catch (\Throwable $e) {
			return null;
		}
		
		$lines = preg_split('!\\r?\\n!', $lines);
		$lines = array_map('trim', $lines);
		
		$data = [];
		
		foreach($lines as $line) {
			if (strpos($line,'#') !== false) {
				// remove everything after #
				$line = substr($line,0,strpos($line,'#'));
			}
			if (strpos($line,'=') > 0 && strpos($line,'=') < strlen($line)-1) {
				list($k, $value) = explode('=', $line, 2);	
			} else {
				$k = $line;
				$value = '';
			}
			
			$k = trim($k);
			$value = trim($value);
			
			if (strpos($k,'#') === 0 || $k == '' || $value == '' && !$includeemptykeys) {
				// comment or empty line
				continue;
			}
			
			$data[$k] = $value;
		}
		
		return $data;
	}
	public function getGroupFile($group,$locale = '') 
	{	
		if ($locale == '') {
			$locale = $this->locale;
		}
		
		$file_path = $this->localespath.$locale.'/';
		
		if ($group != '') {
			$file_path .= $group;
		} else {
			$file_path .= 'default';
		}
		
		$file_path .= '.txt';
		
		return $file_path;
	}
}
