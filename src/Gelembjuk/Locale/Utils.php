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
	 * Returns all groups-locale associations where are some data missed comparing to def locale
	 * 
	 * @param string $deflocale Default locale to use as a base for comparing
	 * @param bool $includingmissedindef Defines if to check what is present in other locales and missed in def locale
	 */
	protected function getTranslateObject() {
		return new Translate(array('localespath' => $this->localespath));
	}
	public function getAllGrupsWithMisses($deflocale,$includingmissedindef = true) {
		$result = array();

		$translate = $this->getTranslateObject();
		$translate->setLocale($deflocale);
		
		$defgroups = $translate->getAllGroups();

		$usedlanguages = $this->getUsedLanguages();
		
		foreach ($usedlanguages as $lang => $langdata) {
			foreach ($defgroups as $group) {
				
				$difference = $this->getDifference($group,$lang,$deflocale);
				
				if ($difference['total'] > 0) {
					if (!is_array($result[$lang])) {
						$result[$lang] = array();
					}
					$result[$lang][$group] = $difference['missedcount'];
				}
			}
		}

		return $result;
	}

	public function getDifference($group,$locale,$deflocale) {
		$translate = $this->getTranslateObject();
		$translate->setLocale($deflocale);
		
		$allkeys = $translate->getAllKeysForGroup($group);
		
		$translate->setLocale($locale);
		
		$keys = $translate->getAllKeysForGroup($group);
		
		$result['missed'] = array_diff($allkeys,$keys);
		$result['extra'] = array_diff($keys,$allkeys);
		
		$result['missedcount'] = count($result['missed']);
		$result['extracount'] = count($result['extra']);
		
		$result['total'] = $result['missedcount']+$result['extracount'];
		
		return $result;
	}

	public function fixMissedWithDefault($group,$locale,$deflocale) {
	}

	public function fixMissedWithEmpty($group,$locale,$deflocale) {
	}
}
