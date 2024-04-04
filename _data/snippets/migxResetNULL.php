id: 139
name: migxResetNULL
description: 'After save hook for MIGXdb. Prevents database fields with default value of NULL from being set to 0 after a save action in MIGX.'
category: f_data
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:37:"romanesco.migxresetnull.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:38:"romanesco.migxresetnull.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * migxResetNULL
 *
 * After save hook for MIGXdb. Prevents database fields with default value of
 * NULL from being set to 0 after a save action in MIGX.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$object = $modx->getOption('object', $scriptProperties, null);
$properties = $modx->getOption('scriptProperties', $scriptProperties, '');
$configs = $modx->getOption('configs', $properties, '');

// Compare values in properties to newly saved object
foreach ($properties as $key => $value) {
    $objectValue = $object->get($key);

    // Reset to NULL if property value is empty and object value is 0
    if ($objectValue === 0 && $value === '') {
        //$modx->log(modX::LOG_LEVEL_ERROR, 'NULL was reset for: ' . $key);
        $object->set($key, NULL);
        $object->save();
    }
}

return true;