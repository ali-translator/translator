# Translator

 The Translation component provides tools to internationalize your application.

## Installation

```bash
$ composer require ali-translator/translator
```

### Simplified translator

Example of init one direction translator (one original and one translation languages):
```php
use \ALI\Translator\Source\Sources\MySqlSource\MySqlSource;
use \ALI\Translator\PlainTranslator\PlainTranslatorFactory;

$originalLanguageAlias = 'en';
$translationLanguageAlias = 'ru';

$connection = new PDO(SOURCE_MYSQL_DNS, SOURCE_MYSQL_USER, SOURCE_MYSQL_PASSWORD);
$source = new MySqlSource($this->createPDO(), $originalLanguageAlias);
$plainTranslator = (new PlainTranslatorFactory())->createPlainTranslator($source,$translationLanguageAlias);

$plainTranslator->saveTranslate('Hello','Привет');
$plainTranslator->translate('Hello'); // -> 'Привет'
$plainTranslator->translateAll(['Hello']); // -> ['Привет']

// Try translate not exist phrase
$plainTranslator->translate('Goodbye'); // -> null
$plainTranslator->translate('Goodbye', true); // With fallback -> 'Goodbye'

$plainTranslator->delete('Hello');
```

### Complex translator
If you need few original and translation languages, this way for you:
```php
use \ALI\Translator\Translator;
use \ALI\Translator\Source\SourceInterface;
use \ALI\Translator\Source\SourcesCollection;
use \ALI\Translator\PlainTranslator\PlainTranslator;

/** @var SourceInterface $firsSource */
/** @var SourceInterface $secondSource */

$sourceCollection = new SourcesCollection();
$sourceCollection->addSource($firsSource,['ua', 'ru']);
$sourceCollection->addSource($secondSource); // Second source for all another translation languages
$translator = new Translator($sourceCollection);

$translator->saveTranslate('en', 'ru', 'Hello', 'Привет');
$translator->translate('en', 'ru', 'Hello');

// Also, if you need, you always can transform translator to plainTranslator to work simplification
$plainTranslator = new PlainTranslator('en', 'ru', $translator)
```

### Missing translation catchers
Packet allow set catchers phrases without translation, which will work after fail `tranlsate` method calling

```php
use ALI\Translator\TranslatorInterface;
use ALI\Translator\MissingTranslateCatchers\CollectorMissingTranslatesCatcher;

/** @var TranslatorInterface $translator */
$translator->addMissingTranslationCatchers(function (string $searchPhrase,TranslatorInterface $translator){
    ...
});

// Or use existed CollectorMissingTranslatesCatcher
$collectorMissingTranslatesCatcher = new CollectorMissingTranslatesCatcher();
$translator->addMissingTranslationCatchers($collectorMissingTranslatesCatcher);
$translator->translate('Hello 123');
$collectorMissingTranslatesCatcher->getOriginalPhraseCollection()->getAll();
 // -> ['Hello 123']
```

### Phrase decorators
If you need decorate original-phrase or translated-phrase before output - this section for you.<br>
Example, of existed decorator, which replaces numbers to "0" before saving originals,
 and restoring correct number after translate.
```php
use ALI\Translator\Decorators\PhraseDecorators\OriginalDecorators\ReplaceNumbersOriginalDecorator;
use ALI\Translator\Decorators\PhraseDecorators\OriginalPhraseDecoratorManager;
use ALI\Translator\Decorators\PhraseDecorators\TranslateDecorators\RestoreNumbersTranslateDecorator;
use ALI\Translator\Decorators\PhraseDecorators\TranslatePhraseDecoratorManager;
use ALI\Translator\Decorators\PhraseTranslatorDecorator;
use ALI\Translator\TranslatorInterface;
use ALI\Translator\PlainTranslator\PlainTranslator;

/** @var TranslatorInterface $translator */

$originalDecoratorManger = new OriginalPhraseDecoratorManager([
    new ReplaceNumbersOriginalDecorator(),
]);
$translateDecoratorManager = new TranslatePhraseDecoratorManager([
    new RestoreNumbersTranslateDecorator(),
]);

$phraseTranslatorDecorator = new PhraseTranslatorDecorator($translator, $originalDecoratorManger, $translateDecoratorManager);
// For simplifying
$plainPhraseTranslatorDecorator = new PlainTranslator('en', 'ua', $phraseTranslatorDecorator);

$plainPhraseTranslatorDecorator->saveTranslate('Hello 123 Hi 000','Привіт 123 Хай 000');
// and when translate text with another numbers, you get previous saved translation
$plainPhraseTranslatorDecorator->translate('Hello 555 Hi 8676');
// -> 'Привіт 555 Хай 8676'
```

#### Available sources
* <b>MySqlSource</b> - recommended for using with `ali-translator/buffered-translation` to reduce the number of requests to Source
* <b>CsvFileSource</b> - csv source. Files will be look like as `en-ua.csv`

### Suggest packets
* <b>ali-translator/translator-js-integrate</b> - Integrate this packet to frontend js
* <b>ali-translator/buffered-translation</b> - Manually pasted text on document for translation, by means of buffering is translated by one approach (helpful for DB sources)
* <b>ali-translator/auto-html-translation</b> - Parses html document, and translate included texts
* <b>ali-translator/url-template</b> - Helps on url language resolving

### Tests
In packet exist docker-compose file, with environment for testing.
```bash
docker-compose up -d
docker-compose exec php bash
# And on opened docker container:
composer install
./vendor/bin/phpunit
``` 
