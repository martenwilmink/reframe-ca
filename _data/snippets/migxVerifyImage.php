id: 100083
name: migxVerifyImage
description: 'Hook that checks if an image already exists (and if it''s edited) before saving.'
category: E6_dat_verify
properties: 'a:0:{}'

-----

/**
 * migxVerifyImage
 *
 * Hook that checks if an image already exists (and if it's edited) before
 * saving.
 *
 * Needs to be attached to beforesave event.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('earthbrain.core_path', null, $modx->getOption('core_path') . 'components/earthbrain/');
$earthbrain = $modx->getService('earthbrain','EarthBrain',$corePath . 'model/earthbrain/', array('core_path' => $corePath));

if (!($earthbrain instanceof EarthBrain)) return;

$object = $modx->getOption('object', $scriptProperties);
$properties = $modx->getOption('scriptProperties', $scriptProperties, '');
$configs = $modx->getOption('configs', $properties, '');

//$modx->log(modX::LOG_LEVEL_ERROR, 'POST: ' . print_r($_REQUEST, true));
//$modx->log(modX::LOG_LEVEL_ERROR, 'Props: ' . print_r($properties, true));
//$modx->log(modX::LOG_LEVEL_ERROR, 'Configs: ' . $configs);

if (is_object($object))
{
    $img = json_decode($object->get('img'), true);
    $imgExisting = null;

    // Check if image already exists
    if ($properties['object_id'] !== 'new')
    {
        $q = $modx->newQuery($properties['class_key'], [
            'id' => $object->get('id')
        ]);
        $q->select('img');
        $imgExisting = json_decode($modx->getValue($q->prepare()), true);
    }

    // Check if it's being created, updated or removed
    if ($img && $imgExisting)
    {
        $diff1 = array_merge($img['sourceImg'], $img['crop']);
        $diff2 = array_merge($imgExisting['sourceImg'], $imgExisting['crop']);
        $imgDiff = array_diff_assoc($diff1, $diff2);

        //$modx->log(modX::LOG_LEVEL_ERROR, 'Diff: ' . print_r($imgDiff, true));

        if ($imgDiff && !$imgDiff['src']) {
            $object->set('img_action', 'update');
            //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is being updated");
        }
        elseif ($imgDiff && $imgDiff['src']) {
            $object->set('img_action', 'create'); // treat replaced image as new
            //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is being replaced");
        }
        else {
            $object->set('img_action', '');
            //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is NOT being updated");
        }

    }
    elseif (!$img && $imgExisting)
    {
        $object->set('img_action', 'remove');
        //$modx->log(modX::LOG_LEVEL_ERROR, "Image exists and is being removed");
    }
    elseif ($img && !$imgExisting)
    {
        $object->set('img_action', 'create');
        //$modx->log(modX::LOG_LEVEL_ERROR, "Image is new");
    }
}








return '';