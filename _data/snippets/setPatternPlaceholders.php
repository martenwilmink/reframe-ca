id: 109
name: setPatternPlaceholders
category: f_hub
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:46:"romanesco.setpatternplaceholders.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:47:"romanesco.setpatternplaceholders.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


$cbCorePath = $modx->getOption('contentblocks.core_path', null, $modx->getOption('core_path').'components/contentblocks/');
$ContentBlocks = $modx->getService('contentblocks','ContentBlocks', $cbCorePath.'model/contentblocks/');
//$ContentBlocks->loadInputs();

$cbField = $modx->getOption('cbField', $scriptProperties, '');
$cbLayout = $modx->getOption('cbLayout', $scriptProperties, '');
$prefix = $modx->getOption('prefix', $scriptProperties, '');

if ($cbField) {
    $field = $modx->getObject('cbField', array(
        'name' => $cbField
    ));

    if ($field) {
        // Create an array with all internal fields
        $array = $field->toArray();

        // Set all fields as placeholders
        // Use a prefix to prevent collisions
        $modx->toPlaceholders($array, $prefix);

        // Set placeholder with all field settings parsed in an HTML table
        //$settingsTable = $modx->runSnippet('jsonToHTML', array(
        //    'json' => $field->get('settings')
        //));
        //$modx->toPlaceholder('settings_table', $settingsTable, $prefix);

        // Above option doesn't work somehow, so just output raw json to placeholder
        $modx->toPlaceholder('settings_json', $field->get('settings'), $prefix);
        $modx->toPlaceholder('properties_json', $field->get('properties'), $prefix);
        $modx->toPlaceholder('availability_json', $field->get('availability'), $prefix);

        // Set placeholder with wrapper template, if present inside properties field
        $properties = json_decode($field->get('properties'), true);
        $wrapperTemplate = $properties['wrapper_template'] ?? '';
        if ($wrapperTemplate) {
            $output = $modx->getChunk('displayRawTemplate', array(
                'template' => $wrapperTemplate,
            ));
            $modx->toPlaceholder('wrapper_template', $output, $prefix);
        }

        // Set separate placeholder with prefix, for easier retrieval of the other placeholders
        // Usage example: [[+[[+cb]].placeholder]]
        $modx->toPlaceholder('cf', $prefix);
    }
    else {
        $modx->log(modX::LOG_LEVEL_WARN, '[setPatternPlaceholders] ' . $cbField . ' could not be processed');
    }
}

if ($cbLayout) {
    $layout = $modx->getObject('cbLayout', array(
        'name' => $cbLayout
    ));

    if ($layout) {
        // Create an array with all internal fields
        $array = $layout->toArray();

        // Set all fields as placeholders
        // Use a prefix to prevent collisions
        $modx->toPlaceholders($array, $prefix);

        // Set placeholder with raw json output from the settings column
        $modx->toPlaceholder('settings_json', $layout->get('settings'), $prefix);

        // Set separate placeholder with prefix, for easier retrieval of the other placeholders
        // Usage example: [[+[[+cl]].placeholder]]
        $modx->toPlaceholder('cl', $prefix);
    }
    else {
        $modx->log(modX::LOG_LEVEL_WARN, '[setPatternPlaceholders] ' . $cbLayout . ' could not be processed');
    }
}

return '';