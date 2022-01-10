```php
use Panvid\UnitConverter\Convert;
use Panvid\UnitConverter\Units;

$convertion = new Convert(1, 9, Units::LENGTH);
$result = $convertion->getResult();
$convertion->debug();
```