id: 100068
name: earthObjectMediaPath
category: E6_plumbing
snippet: "/**\n * earthObjectMediaPath\n *\n * Based on migxObjectMediaPath. But in addition to using the object ID in image\n * paths, you can also grab a field value of choice to fill the placeholder.\n *\n * Usage example:\n * [[earthObjectMediaPath? &pathTpl=`uploads/img/forest/{fieldValue}/` &className=`forestData` &fieldName=`resource_id`]]\n *\n * As of August 2022, you can also use {category} and {parentId} placeholders in\n * the image path. This was added to accommodate extended images, which need a\n * little detour to find their parent objects.\n *\n * Usage example:\n * [[earthObjectMediaPath? &pathTpl=`uploads/img/{category}/{parentId}/`]]\n *\n * If symCategory is defined (in switch case below), a symlink will be placed\n * inside this category folder, under the same parent ID. This way, all images\n * belonging to the symlink parent (i.e. a forest resource) are accessible from\n * their media source root, while the images themselves remain stored in their\n * respective category folders. This makes it easier to retrieve them in grids\n * and snippets.\n * \n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$pathTpl = $modx->getOption('pathTpl', $scriptProperties, '');\n$objectID = $modx->getOption('objectID', $scriptProperties, '');\n$className = $modx->getOption('className'); // Direct entry only!\n$fieldName = $modx->getOption('fieldName', $scriptProperties, '');\n$createFolder = $modx->getOption('createFolder', $scriptProperties, 0);\n$createPath = true;\n$createSymlink = false;\n$path = '';\n$symlinkPath = '';\n\n// Check if placeholder was set by some script\nif (empty($objectID) && $modx->getPlaceholder('objectid')) {\n    $objectID = $modx->getPlaceholder('objectid');\n}\nif (empty($objectID) && isset($_REQUEST['object_id'])) {\n    $objectID = $_REQUEST['object_id'];\n}\n\n// Check if session var was set in fields.php processor\nif (empty($objectID) && isset($_SESSION['migxWorkingObjectid'])) {\n    $objectID = $_SESSION['migxWorkingObjectid'];\n    $createPath = !empty($createFolder);\n}\n\n// By default, fill {fieldValue} placeholder with object ID\n$fieldValue = $objectID;\n\n// Prepare {category} and {parentId} placeholders\n$parentID = $_REQUEST['co_id'] ?? null;\n$parentConfig = $_REQUEST['reqConfigs'] ?? null;\n$category = '';\n\n// Also prepare special conditions\n$plantID = '';\n$symCategory = '';\n$symParentID = '';\n$storedParentID = '';\n\n// Check storeParams if fields cannot be fetched from request directly\n$storeParams = $modx->getOption('storeParams', $_REQUEST);\nif ($storeParams) {\n    $storeParams = json_decode($storeParams, true);\n\n    if (!$parentConfig) {\n        $parentConfig = $storeParams['reqConfigs'];\n    }\n    if (!$parentID) {\n        $parentID = $storeParams['object_id'];\n    }\n}\n\n// Set category variables for each config\nswitch ($parentConfig) {\n    case 'earthbrain_seeds:earthbrain':\n        $category = 'seed';\n        break;\n    case 'earthbrain_plants:earthbrain':\n        $category = 'plant';\n        break;\n    case 'earthbrain_plantings:earthbrain':\n        $category = 'plant';\n\n        // Define classname to share image path with plant parent\n        $className = 'earthPlanting';\n        $fieldName = 'plant_id';\n        break;\n    case 'earthbrain_sources:earthbrain':\n        $category = 'source';\n        break;\n    case 'earthbrain_exchanges:earthbrain':\n        $category = 'exchange';\n        break;\n\n    // Some foreigners, for convenience\n    case 'forestbrain_plants:forestbrain':\n        $category = 'plant';\n\n        // Define secondary category folder so symlinks can be created there\n        $symCategory = 'forest';\n        $className = 'forestPlant';\n        $fieldName = 'parent_id';\n        break;\n    case 'forestbrain_features:forestbrain':\n        $category = 'feature';\n        $symCategory = 'forest';\n        $className = 'forestFeature';\n        $fieldName = 'forest_id';\n        break;\n    case 'forestbrain_components:forestbrain':\n        $category = 'component';\n        $symCategory = 'forest';\n        $className = 'forestComponent';\n        $fieldName = 'forest_id';\n        break;\n    case 'forestbrain_writings:forestbrain':\n        $category = 'writing';\n        $symCategory = 'forest';\n        $className = 'forestWriting';\n        $fieldName = 'forest_id';\n        break;\n    case 'releafbrain_needs:releafbrain':\n        $category = 'need';\n        break;\n}\n\n// Store / get session fields, in case parent connexion is lost\nif ($category) {\n    $_SESSION['migxWorkingObject']['category'] = $category;\n\n    // If applicable, get symCategory parent ID\n    if ($className && $symCategory) {\n        $query = $modx->newQuery($className, [\n            'id' => $parentID,\n        ]);\n        $query->select($fieldName);\n        $symParentID = $modx->getValue($query->prepare());\n        $className = ''; // unset to prevent triggering additional query below\n\n        $_SESSION['migxWorkingObject']['symCategory'] = $symCategory;\n        $_SESSION['migxWorkingObject']['symParentID'] = $symParentID;\n    } else {\n        $_SESSION['migxWorkingObject']['symCategory'] = '';\n        $_SESSION['migxWorkingObject']['symParentID'] = '';\n    }\n\n    // Match plantings to plant ID (instead of object ID)\n    if ($className == 'earthPlanting') {\n        $query = $modx->newQuery('earthPlanting', [\n            'id' => $parentID,\n        ]);\n        $query->select($fieldName);\n        $plantID = $modx->getValue($query->prepare());\n        $className = ''; // unset to prevent triggering additional query below\n\n        $_SESSION['migxWorkingObject']['plantID'] = $plantID;\n    } else {\n        $_SESSION['migxWorkingObject']['plantID'] = '';\n    }\n} else {\n    $category = $_SESSION['migxWorkingObject']['category'] ?? '';\n    $plantID = $_SESSION['migxWorkingObject']['plantID'] ?? '';\n    $symCategory = $_SESSION['migxWorkingObject']['symCategory'] ?? '';\n    $symParentID = $_SESSION['migxWorkingObject']['symParentID'] ?? '';\n}\n\nif ($parentID) {\n    $_SESSION['migxWorkingObject']['parentID'] = $parentID;\n} else {\n    $parentID = $_SESSION['migxWorkingObject']['parentID'] ?? '';\n}\n\n// Let plantings hijack the parent ID, so images end up in the same folder\nif ($plantID) {\n    $storedParentID = $parentID;\n    $parentID = $plantID;\n}\n\n// If class name is specified, look for alternative field value\nif ($className) {\n    $query = $modx->newQuery($className, [\n        'id' => $objectID,\n    ]);\n    $query->select($fieldName);\n    $fieldValue = $modx->getValue($query->prepare());\n}\n\n//$modx->log(modX::LOG_LEVEL_ERROR, sprintf('[earthObjectMediaPath] Field value: %s', $fieldValue));\n//$modx->log(modX::LOG_LEVEL_ERROR, sprintf('[earthObjectMediaPath] Parent ID: %s', $parentID));\n//$modx->log(modX::LOG_LEVEL_ERROR, print_r($_SESSION['migxWorkingObject'],1));\n//$modx->log(modX::LOG_LEVEL_ERROR, $_REQUEST['reqConfigs'] ?? '');\n//$modx->log(modX::LOG_LEVEL_ERROR, $objectID);\n\n// Create path\n$path = str_replace('{fieldValue}', $fieldValue, $pathTpl);\n$path = str_replace('{category}', $category, $path);\n$path = str_replace('{parentId}', $parentID, $path);\n$path = str_replace('{id}', $objectID, $path);\n$path = str_replace('//', '/', $path);\n$fullPath = $modx->getOption('base_path') . $path;\n\n// Create symlink path\n$symPath = \"/$symCategory/$symParentID/$category/\";\nif ($symCategory && $symParentID) {\n    $symlinkPath = str_replace('{fieldValue}', $fieldValue, $pathTpl);\n    $symlinkPath = str_replace('{category}', $symPath, $symlinkPath);\n    $symlinkPath = str_replace('{parentId}', '', $symlinkPath);\n    $symlinkPath = str_replace('//', '/', $symlinkPath);\n    $symlinkPath = str_replace('//', '/', $symlinkPath); // hij gaat voor de tripel\n    $symlinkPath = $modx->getOption('base_path') . $symlinkPath;\n    $createSymlink = true;\n}\n\n// Avoid creating folders for objects without ID\nif (str_contains($fullPath, '/new/')) {\n    $createPath = false;\n    $createSymlink = false;\n}\n\n//$modx->log(modX::LOG_LEVEL_ERROR, $symlinkPath);\n//$modx->log(modX::LOG_LEVEL_ERROR, $symPath);\n//$modx->log(modX::LOG_LEVEL_ERROR, $createSymlink);\n\n// Avoid spawning folders in unrelated locations\nif (!str_contains($path, \"/$category/$parentID/\") || $plantID) {\n    $createPath = false;\n}\nif (!str_contains($symlinkPath, $symPath)) {\n    $createSymlink = false;\n}\n\n// Set folder permissions\n$permissions = octdec('0' . (int)($modx->getOption('new_folder_permissions', null, '755', true)));\n\n// Write file path\nif ($createPath && !file_exists($fullPath)) {\n    if (!@mkdir($fullPath, $permissions, true)) {\n        $modx->log(modX::LOG_LEVEL_ERROR, sprintf('[earthObjectMediaPath]: could not create directory %s', $fullPath));\n    }\n    else {\n        chmod($fullPath, $permissions);\n    }\n}\n\n// Write symlink\nif ($createSymlink && !file_exists($symlinkPath . $parentID)) {\n    if (@mkdir($symlinkPath, $permissions, true)) {\n        chmod($symlinkPath, $permissions);\n    }\n    symlink(\"../../../$category/$parentID\", $symlinkPath . $parentID);\n}\n\nreturn $path;"
properties: 'a:0:{}'

