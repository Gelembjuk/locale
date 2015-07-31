## Gelembjuk/Locale

Internationalization support for PHP applications. It is a simple set of classes to manage languages/locales, to translate texts depending on a locale.
The package contains a trait to include internationalization functions to any classes very easy and with minimum coding.

### Installation
Using composer: [gelembjuk/locale](http://packagist.org/packages/gelembjuk/locale) ``` require: {"gelembjuk/locale": "1.*"} ```


### Configuration

No configuration needed.

You need to have a folder with your texts on different languages. Separate fodler for each locale, like en,ge,it,.... Each folder should contain minimum one file default.txt

Translation files have simple key=values format

### Usage


```php

require '../vendor/autoload.php';

$translation = new Gelembjuk\Locale\Translate(
	array(
		'localespath' => $lang_folder_path, // path to your translations directory
		'locale' => $locale // current locale, 2 symbol language code
	)
	);
	
echo $translation->getText('hello',''/*default group*/,$username);  // hello = Hello, %s on our site. $username will be put in place of %s

echo $translation->getText('welcome');

echo $translation->getText('backsoon','logoutpage'); // custom texts group in a separate web site

```

**Trait** usage

```php
class MyClass {
	// include translation functions
	use Gelembjuk\Locale\GetTextTrait;
	
	public function doSomething() {
		echo $this->getText('welcome').'<br>';
	}
	
	public function andAgainWelcome($name) {
		// use short call for getText
		echo $this->_('hello','',$name).'<br>';
	}
}

$obj = new MyClass();

$obj->setTranslation($translation);

$obj->doSomething();

$obj->andAgainWelcome($username);

$obj->setLocale('en');

$obj->andAgainWelcome($username);

```

Languages manager usage


```php
$langobj = new Gelembjuk\Locale\Languages(array('localespath' => $lang_folder_path));

// list of used languages, used are languages with a folder in $lang_folder_path
$languages = $langobj->getUsedLanguages();

// print language select form

echo '<form name=\'langform\' method=\'GET\' action="index.php">';

echo $langobj->getHTMLSelect(' name="locale" onchange="document.langform.submit()" ',$locale/*current selected locale*/);

echo '</form>';

```

### Author

Roman Gelembjuk (@gelembjuk)

