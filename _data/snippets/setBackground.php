id: 142
name: setBackground
description: 'Output the necessary class names for the applied Global Background.'
category: f_framework
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:37:"romanesco.setbackground.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:38:"romanesco.setbackground.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * setBackground
 *
 * Set the appropriate classes for an applied Global Background.
 * For use in CB layout templates.
 *
 * Backwards compatible with the old, classname-based approach.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 */

$background = $modx->getOption('background', $scriptProperties, $input ?? null);
$creditsTpl = $modx->getOption('creditsTpl', $scriptProperties, 'globalBackgroundImgCredits');
$cbField = $modx->getOption('romanesco.cb_field_background_id', $scriptProperties, '');

// Convert system default value
if ($background == 'default') {
    $background = $modx->getObject('cgSetting', array('key' => 'layout_background_default'))->get('value');
}

// Numeric value means it's a resource-based global background
if (is_numeric($background)) {
    $query = $modx->newQuery('modResource', $background);
    $query->select('alias');
    $alias = $modx->getValue($query->prepare());

    // Get settings from CB field
    $bgSettings = $modx->runSnippet('cbGetFieldContent',array(
        'resource' => $background,
        'field' => $cbField,
        'returnAsJSON' => 1,
    ));
    $bgSettings = json_decode($bgSettings, 1);

    // Get inverted setting
    $inverted = $bgSettings[0]['settings']['inverted'] ? ' inverted' : '';

    // Process background layers
    $credits = '';
    if (is_array($bgSettings[0]['rows'])) {
        foreach ($bgSettings[0]['rows'] as $settings) {
            if (isset($settings['credits']['value'])) {
                $credits .= $settings['credits']['value'];
            }
        }
    }

    // Set placeholder with credits
    if ($credits) {
        $tpl = $modx->getChunk($creditsTpl, [
            'classes' => $inverted,
            'credits' => $credits
        ]);
        $modx->setPlaceholder('bg' . $background . '.credits', $tpl);
    }

    $background = $alias . $inverted . ' background';
}

return $background;