-----


/**
 * earthObjectMediaPath
 *
 * Based on migxObjectMediaPath. But in addition to using the object ID in image
 * paths, you can also grab a field value of choice to fill the placeholder.
 *
 * Usage example:
 * [[earthObjectMediaPath? &pathTpl=`uploads/img/forest/{fieldValue}/` &className=`forestData` &fieldName=`resource_id`]]
 *
 * As of August 2022, you can also use {category} and {parentId} placeholders in
 * the image path. This was added to accommodate extended images, which need a
 * little detour to find their parent objects.
 *
 * Usage example:
 * [[earthObjectMediaPath? &pathTpl=`uploads/img/{category}/{parentId}/`]]
 *
 * If symCategory is defined (in switch case below), a symlink will be placed
 * inside this category folder, under the same parent ID. This way, all images
 * belonging to the symlink parent (i.e. a forest resource) are accessible from
 * their media source root, while the images themselves remain stored in their
 * respective category folders. This makes it easier to retrieve them in grids
 * and snippets.
 * 
 * @var modX $modx
 * @var array $scriptProperties
 */

$pathTpl = $modx->getOption('pathTpl', $scriptProperties, '');
$objectID = $modx->getOption('objectID', $scriptProperties, '');
$className = $modx->getOption('className'); // Direct entry only!
$fieldName = $modx->getOption('fieldName', $scriptProperties, '');
$createFolder = $modx->getOption('createFolder', $scriptProperties, 0);
$createPath = true;
$createSymlink = false;
$path = '';
$symlinkPath = '';

