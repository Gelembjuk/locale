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
	 * Marker for a mode to return big texts file name
	 * instead of file contents. 
	 * Such mode is needed for management of translations
	 * and is not used on production
	 * 
	 * @var string
	 */
	protected $returnfilename = false;
	
	/**
	 * Constructor. Initializes a translation object
	 * 
	 * @param array $options Settings
	 */
	public function __construct($options = array()) {
		$this->cache = array();

		if (is_array($options)) {
			if (($options['locale'] ?? '' ) != '') {
				$this->setLocale($options['locale']);
			}
			if (($options['localespath'] ?? '') != '') {
				$this->localespath = $options['localespath'];
			}
			if (($options['returnfilename'] ?? '') != '') {
				$this->returnfilename = $options['returnfilename'];
			}
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
			$groupkeys = $this->loadDataForGroup($group);
			
			if ($groupkeys == null) {
				return $key;
			}
			
			$this->cache[$group] = $groupkeys;
		}
		
		if (isset($this->cache[$group][$key])) {
			// check if data are in a file
			if (strpos($this->cache[$group][$key],'file:') === 0 && !$this->returnfilename) {
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
	/**
	 * Loads data (keys,values) for a group from file
	 * 
	 * @param string $group Group name
	 * 
	 * @return array
	 */
	protected function loadDataForGroup($group, $includeemptykeys = false) {
		// load strings to cache
		$file_path = $this->getGroupFile($group);
		
		if (!file_exists($file_path)) {	
			// translation file not found
			return null;
		}
		
		$lines = @file_get_contents($file_path);
		
		if ($lines == '') {
			return null;
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
			
			if ($value === null) {
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
	public function getGroupFile($group,$locale = '') {
		
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
