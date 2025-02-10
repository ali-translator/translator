EventDrivenSource is a decorator that adds event logic to any SourceInterface of an object.

```php
use \ALI\Translator\Event\EventDispatcher;
use \ALI\Translator\Source\Sources\EventDriven\EventDrivenSource;
use \ALI\Translator\Source\Sources\MySqlSource\MySqlSource;
use \ALI\Translator\Event\EnumList\EventType;

$dispatcher = new EventDispatcher();

$dispatcher->addListener(EventType::EVENT_SAVE_ORIGINAL_AFTER, function ($event) {
    // ...
});

$dispatcher->addListener(EventType::EVENT_DELETE_ORIGINAL_BEFORE, function ($event) {
    // ...
});

$innerWriter = new MySqlSource(/* ... */);
$eventWriter = new EventDrivenSource($innerWriter, $dispatcher);

$eventWriter->saveTranslate('ua', 'Hello', 'Привіт');
$eventWriter->delete('Hello');
```