// Check if placeholder was set by some script
if (empty($objectID) && $modx->getPlaceholder('objectid')) {
    $objectID = $modx->getPlaceholder('objectid');
}
if (empty($objectID) && isset($_REQUEST['object_id'])) {
    $objectID = $_REQUEST['object_id'];
}

// Check if session var was set in fields.php processor
if (empty($objectID) && isset($_SESSION['migxWorkingObjectid'])) {
    $objectID = $_SESSION['migxWorkingObjectid'];
    $createPath = !empty($createFolder);
}

// By default, fill {fieldValue} placeholder with object ID
$fieldValue = $objectID;

// Prepare {category} and {parentId} placeholders
$parentID = $_REQUEST['co_id'] ?? null;
$parentConfig = $_REQUEST['reqConfigs'] ?? null;
$category = '';

// Also prepare special conditions
$plantID = '';
$symCategory = '';
$symParentID = '';
$storedParentID = '';

// Check storeParams if fields cannot be fetched from request directly
$storeParams = $modx->getOption('storeParams', $_REQUEST);
if ($storeParams) {
    $storeParams = json_decode($storeParams, true);

    if (!$parentConfig) {
        $parentConfig = $storeParams['reqConfigs'];
    }
    if (!$parentID) {
        $parentID = $storeParams['object_id'];
    }
}

