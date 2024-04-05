id: 100075
name: migxSavePerson
category: E6_dat_save
snippet: "/**\n * migxSavePerson\n *\n * Aftersave snippet for earthPersonData object.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n\nif (!($earthbrain instanceof EarthBrain)) return;\n\n$earthProcessorProps = ['processors_path' => $earthbrain->config['processorsPath']];\n\n$object = $modx->getOption('object', $scriptProperties);\n$properties = $modx->getOption('scriptProperties', $scriptProperties, []);\n$configs = $modx->getOption('configs', $properties, '');\n$postValues = $modx->getOption('postvalues', $scriptProperties, []);\n$result = [];\n\n$objectID = $properties['object_id'] ?? '';\n$className = 'earthPersonData';\n\nif (!is_object($object) || !$objectID) return;\n\n// Reuse timestamp so all dates match\n$createdOn = time();\n\n// Set default ownership\n$createdBy = (int)$modx->user->get('id');\n\n// Collect data in arrays\n$personData = [\n    'object_id' => $object->get('id'),\n    'person_id' => $properties['person_id'] ?? '',\n    'classname' => $className,\n    'firstname' => $properties['firstname'] ?? '',\n    'middlename' => $properties['middlename'] ?? '',\n    'lastname' => $properties['lastname'] ?? '',\n    'username' => $properties['Person_username'] ?? '',\n    'fullname' => $properties['UserData_fullname'] ?? '',\n    'address' => '',\n    'country' => $properties['UserData_country'] ?? '',\n    'gender' => $properties['UserData_gender'] ?? '',\n    'dob' => $properties['UserData_dob'] ?? '',\n    'email' => $properties['UserData_email'] ?? '',\n    'phone' => $properties['UserData_phone'] ?? '',\n    'mobilephone' => $properties['UserData_mobilephone'] ?? '',\n    'website' => '',\n    'createdon' => $createdOn,\n    'class_key' => $properties['Person_class_key'] ?? '',\n    'active' => $properties['Person_active'] ?? 0,\n    'published' => $properties['published'] ?? 0,\n    'groups' => [\n        [\n            \"usergroup\" => $modx->getOption('earthbrain.usergroup_registered'),\n            \"member\" => 1,\n            \"role\" => 1,\n        ]\n    ]\n];\n$personAddress = [\n    'line_1' => $properties['Address_line_1'] ?? '',\n    'line_2' => $properties['Address_line_2'] ?? '',\n    'line_3' => $properties['Address_line_3'] ?? '',\n    'locality' => $properties['Address_locality'] ?? '',\n    'region' => $properties['Address_region'] ?? '',\n    'country' => $properties['Address_country'] ?? '',\n    'postal_code' => $properties['Address_postal_code'] ?? '',\n    'comments' => $properties['Address_comments'] ?? '',\n    'createdon' => $createdOn,\n    'createdby' => $createdBy,\n    'published' => $properties['Address_published'] ?? 0,\n];\n$personLocation = [\n    'lat' => $properties['Location_lat'] ?? null,\n    'lng' => $properties['Location_lng'] ?? null,\n    'elevation' => $properties['Location_elevation'] ?? null,\n    'radius' => $properties['Location_radius'] ?? 0,\n    'geojson' => $properties['Location_geojson'] ?? null,\n    'createdon' => $createdOn,\n    'createdby' => $createdBy,\n    'published' => $properties['Location_published'] ?? 0,\n];\n\n// Consolidate data in single array for processor\n$personData['personAddress'] = $personAddress;\n$personData['personLocation'] = $personLocation;\n\n// Create or update person\nif ($objectID === 'new') {\n    $response = $modx->runProcessor('data/person/create', $personData, $earthProcessorProps);\n    $person = $response->getObject();\n} else {\n    $personData['id'] = $object->get('person_id');\n    $response = $modx->runProcessor('data/person/update', $personData, $earthProcessorProps);\n}\nif ($response->isError()) {\n    $modx->log(modX::LOG_LEVEL_ERROR, print_r($response->getAllErrors(),1));\n    return json_encode($response->getMessage());\n}\n\n// Reset potentially altered null fields\n$earthbrain->resetNull($object, $properties);\n\nreturn json_encode($result);"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/e6_dat_save/migxsaveperson.snippet.php'

