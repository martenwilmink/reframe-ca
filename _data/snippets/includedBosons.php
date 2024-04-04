id: 111
name: includedBosons
category: f_hub
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:38:"romanesco.includedbosons.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:39:"romanesco.includedbosons.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * includedBosons
 *
 * List all ContentBlocks fields being used in a given layout, or if no layout
 * is specified, on a given page.
 *
 * @author Hugo Peek
 */

$cbCorePath = $modx->getOption('contentblocks.core_path', null, $modx->getOption('core_path').'components/contentblocks/');
$ContentBlocks = $modx->getService('contentblocks','ContentBlocks', $cbCorePath.'model/contentblocks/');

$corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
$romanesco = $modx->getService('romanesco','Romanesco',$corePath . 'model/romanescobackyard/',array('core_path' => $corePath));

if (!($romanesco instanceof Romanesco)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[Romanesco] Class not found!');
    return;
}

$resourceID = $modx->getOption('resource', $scriptProperties, $modx->resource->get('id'));
$layoutIdx = $modx->getOption('layout', $scriptProperties, '');
$filterFields = $modx->getOption('filterFields', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, 'includedContentBlocksRow');

$htmlContentType = $modx->getObject('modContentType', array('name' => 'HTML'));

// Function to turn result into a link to its corresponding resource
if (!function_exists('createLink')) {
    function createLink($catID, $uriExtension) {
        global $modx;

        // Since we have an ID, let's go hunt for the category name
        $category = $modx->getObject('cbCategory', array(
            'id' => $catID
        ));

        $catName = '';
        if ($category) {
            $catName = strtolower($category->get('name'));
        } else {
            $modx->log(modX::LOG_LEVEL_WARN, '[includedBosons] Link could not be generated due to missing category ID');
        }

        // Use bosons as parent name, because we don't know if this is a layout or field
        $parentName = 'bosons';

        // Get the resource with an alias that matches both category and parent name
        $query = $modx->newQuery('modResource');
        $query->where(array(
            'uri:LIKE' => '%' . $parentName . '%',
            'AND:uri:LIKE' => '%' . $catName . $uriExtension
        ));
        $query->select('uri');

        return $modx->getValue($query->prepare());
    }
}

// Get the properties of the current resource first
$query = $modx->newQuery('modResource', array(
    'id' => $resourceID
));
$query->select('properties');
$properties = $modx->getValue($query->prepare());

// Prepare an array with just the content part
$propertiesArray = json_decode($properties, true);
$propertiesArray = json_decode($propertiesArray['contentblocks']['content'], true);

// If a layout idx is set, pick the corresponding layout from the array
if ($layoutIdx != '') {
    $result = $propertiesArray[$layoutIdx];
} else {
    $result = $propertiesArray; // And if not, just get all the fields
}

// Great! Now let's retrieve all field IDs from the array
if (is_array($result)) {
    $result = $romanesco->recursiveArraySearch($result, 'field');
    $result = array_unique($result);
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, '[includedBosons] Result is not a valid array. Is the layout idx correct?');
    return '';
}

// User specified CB fields need to be excluded from result
$arrayFilter = explode(',', $filterFields);

// Turn each match into a list item with a link
$boson = '';
$output = [];
foreach ($result as $id) {
    if (!in_array($id, $arrayFilter)) {
        $boson = $modx->getObject('cbField', array(
            'id' => $id
        ));
    }
    if ($boson) {
        $name = $boson->get('name');
        $link = createLink($boson->get('category'), $htmlContentType->get('file_extensions'));

        $output[] = $modx->getChunk($tpl, array(
            'name' => $name,
            'link' => $link,
            'label_classes' => ''
        ));
    }
}

return implode(array_unique($output));