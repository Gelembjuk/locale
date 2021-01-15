<?php

/**
* Class to operate translation files, find missed strings and autofix them.
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

class Utils extends Languages {
	/**
	 * Returns translation management object
	 * 
	 */
	public function getTranslateObject($returnfilename = false) {
		return new TranslateManagement(array('localespath' => $this->localespath,
			'returnfilename' => $returnfilename));
	}
	/**
	 * Returns all groups-locale associations where are some data missed comparing to def locale
	 * 
	 * @param string $deflocale Default locale to use as a base for comparing
	 * @param bool $includingmissedindef Defines if to check what is present in other locales and missed in def locale
	 */
	public function getAllGrupsWithMisses($deflocale,$includingmissedindef = true) {
		$result = array();

		$translate = $this->getTranslateObject();
		$translate->setLocale($deflocale);
		
		$defgroups = $translate->getAllGroups();

		$usedlanguages = $this->getUsedLanguages();
		
		foreach ($usedlanguages as $lang => $langdata) {
			foreach ($defgroups as $group) {
				
				$difference = $this->getDifference($group,$lang,$deflocale);
				
				if ($difference['missedcount'] > 0) {
					if (!is_array($result[$lang])) {
						$result[$lang] = array();
					}
					$result[$lang][$group] = $difference['missedcount'];
				}
			}
		}

		return $result;
	}
	/**
	 * Returns information about difference between same group file in different locales
	 * an be used to find what is missed in secondary languages files
	 * 
	 * @param string $group Translation group
	 * @param string $locale Locale to compare to default locale
	 * @param string $deflocale Default locale to use as a base for comparing
	 * 
	 * @return array
	 */
	public function getDifference($group,$locale,$deflocale) {
		$translate = $this->getTranslateObject();
		$translate->setLocale($deflocale);
		
		$allkeys = $translate->getAllKeysForGroup($group);
		
		$translate->setLocale($locale);
		
		$result['groupmissed'] = false;
		
		try {
			$keys = $translate->getAllKeysForGroup($group);
		} catch (\Exception $e) {
			$result['groupmissed'] = true;
			$keys = array();
		}
		
		$result['missed'] = array_values(array_diff($allkeys,$keys));
		$result['extra'] = array_values(array_diff($keys,$allkeys));
		
		$result['missedcount'] = count($result['missed']);
		$result['extracount'] = count($result['extra']);
		
		$result['total'] = $result['missedcount']+$result['extracount'];

		return $result;
	}
	/**
	 * Returns text of array of lines to add toa secondary locale group to correct it
	 * 
	 * @param string $group Translation group
	 * @param string $locale Locale to compare to default locale
	 * @param string $deflocale Default locale to use as a base for comparing
	 * @param string $mode `empty` or `default`. If empty then generated keys are empty and deflocale value is added as comment
	 * 
	 * @return array|string
	 */
	public function getMissedKeysTemplate($group,$locale,$deflocale,$mode = 'empty',$returntype = 'text', $textlinesplitter = "\n") {
		$translate = $this->getTranslateObject(true);
		$translate->setLocale($deflocale);
		
		$difference = $this->getDifference($group,$locale,$deflocale);
		
		$fixtextlines = array();
		
		foreach ($difference['missed'] as $key) {
			$line = $key.' = ';
			
			if ($mode == 'empty') {
				$line .= '#';
			}
			$defvalue = $translate->getText($key,$group);
			$line .= $defvalue;
			
			if ($returntype == 'hash') {
				$fixtextlines[$key] = $defvalue;
			} else {
				$fixtextlines[] = $line;
			}
		}
		
		if ($returntype == 'lines' || $returntype == 'hash') {
			return $fixtextline;
		}
		return implode($textlinesplitter,$fixtextlines);
	}
	/**
	 * Fixes a group by adding all missed keys with empty values or def locale values 
	 * 
	 * @param string $group Translation group
	 * @param string $locale Locale to compare to default locale
	 * @param string $deflocale Default locale to use as a base for comparing
	 * @param string $mode `empty` or `default`. If empty then generated keys are empty and deflocale value is added as comment
	 * 
	 * @return bool
	 */
	public function fixMissedKeysFromTemplate($group,$locale,$deflocale,$mode = 'empty',$textlinesplitter = "\n") {
		$text = $this->getMissedKeysTemplate($group,$locale,$deflocale,$mode,'text', $textlinesplitter);
		
		$translate = $this->getTranslateObject();
		
		$groupfile = $translate->getGroupFile($group,$locale);
		
		$groupfilecontents = '';
		
		if (file_exists($groupfile)) {
			$groupfilecontents = @file_get_contents($groupfile);
		}
		
		$groupfilecontents .= $textlinesplitter . $text;
		
		file_put_contents($groupfile,$groupfilecontents);
		
		return true;
	}
	/**
	 * Saves the list of key=>values to the group file. Fully overwrites
	 * 
	 * @param string $group Translation group
	 * @param string $locale Locale to compare to default locale
	 * @param array $data Translation data - key,values
	 * @param string textlinesplitter Lien break symbol
	 * 
	 * @return array|string
	 */
	public function saveGroupFile($group,$locale,$data,$textlinesplitter = "\n")
	{
        $translate = $this->getTranslateObject();
		
		$groupfile = $translate->getGroupFile($group,$locale);
		
		array_walk($data, function(&$value, $key) { $value = "$key = $value"; });
		
		$groupfilecontents .= implode($textlinesplitter,$data).$textlinesplitter;

		file_put_contents($groupfile,$groupfilecontents);
	}
	/**
	 * Returns all empty keys from a group and associated values from def locale group
	 * 
	 * @param string $group Translation group
	 * @param string $locale Locale to compare to default locale
	 * @param string $deflocale Default locale to use as a base for comparing
	 * @param bool $skipfiles If to skip keys pointing to files with a text
	 * 
	 * @return array|string
	 */
	public function getEmptyKeysWithDefValues($group,$locale,$deflocale,$skipfiles = true) {
		$translate = $this->getTranslateObject(true);
		$translate->setLocale($deflocale);
		
		$allkeys = $translate->getAllKeysForGroup($group);
		$alltranslates = $translate->getDataForGroup($group);
		
		$translate->setLocale($locale);
		
		try {
			$keys = $translate->getAllKeysForGroup($group);
			$translates = $translate->getDataForGroup($group);
		} catch (\Exception $e) {
			$keys = array();
			$translates = array();
		}
		
		$missed = array_values(array_diff($allkeys,$keys));
		
		$result = array();
		
		foreach ($translates as $key => $value) {
			if ($value == '') {
				$missed[] = $key;
			}
		}
		
		foreach ($missed as $key) {
			$result[$key] = $alltranslates[$key];
			
			if ($result[$key] == '') {
				$result[$key] = '*';
			} elseif (strpos($result[$key],'file:') === 0 && $skipfiles) {
				unset($result[$key]);
			}
		}

		return $result;
	}
	/**
	 * Receives a group of values as a list and assigns to missed keys in a group of locale.
	 * It can be used to quick translate of values as text file with a value per line.
	 * 
	 * @param string $group Translation group
	 * @param string $locale Locale to compare to default locale
	 * @param string $deflocale Default locale to use as a base for comparing
	 * @param bool $skipfiles If to skip keys pointing to files with a text
	 * 
	 * @return bool
	 */
	public function fixMissedKeysFromList($lines,$group,$locale,$deflocale,$skipfiles = true, $textlinesplitter = "\n") {
		$list = $this->getEmptyKeysWithDefValues($group,$locale,$deflocale,$skipfiles);
		
		if (count($list) != count($lines)) {
			throw new \Exception(sprintf('Wrong count of input lines. %d vs %d',count($list),count($lines)));
		}
		
		$translate = $this->getTranslateObject(true);
		
		$translate->setLocale($locale);
		
		try {
			$translates = $translate->getDataForGroup($group);
		} catch (\Exception $e) {
			$translates = array();
		}
		// assign new values and keys
		foreach ($list as $key => $value) {
			$value = array_shift($lines);
			
			$translates[$key] = $value;
		}
		
		$groupfile = $translate->getGroupFile($group,$locale);
		
		array_walk($translates, function(&$value, $key) { $value = "$key = $value"; });
		
		$groupfilecontents .= implode($textlinesplitter,$translates).$textlinesplitter;
		
		file_put_contents($groupfile,$groupfilecontents);
		
		return true;
	}
}
