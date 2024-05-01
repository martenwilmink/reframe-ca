id: 100075
name: migxSavePerson
category: E6_dat_save
properties: 'a:0:{}'

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
$earthbrain = $modx->getService('earthbrain','earthbrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));

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