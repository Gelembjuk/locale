<?php

/**
* Class to manipulate with languages. Detects what locales are used in the system. 
* Returns list of languages and attributes like a flag or a region 
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

class Languages {
	/**
	 * Path to locales directory
	 * 
	 * @var string
	 */
	protected $localespath;
	/**
	 * Switcher on ho to behave when some language/locale information is missed
	 * 
	 * @var boolean
	 */
	protected $errorondatamissed;
	/**
	 * Languages list cache
	 * 
	 * @var array
	 */
	protected $alllanguages;
	
	/**
	 * Constructor
	 * 
	 * Initis options
	 * 
	 * @param array $options array of options
	 */
	public function __construct($options = []) 
	{
		$this->alllanguages = null;
		
		if (!empty($options['localespath'])) {
			$this->localespath = $options['localespath'];
		}
		
		$this->errorondatamissed = false;
		
		if (isset($options['errorondatamissed'])) {
			$this->errorondatamissed = $options['errorondatamissed'];
		}
		
		if ($this->errorondatamissed && 
			($this->localespath == '' || !is_dir($this->localespath))) {
				throw new \Exception('Locales directory path is not provided or is not correct');
			}
	}
	/**
	 * Returns all languages with all attributes installed on the system
	 * 
	 * @return array
	 */
	public function getAllLanguages() 
	{
		
		if (is_array($this->alllanguages)) {
			return $this->alllanguages;
		}
		
		// find languages file in a locales path
		$langfile = $this->localespath.'languages.txt';

		if (!file_exists($langfile)) {
			// if no in locales then use default from this package
			$langfile = dirname(__FILE__).'/languages.txt';
		}

		if (!file_exists($langfile)) {
			// if not found then show error or return empty array
			if ($this->errorondatamissed) {
				throw new \Exception('No languages file found');
			}
			return [];
		}
		
		// load a file
		$list = file_get_contents($langfile);
		
		if (!$list) {
			// if somethign went wrong throw error or return empty array
			if ($this->errorondatamissed) {
				throw new \Exception('Can not load languages list');
			}
			return [];
		}
		
		$languages = [];
		
		foreach (preg_split('!\\r?\\n!',$list) as $line) {
			
			list ($code,$name) = explode('=',$line);
			
			$code = trim($code);
			$name = trim($name);
			
			if ($code == '' || $name == '') {
				continue;
			}
			
			$origname = $name;
			$tags = array();
			
			$splitsettings = explode(':',$name,3);
			
			if (count($splitsettings) > 1) {
				$name = $splitsettings[0];
				$origname = $splitsettings[1];
				
				if (isset($splitsettings[2]) && $splitsettings[2] != '') {
					$tags = explode(',',$splitsettings[2]);
				}
			}
			
			$flagfile = $this->localespath.'flags/small/'.$code.'.png';
			
			$hasflag = file_exists($flagfile);
			
			$languages[$code] = [
				'code'=>$code,
				'name'=>$name,
				'origname'=>$origname,
				'hasflag'=>$hasflag,
				'tags' => $tags
			];
		}
		return $languages;
	}
	/**
	 * Return used languages. This are languages with any translation text set
	 * 
	 * @return array
	 */
	public function getUsedLanguages() 
	{
		$alllanguages = $this->getAllLanguages();
		
		$usedlanguages = array();
		
		foreach ($alllanguages as $code=>$lang) {
			// check if lang folder and default file exists
			$langfile = $this->localespath.$code.'/default.txt';
			
			if (file_exists($langfile)) {
				$usedlanguages[$code] = $lang;
			}
		}
		
		return $usedlanguages;
	}
	/**
	 * Return used languages. This are languages with any translation text set
	 * 
	 * @return array
	 */
	public function getLanguagesFiltered($tag) 
	{
		$alllanguages = $this->getAllLanguages();
		
		$languages = array();
		
		foreach ($alllanguages as $code=>$lang) {
			if (in_array($tag,$lang['tags'])) {
				$languages[$code] = $lang;
			}
		}
		
		return $languages;
	}
	/**
	 * Return languages listed in the argument array. Is used for case when it is needed to get list of specified languages
	 * 
	 * @return array
	 */
	public function getLanguagesFromList($list) 
	{
		$alllanguages = $this->getAllLanguages();
		
		$languages = array();
		
		foreach ($alllanguages as $code=>$lang) {
			if (in_array($code,$list)) {
				$languages[$code] = $lang;
			}
		}
		
		return $languages;
	}
	
	/*
	* Reformat list of languages as a hash. Input array must be result of one of above functions
	* @return array
	*/
	public function getLanguagesAsHash($langlist,$name = true) 
	{
		$hash = array();
		
		foreach ($langlist as $lang) {
			$hash[$lang['code']] = ($name)?$lang['name']:$lang['origname'];
		}
		
		return $hash;
	}
	/**
	 * Build HTML select consruction with used languages
	 * Can be used on a web site to show a Language change dropbox
	 * 
	 * @param string $attributes for a select box
	 * @param string $currentlang Current selected language code (locale)
	 * @param boolean $orignames If true then original manes of languages ill be used as titles in a dropbox
	 * 
	 * @return string
	 */
	public function getHTMLSelect($attributes = '',$currentlang = '',$orignames = false) 
	{
		$html = '<select '.$attributes.'>';
		
		foreach ($this->getUsedLanguages() as $code=>$lang) {
			$html .= '<option value="'.$code.'" ';
			
			if ($currentlang == $code) {
				$html .= 'selected';
			}
			
			$html .= '>';
			
			if ($orignames) {
				$html .= $lang['origname'];
			} else {
				$html .= $lang['name'];
			}
		}
		
		$html .= '</select>';
		
		return $html;
	}
}