// Set category variables for each config
switch ($parentConfig) {
    case 'earthbrain_seeds:earthbrain':
        $category = 'seed';
        break;
    case 'earthbrain_plants:earthbrain':
        $category = 'plant';
        break;
    case 'earthbrain_plantings:earthbrain':
        $category = 'plant';

        // Define classname to share image path with plant parent
        $className = 'earthPlanting';
        $fieldName = 'plant_id';
        break;
    case 'earthbrain_sources:earthbrain':
        $category = 'source';
        break;
    case 'earthbrain_exchanges:earthbrain':
        $category = 'exchange';
        break;

    // Some foreigners, for convenience
    case 'forestbrain_plants:forestbrain':
        $category = 'plant';

        // Define secondary category folder so symlinks can be created there
        $symCategory = 'forest';
        $className = 'forestPlant';
        $fieldName = 'parent_id';
        break;
    case 'forestbrain_features:forestbrain':
        $category = 'feature';
        $symCategory = 'forest';
        $className = 'forestFeature';
        $fieldName = 'forest_id';
        break;
    case 'forestbrain_components:forestbrain':
        $category = 'component';
        $symCategory = 'forest';
        $className = 'forestComponent';
        $fieldName = 'forest_id';
        break;
    case 'forestbrain_writings:forestbrain':
        $category = 'writing';
        $symCategory = 'forest';
        $className = 'forestWriting';
        $fieldName = 'forest_id';
        break;
    case 'releafbrain_needs:releafbrain':
        $category = 'need';
        break;
}

// Store / get session fields, in case parent connexion is lost
if ($category) {
    $_SESSION['migxWorkingObject']['category'] = $category;

    // If applicable, get symCategory parent ID
    if ($className && $symCategory) {
        $query = $modx->newQuery($className, [
            'id' => $parentID,
        ]);
        $query->select($fieldName);
        $symParentID = $modx->getValue($query->prepare());
        $className = ''; // unset to prevent triggering additional query below

        $_SESSION['migxWorkingObject']['symCategory'] = $symCategory;
        $_SESSION['migxWorkingObject']['symParentID'] = $symParentID;
    } else {
        $_SESSION['migxWorkingObject']['symCategory'] = '';
        $_SESSION['migxWorkingObject']['symParentID'] = '';
    }

    // Match plantings to plant ID (instead of object ID)
    if ($className == 'earthPlanting') {
        $query = $modx->newQuery('earthPlanting', [
            'id' => $parentID,
        ]);
        $query->select($fieldName);
        $plantID = $modx->getValue($query->prepare());
        $className = ''; // unset to prevent triggering additional query below

        $_SESSION['migxWorkingObject']['plantID'] = $plantID;
    } else {
        $_SESSION['migxWorkingObject']['plantID'] = '';
    }
} else {
    $category = $_SESSION['migxWorkingObject']['category'] ?? '';
    $plantID = $_SESSION['migxWorkingObject']['plantID'] ?? '';
    $symCategory = $_SESSION['migxWorkingObject']['symCategory'] ?? '';
    $symParentID = $_SESSION['migxWorkingObject']['symParentID'] ?? '';
}

