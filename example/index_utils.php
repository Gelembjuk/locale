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
$deflocale = 'en';

$utils = new Gelembjuk\Locale\Utils(array('localespath' => $lang_folder_path));


echo '<p><a href="index.php">Back to main test</a></p>';
// =============================================================
echo '<h1>List all groups where some keys are missed (default locale is '.$deflocale.')</h1>';

$misses = $utils->getAllGrupsWithMisses($deflocale);

echo '<ul>';

foreach ($misses as $locale=>$groups) {
	echo '<li>'.$locale.'</li>';
	echo '<ul>';
	
	foreach ($groups as $group=>$missedcount) {
		echo '<li>'.$group.' ('.$missedcount.') '.'</li>';
	}
	echo '</ul>';
}
echo '</ul>';

// =============================================================
echo '<h1>List each missed key in each groups and locale</h1>';

echo '<ul>';

foreach ($misses as $locale=>$groups) {
	echo '<li>'.$locale.'</li>';
	echo '<ul>';
	
	foreach ($groups as $group=>$missedcount) {
		echo '<li>'.$group.' ('.$missedcount.') '.'</li>';
		echo '<ul>';
		
		$missedkeys = $utils->getDifference($group,$locale,$deflocale);
		
		foreach ($missedkeys['missed'] as $key) {
			echo '<li>'.$key.'</li>';
		}
		
		echo '</ul>';
	}
	echo '</ul>';
}
echo '</ul>';

// =============================================================
echo '<h1>List keys in text format to add to a translation file</h1>';

echo '<ul>';

foreach ($misses as $locale=>$groups) {
	echo '<li>'.$locale.'</li>';
	echo '<ul>';
	
	foreach ($groups as $group=>$misses) {
		echo '<li>'.$group.' ('.$misses.') '.'</li>';
		echo '<li>';
		
		$text = $utils->getMissedKeysTemplate($group,$locale,$deflocale,'empty');
		
		echo '<textarea rows="4" cols="80">'.$text.'</textarea>';
		
		echo '</li>';
	}
	echo '</ul>';
}
echo '</ul>';



