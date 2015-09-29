<?php

/**
* Localization/Internationalization support class. The class helps to manage groups/keys 
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

class TranslateManagement extends Translate {
	/**
	 * Check if a key exists for a group. Is used for translations management
	 * 
	 * @param string $key Key of text to translate
	 * @param string $group Group of keys
	 * @param bool $allowempty Marker if empty key is allowed
	 * 
	 * @return bool
	 */
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
	/**
	 * Get array of all groups for current locale
	 * 
	 * @return array
	 */
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
	/**
	 * Return list of all keys for a group with values
	 * 
	 * @param string $group Group name
	 * 
	 * @return array
	 */
	public function getDataForGroup($group) {
		if ($this->locale == '') {
			throw new \Exception('Locale is not set');
		}
		
		return $this->loadDataForGroup($group,true);
	}
	/**
	 * Return list of all keys for a group
	 * 
	 * @param string $group Group name
	 * 
	 * @return array
	 */
	public function getAllKeysForGroup($group) {
		$cache = $this->getDataForGroup($group);
		
		return array_keys($cache);
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
