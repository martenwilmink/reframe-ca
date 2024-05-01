id: 100073
name: migxLoadExchange
description: 'This adds the class_key to the request, so it can be accessed with POST.'
category: E6_dat_load
properties: 'a:0:{}'

-----

/**
 * migxLoadExchange
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
if ($classKey) {
    $_POST['object_class_key'] = $classKey;
}

return '';