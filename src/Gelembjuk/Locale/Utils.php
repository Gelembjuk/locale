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
	protected function getTranslateObject($returnfilename = false) {
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
	public function getMissedKeysTemplate($group,$locale,$deflocale,$mode = 'empty',$returnlines = false, $textlinesplitter = "\n") {
		$translate = $this->getTranslateObject(true);
		$translate->setLocale($deflocale);
		
		$difference = $this->getDifference($group,$locale,$deflocale);
		
		$fixtextlines = array();
		
		foreach ($difference['missed'] as $key) {
			$line = $key.' = ';
			
			if ($mode == 'empty') {
				$line .= '#';
			}
			$line .= $translate->getText($key,$group);
			$fixtextlines[] = $line;
		}
		
		if ($returnlines) {
			return $fixtextline;
		}
		return implode($textlinesplitter,$fixtextlines);
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
	public function fixMissedKeysFromTemplate($group,$locale,$deflocale,$mode = 'empty',$textlinesplitter = "\n") {
		$text = $this->getMissedKeysTemplate($group,$locale,$deflocale,$mode,false, $textlinesplitter);
		
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
}
