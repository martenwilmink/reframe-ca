id: 144
name: fbFormReport
description: 'Generates a report from submitted field values. Primarily for emails, but you can also use this snippet to template other kinds of functionality (confirmation pages, multi page forms..).'
category: f_formblocks
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:36:"romanesco.fbformreport.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:37:"romanesco.fbformreport.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * fbFormReport
 *
 * Generates a report from submitted field values. Primarily used in email
 * responders of course, but you can also use this snippet to template other
 * kinds of functionality (confirmation pages, multi-page forms..).
 *
 * @author Jsewill
 * @version 1.0
 *
 * &tplPrefix: Template chunk name prefix.
 * &formID: Resource ID of the form. Can be a comma-separated list also, for
 *  processing multi-page forms.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$formID = $modx->getOption('formID', $scriptProperties, '');
$tplPrefix = $modx->getOption('tplPrefix', $scriptProperties, 'fbEmailRow_');
$tplSectionHeader = $modx->getOption('tplSectionHeader', $scriptProperties, '');
$tplStepCompleted = $modx->getOption('tplStepCompleted', $scriptProperties, 'fbStoreRowStep');
$allSteps = $modx->getOption('allSteps', $scriptProperties, '');
$allForms = $modx->getOption('allForms', $scriptProperties, '');
$reqOnly = $modx->getOption('requiredOnly', $scriptProperties, '');
$outputReverse = $modx->getOption('outputReverse', $scriptProperties, 0);

if (!function_exists('getFields')) {
    function getFields(&$modx, $data, $prefix, $id, $uid, $reqOnly): array
    {
        $result = [];
        $idx = 0;

        foreach($data as $value) {
            if (!is_array($value)) {
                continue;
            }

            // Capture all fields, except for nested fieldsets (which contain fields themselves)
            if (isset($value['field']) && $value['field'] != $modx->getOption('formblocks.cb_nested_fieldset_id')) {
                $idx++;
                $value['settings']['id'] = $id;
                $value['settings']['uid'] = $uid . '_' . $idx;

                // Only return required fields if specified
                if ($reqOnly) {

                    // Some fields are always required
                    switch ($value['field']) {
                        case $modx->getOption('formblocks.cb_input_email_id'):
                            $value['settings']['field_required'] = 1;
                            $value['settings']['field_type'] = 'email';
                            break;
                        case $modx->getOption('formblocks.cb_accept_terms_id'):
                            $value['settings']['field_required'] = 1;
                            $value['settings']['field_type'] = 'terms';
                            break;
                        case $modx->getOption('formblocks.cb_math_question_id'):
                            // Almost always...
                            if (!$modx->getOption('formblocks.ajax_mode')) {
                                $value['settings']['field_required'] = 1;
                                $value['settings']['field_type'] = 'math';
                            }
                            break;
                    }

                    $required = $value['settings']['field_required'] ?? 0;
                    if ($required != 1) {
                        continue;
                    }
                }

                $result[] = $modx->getChunk($prefix.$value['field'], $value['settings']);
                continue;
            }

            // This iterates over nested fields until a field is found
            // Each iteration receives a new parent (layout) idx
            $result[] = getFields($modx, $value, $prefix, $id, $uid++, $reqOnly);
        }

        return $result;
    }
}

if (!$formID) return '';
$forms = explode(',',$formID);
$output = [];

// Match form IDs to resource IDs
$allFormSteps = [];
if ($allSteps && $allForms) {
    $allSteps = explode(',',$allSteps);
    $allForms = explode(',',$allForms);
    $allFormSteps = array_combine(array_filter($allForms), array_filter($allSteps));
}

// Reverse output to display multistep forms in consecutive order
if ($outputReverse) {
    $forms = array_reverse($forms);
}

// Set UID to help with caching
// UID format will end up as formID_layoutID_idx
$uid = $formID . '_0';

foreach ($forms as $formID) {
    $resource = $modx->getObject('modResource', $formID);
    $cbData = json_decode($resource->getProperty('content', 'contentblocks'), true);
    $result = [];
    $uid++;

    // Only add header if there are multiple forms and a tpl chunk present
    if (isset($forms[1]) && $tplSectionHeader) {
        $title = $resource->get('menutitle') ? $resource->get('menutitle') : $resource->get('pagetitle');
        $result[] = $modx->getChunk($tplSectionHeader, ['title' => $title]);
    }

    // Add hidden field to indicate this step is completed
    if ($allFormSteps) {
        $result[] = $modx->getChunk($tplStepCompleted, ['id' => $allFormSteps[$formID]]);
    }

    // Get fields
    $fields = getFields($modx, $cbData, $tplPrefix, $formID, $uid, $reqOnly);

    // Flatten fields array
    $fields = new RecursiveIteratorIterator(new RecursiveArrayIterator($fields));
    foreach($fields as $field) {
        $result[] = $field;
    }

    $output[] = implode($result);
}

return implode($output);