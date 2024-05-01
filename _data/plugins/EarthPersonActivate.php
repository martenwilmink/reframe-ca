id: 100024
name: EarthPersonActivate
category: E7_person
plugincode: "/**\n * EarthPersonActivate plugin\n *\n * Fetch newly created user and set extended information.\n *\n * NB! Keep in mind that the OnUserActivate event fires on reactivation too.\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\n// Get first array key in PHP < 7.3\nif (!function_exists('array_key_first')) {\n    /**\n     * @param array $array\n     * @return int|string|null\n     */\n    function array_key_first(array $array)\n    {\n        if (count($array)) {\n            reset($array);\n            return key($array);\n        }\n        return null;\n    }\n}\n\nswitch ($modx->event->name) {\n    case 'OnUserActivate':\n        /** @var modUser $user */\n\n        $corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');\n        $earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));\n        if (!($earthbrain instanceof ForestBrain)) return;\n\n        $profile = $user->getOne('Profile');\n        $extended = $profile->get('extended');\n\n        // This event fires on reactivation too, so only apply changes to non-earthPersons\n        if ($user->get('class_key') == 'modUser') {\n\n            // Set class_key to earthPerson\n            $user->set('class_key', 'earthPerson');\n            $user->save();\n\n            // Derive extended value prefix from first array key\n            $prefix = preg_match(\"/^fb[0-9]+?-/\", array_key_first($extended),$match);\n            $prefix = $match[0];\n\n            // Recreate extended profile without prefixes and remove unwanted properties\n            $extendedNew = [];\n            foreach ($extended as $key => $value) {\n                $key = str_replace($prefix, '', $key);\n                $extendedNew[$key] = $value;\n            }\n            unset($extendedNew['math']);\n            unset($extendedNew['op1']);\n            unset($extendedNew['op2']);\n            unset($extendedNew['operator']);\n            unset($extendedNew['email-alt']);\n\n            // Only set phone number if currently empty, to avoid overwriting it someday with old data\n            $phone = $profile->get('phone');\n            if (!$phone) {\n                $phone = $extendedNew['phone'];\n            }\n\n            // Write values to profile and save\n            $profile->set('fullname', $extendedNew['firstname'] . ' ' . $extendedNew['lastname']);\n            $profile->set('phone', $phone);\n            $profile->set('extended', $extendedNew);\n            $profile->save();\n        }\n\n        // Create earthPersonData object\n        $earthPersonData = $modx->getObject('earthPersonData', ['user_id' => $user->get('id')]);\n        if (!$earthPersonData) {\n            $earthPersonData = $modx->newObject('earthPersonData');\n            $earthPersonData->set('user_id', $user->get('id'));\n        }\n        $earthPersonData->fromArray($profile->get('extended'));\n        $earthPersonData->save();\n\n        break;\n}"
properties: 'a:0:{}'

-----


/**
 * EarthPersonActivate plugin
 *
 * Fetch newly created user and set extended information.
 *
 * NB! Keep in mind that the OnUserActivate event fires on reactivation too.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

// Get first array key in PHP < 7.3
if (!function_exists('array_key_first')) {
    /**
     * @param array $array
     * @return int|string|null
     */
    function array_key_first(array $array)
    {
        if (count($array)) {
            reset($array);
            return key($array);
        }
        return null;
    }
}

switch ($modx->event->name) {
    case 'OnUserActivate':
        /** @var modUser $user */

        $corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
        $earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));
        if (!($earthbrain instanceof ForestBrain)) return;

        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');

        // This event fires on reactivation too, so only apply changes to non-earthPersons
        if ($user->get('class_key') == 'modUser') {

            // Set class_key to earthPerson
            $user->set('class_key', 'earthPerson');
            $user->save();

            // Derive extended value prefix from first array key
            $prefix = preg_match("/^fb[0-9]+?-/", array_key_first($extended),$match);
            $prefix = $match[0];

            // Recreate extended profile without prefixes and remove unwanted properties
            $extendedNew = [];
            foreach ($extended as $key => $value) {
                $key = str_replace($prefix, '', $key);
                $extendedNew[$key] = $value;
            }
            unset($extendedNew['math']);
            unset($extendedNew['op1']);
            unset($extendedNew['op2']);
            unset($extendedNew['operator']);
            unset($extendedNew['email-alt']);

            // Only set phone number if currently empty, to avoid overwriting it someday with old data
            $phone = $profile->get('phone');
            if (!$phone) {
                $phone = $extendedNew['phone'];
            }

            // Write values to profile and save
            $profile->set('fullname', $extendedNew['firstname'] . ' ' . $extendedNew['lastname']);
            $profile->set('phone', $phone);
            $profile->set('extended', $extendedNew);
            $profile->save();
        }

        // Create earthPersonData object
        $earthPersonData = $modx->getObject('earthPersonData', ['user_id' => $user->get('id')]);
        if (!$earthPersonData) {
            $earthPersonData = $modx->newObject('earthPersonData');
            $earthPersonData->set('user_id', $user->get('id'));
        }
        $earthPersonData->fromArray($profile->get('extended'));
        $earthPersonData->save();

        break;
}