-----


/**
 * migxSavePerson
 *
 * Aftersave snippet for earthPersonData object.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$earthProcessorProps = ['processors_path' => $earthbrain->config['processorsPath']];

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, []);
$configs = $modx->getOption('configs', $properties, '');
$postValues = $modx->getOption('postvalues', $scriptProperties, []);
$result = [];

$objectID = $properties['object_id'] ?? '';
$className = 'earthPersonData';

if (!is_object($object) || !$objectID) return;

// Reuse timestamp so all dates match
$createdOn = time();

// Set default ownership
$createdBy = (int)$modx->user->get('id');

// Collect data in arrays
$personData = [
    'object_id' => $object->get('id'),
    'person_id' => $properties['person_id'] ?? '',
    'classname' => $className,
    'firstname' => $properties['firstname'] ?? '',
    'middlename' => $properties['middlename'] ?? '',
    'lastname' => $properties['lastname'] ?? '',
    'username' => $properties['Person_username'] ?? '',
    'fullname' => $properties['UserData_fullname'] ?? '',
    'address' => '',
    'country' => $properties['UserData_country'] ?? '',
    'gender' => $properties['UserData_gender'] ?? '',
    'dob' => $properties['UserData_dob'] ?? '',
    'email' => $properties['UserData_email'] ?? '',
    'phone' => $properties['UserData_phone'] ?? '',
    'mobilephone' => $properties['UserData_mobilephone'] ?? '',
    'website' => '',
    'createdon' => $createdOn,
    'class_key' => $properties['Person_class_key'] ?? '',
    'active' => $properties['Person_active'] ?? 0,
    'published' => $properties['published'] ?? 0,
    'groups' => [
        [
            "usergroup" => $modx->getOption('earthbrain.usergroup_registered'),
            "member" => 1,
            "role" => 1,
        ]
    ]
];
$personAddress = [
    'line_1' => $properties['Address_line_1'] ?? '',
    'line_2' => $properties['Address_line_2'] ?? '',
    'line_3' => $properties['Address_line_3'] ?? '',
    'locality' => $properties['Address_locality'] ?? '',
    'region' => $properties['Address_region'] ?? '',
    'country' => $properties['Address_country'] ?? '',
    'postal_code' => $properties['Address_postal_code'] ?? '',
    'comments' => $properties['Address_comments'] ?? '',
    'createdon' => $createdOn,
    'createdby' => $createdBy,
    'published' => $properties['Address_published'] ?? 0,
];
$personLocation = [
    'lat' => $properties['Location_lat'] ?? null,
    'lng' => $properties['Location_lng'] ?? null,
    'elevation' => $properties['Location_elevation'] ?? null,
    'radius' => $properties['Location_radius'] ?? 0,
    'geojson' => $properties['Location_geojson'] ?? null,
    'createdon' => $createdOn,
    'createdby' => $createdBy,
    'published' => $properties['Location_published'] ?? 0,
];

// Consolidate data in single array for processor
$personData['personAddress'] = $personAddress;
$personData['personLocation'] = $personLocation;

// Create or update person
if ($objectID === 'new') {
    $response = $modx->runProcessor('data/person/create', $personData, $earthProcessorProps);
    $person = $response->getObject();
} else {
    $personData['id'] = $object->get('person_id');
    $response = $modx->runProcessor('data/person/update', $personData, $earthProcessorProps);
}
if ($response->isError()) {
    $modx->log(modX::LOG_LEVEL_ERROR, print_r($response->getAllErrors(),1));
    return json_encode($response->getMessage());
}

// Reset potentially altered null fields
$earthbrain->resetNull($object, $properties);

return json_encode($result);