if ($parentID) {
    $_SESSION['migxWorkingObject']['parentID'] = $parentID;
} else {
    $parentID = $_SESSION['migxWorkingObject']['parentID'] ?? '';
}

// Let plantings hijack the parent ID, so images end up in the same folder
if ($plantID) {
    $storedParentID = $parentID;
    $parentID = $plantID;
}

// If class name is specified, look for alternative field value
if ($className) {
    $query = $modx->newQuery($className, [
        'id' => $objectID,
    ]);
    $query->select($fieldName);
    $fieldValue = $modx->getValue($query->prepare());
}

//$modx->log(modX::LOG_LEVEL_ERROR, sprintf('[earthObjectMediaPath] Field value: %s', $fieldValue));
//$modx->log(modX::LOG_LEVEL_ERROR, sprintf('[earthObjectMediaPath] Parent ID: %s', $parentID));
//$modx->log(modX::LOG_LEVEL_ERROR, print_r($_SESSION['migxWorkingObject'],1));
//$modx->log(modX::LOG_LEVEL_ERROR, $_REQUEST['reqConfigs'] ?? '');
//$modx->log(modX::LOG_LEVEL_ERROR, $objectID);

// Create path
$path = str_replace('{fieldValue}', $fieldValue, $pathTpl);
$path = str_replace('{category}', $category, $path);
$path = str_replace('{parentId}', $parentID, $path);
$path = str_replace('{id}', $objectID, $path);
$path = str_replace('//', '/', $path);
$fullPath = $modx->getOption('base_path') . $path;

// Create symlink path
$symPath = "/$symCategory/$symParentID/$category/";
if ($symCategory && $symParentID) {
    $symlinkPath = str_replace('{fieldValue}', $fieldValue, $pathTpl);
    $symlinkPath = str_replace('{category}', $symPath, $symlinkPath);
    $symlinkPath = str_replace('{parentId}', '', $symlinkPath);
    $symlinkPath = str_replace('//', '/', $symlinkPath);
    $symlinkPath = str_replace('//', '/', $symlinkPath); // hij gaat voor de tripel
    $symlinkPath = $modx->getOption('base_path') . $symlinkPath;
    $createSymlink = true;
}

// Avoid creating folders for objects without ID
if (str_contains($fullPath, '/new/')) {
    $createPath = false;
    $createSymlink = false;
}

//$modx->log(modX::LOG_LEVEL_ERROR, $symlinkPath);
//$modx->log(modX::LOG_LEVEL_ERROR, $symPath);
//$modx->log(modX::LOG_LEVEL_ERROR, $createSymlink);

// Avoid spawning folders in unrelated locations
if (!str_contains($path, "/$category/$parentID/") || $plantID) {
    $createPath = false;
}
if (!str_contains($symlinkPath, $symPath)) {
    $createSymlink = false;
}

// Set folder permissions
$permissions = octdec('0' . (int)($modx->getOption('new_folder_permissions', null, '755', true)));

// Write file path
if ($createPath && !file_exists($fullPath)) {
    if (!@mkdir($fullPath, $permissions, true)) {
        $modx->log(modX::LOG_LEVEL_ERROR, sprintf('[earthObjectMediaPath]: could not create directory %s', $fullPath));
    }
    else {
        chmod($fullPath, $permissions);
    }
}

// Write symlink
if ($createSymlink && !file_exists($symlinkPath . $parentID)) {
    if (@mkdir($symlinkPath, $permissions, true)) {
        chmod($symlinkPath, $permissions);
    }
    symlink("../../../$category/$parentID", $symlinkPath . $parentID);
}

return $path;