<?php 

/**
 * Example. Usage of Gelembjuk/Locale . 
 * 
 * This example is part of gelembjuk/locale package by Roman Gelembjuk (@gelembjuk)
 */

// ==================== CONFIGURATION ==================================
// path to your composer autoloader
require ('vendor/autoload.php');

// folder where translations are stored
$lang_folder_path = dirname(__FILE__) . '/lang/'; 

// get locale from url
$locale = $_REQUEST['locale'];

if ($locale == '') {
	// set english of not posted
	$locale = 'en';
}

// create translation object
$translation = new Gelembjuk\Locale\Translate(
	array(
		'localespath' => $lang_folder_path,
		'locale' => $locale
	)
	);

// =============================================================
echo '<h1>Test 1. Welcome message</h1>';

$username = 'Johm Smith';

// return formatted text , insert user's info
echo '<p>'.$translation->getText('hello',''/*default group*/,$username).'</p>';

// return big translater text
echo '<div>'.$translation->getText('about','frontpage').'</div>';

// =============================================================
echo '<h1>Test 2. Class with translation functionality</h1>';

class MyClass {
	// include translation functions
	use Gelembjuk\Locale\GetTextTrait;
	
	public function doSomething() {
		echo $this->getText('welcome').'<br>';
	}
	
	public function andAgainWelcome($name) {
		// use short call for getText
		echo $this->_('hello','',$name).'<br>';
		
		echo $this->_('contact','frontpage').'<br>';
	}
}

$obj = new MyClass();

// this meathod comes from a trait
$obj->setTranslation($translation);

$obj->doSomething();

$obj->andAgainWelcome($username);

echo '<br><br>';
// change to english
$obj->setLocale('en');
// and repeat
$obj->andAgainWelcome($username);

// =============================================================
echo '<h1>Test 3. Show language change select box</h1>';
// output form to change locale

$languages = new Gelembjuk\Locale\Languages(array('localespath' => $lang_folder_path));

echo '<hr><form name=\'langform\' method=\'GET\' action="index.php">';

echo $languages->getHTMLSelect(' name="locale" onchange="document.langform.submit()" ',$locale/*current selected locale*/);

echo '</form>';
