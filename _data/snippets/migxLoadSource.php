id: 100071
name: migxLoadSource
description: 'When parsing input options with @CHUNK, it seems impossible to read existing values from other fields. This hook fetches them and adds them to the request, so they can be accessed with POST.'
category: E6_dat_load
properties: 'a:0:{}'

-----

/**
 * migxLoadSource
 *
 * When parsing options inside a selector with @CHUNK, it seems impossible to
 * read existing values from other fields. This hook fetches the existing class
 * key and adds it to the request, making it available for templating.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

// Forward class key, for use in TV templating
$classKey = $scriptProperties['record']['class_key'] ?? '';
$_POST['object_class_key'] = $classKey;

return '';