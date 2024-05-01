id: 100024
name: EarthPersonActivate
category: E7_person
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
        $earthbrain = $modx->getService('earthbrain','earthbrain',$corePath . 'model/earthbrain/',array('core_path' => $corePath));
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