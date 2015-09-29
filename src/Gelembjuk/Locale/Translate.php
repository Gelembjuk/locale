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
* @version    1.0
* @link       https://github.com/Gelembjuk/locale
*/

namespace Gelembjuk\Locale;

class Translate {
	/**
	 * Path to a languages/locales folder
	 * 
	 * @val string
	 */
	protected $localespath;
	/**
	 * Current locale
	 * 
	 * @var string
	 */
	protected $locale;
	/**
	 * Internal data cache to speed up
	 * 
	 * @var array
	 */
	protected $cache;
	
	/**
	 * Constructor. Initializes a translation object
	 * 
	 * @param array $options Settings
	 */
	public function __construct($options = array()) {
		$this->cache = array();

		if ($options['locale'] != '') {
			$this->setLocale($options['locale']);
		}
		if ($options['localespath'] != '') {
			$this->localespath = $options['localespath'];
		}
	}
	/**
	 * Set locale
	 * 
	 * @param string $locale
	 */
	public function setLocale($locale) {
		$this->cache = array();
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
	public function getText($key,$group='', $p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '') {
		
		if (!$this->locale || $this->locale == '') {
			// if locale is not set then can not find something, so just return a text
			return $key;
		}
		
		if (!is_array($this->cache[$group])) {
			$this->cache[$group] = $this->loadDataForGroup($group);
		}
		
		if (isset($this->cache[$group][$key])) {
			// check if data are in a file
			if (strpos($this->cache[$group][$key],'file:') === 0) {
				$filname = substr($this->cache[$group][$key],5);
				$file_path = $this->localespath.$this->locale.'/files/'.$filname;
				
				if (@file_exists($file_path)) {
					$contents = @file_get_contents($file_path);
					// contents also can have some formatting to insert arguments
					if ($contents != '') {
						return sprintf($contents,$p1,$p2,$p3,$p4,$p5);
					}
					
					return '';
				}
			}
			return sprintf($this->cache[$group][$key],$p1,$p2,$p3,$p4,$p5);
		}
		
		return $key;
	}
	
	public function checkKeyExists($key,$group,$allowempty = false) {
		if (!$this->locale || $this->locale == '') {
			return false;
		}
		
		$data = $this->loadDataForGroup($group,$allowempty);
		
		if (isset($data[$key])) {
			// check if data are in a file
			if (strpos($data[$key],'file:') === 0) {
				$filname = substr($data[$key],5);
				$file_path = $this->localespath.$this->locale.'/files/'.$filname;
				
				if (@file_exists($file_path)) {
					return true;
				}
				
				return false;
			}
			return true;
		}
		
		return false;
	}

	public function getAllGroups() {
		if ($this->locale == '') {
			throw new \Exception('Locale is not set');
		}

		$folder_path = $this->localespath.$this->locale.'/';

		if (!is_dir($folder_path)) {
			throw new \Exception('Locale folder not found');
		}

		$files = @scandir($folder_path);

		$groups = array();

		if (is_array($files)) {
			foreach ($files as $file) {
				if (is_file($folder_path.$file) && strtolower(pathinfo($folder_path.$file, PATHINFO_EXTENSION)) == 'txt') {
					if (preg_match('!^(.+)\\.txt!i',$file,$m)) {
						$groups[] = $m[1];
					}
				}
			}
		}

		return $groups;
	}
	public function getDataForGroup($group) {
		if ($this->locale == '') {
			throw new \Exception('Locale is not set');
		}
		
		return $this->loadDataForGroup($group,true);
	}
	public function getAllKeysForGroup($group) {
		$cache = $this->getDataForGroup($group);
		
		return array_keys($cache);
	}
	protected function loadDataForGroup($group, $includeemptykeys = false) {
		// load strings to cache
		$file_path = $this->localespath.$this->locale.'/';
		
		if ($group != '') {
			$file_path .= $group;
		} else {
			$file_path .= 'default';
		}
		
		$file_path .= '.txt';
		
		if (!file_exists($file_path)) {	
			// translation file not found
			return $key;
		}
		
		$lines = @file_get_contents($file_path);
		
		if ($lines == '') {
			return $key;
		}
		
		$lines = preg_split('!\\r?\\n!', $lines);
		$lines = array_map('trim', $lines);
		
		$data = array();
		
		foreach($lines as $line) {
			if (strpos($line,'#') !== false) {
				// remove everything after #
				$line = substr($line,0,strpos($line,'#'));
			}
			list($k, $value) = explode('=', $line, 2);
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
}
