id: 25
name: setResourceIDs
description: 'Looks for resource IDs of key Romanesco pages that were built by the Romanesco Backyard package. Updates system setting with corresponding ID if resource is found. Disabled by default.'
category: c_configuration
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:38:"romanesco.setresourceids.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * setResourceIDs
 *
 * This plugin looks for resource IDs of Romanesco pages that were built by the
 * Romanesco Backyard package. When a resource is found, the referring system
 * setting is updated with the corresponding ID.
 *
 * It's deactivated by default, because the Backyard package includes a resolver
 * that does the same thing.
 *
 * @var modX $modx
 *
 * @package romanesco
 */

$eventName = $modx->event->name;

switch($eventName) {
    case 'OnDocFormSave':

        //$corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
        //$assetsPath = $modx->getOption('assets_path');

        if (!function_exists('setResourceID')) {
            function setResourceID($systemSetting, $contextKey, $alias)
            {
                global $modx;

                // Get the resource
                $query = $modx->newQuery('modResource');
                $query->where(array(
                    'context_key' => $contextKey,
                    'alias' => $alias,
                ));
                $query->select('id');
                $resourceID = $modx->getValue($query->prepare());

                if (!$resourceID) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not find resource ID for: ' . $alias);
                    return;
                }

                // Update system setting
                $setting = $modx->getObject('modSystemSetting', array('key' => $systemSetting));

                if ($setting) {
                    $setting->set('value', $resourceID);
                    $setting->save();
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not find system setting with key: ' . $systemSetting);
                }
            }
        }

        if (!function_exists('setContextSetting')) {
            function setContextSetting($contextSetting, $contextKey, $alias)
            {
                global $modx;

                // Get the resource
                $query = $modx->newQuery('modResource');
                $query->where(array(
                    'context_key' => $contextKey,
                    'alias' => $alias,
                ));
                $query->select('id');
                $resourceID = $modx->getValue($query->prepare());

                if (!$resourceID) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not find resource ID for: ' . $alias);
                    return;
                }

                // Update context setting
                $setting = $modx->getObject('modContextSetting', array(
                    'context_key' => $contextKey,
                    'key' => $contextSetting
                ));

                if ($setting) {
                    $setting->set('value', $resourceID);
                    $setting->save();
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not find context setting with key: ' . $contextSetting);
                }
            }
        }

        // Find resources and set correct IDs
        setResourceID('romanesco.footer_container_id', 'global','footers');
        setResourceID('romanesco.cta_container_id', 'global','call-to-actions');
        setResourceID('romanesco.global_backgrounds_id', 'global','backgrounds');
        setResourceID('formblocks.container_id', 'global','forms');
        setResourceID('romanesco.dashboard_id', 'hub','dashboard');
        setResourceID('romanesco.pattern_container_id', 'hub','patterns');
        setResourceID('romanesco.backyard_container_id', 'hub','backyard');

        // Set site_start for Project Hub context
        setContextSetting('site_start', 'hub','dashboard');

        break;
}