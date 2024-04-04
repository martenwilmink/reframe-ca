id: 28
name: UpdateStyling
description: 'Fires when theme settings are changed under Configuration. It updates Semantic UI theme.variables and triggers a new SUI build in the background. Requires NPM and EXEC function on the server.'
category: c_configuration
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:37:"romanesco.updatestyling.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * UpdateStyling
 *
 * This plugin is activated when certain theme settings are changed in the
 * ClientConfig CMP.
 *
 * It changes some variables used by Semantic UI to generate the CSS, and it
 * triggers a new SUI build in the background. This requires NPM to be available
 * on the server.
 *
 * It also generates favicon images if a logo badge is provided. This relies on
 * a few Gulp dependencies (see package.json) and the Real Favicon service:
 * https://realfavicongenerator.net/favicon/gulp
 *
 * Update May 15, 2020:
 * This plugin is now able to process context-aware configuration settings.
 *
 * Update May 20, 2020:
 * The plugin no longer relies on an assets/css/theme.variables resource to be
 * present in MODX. The settings are directly written to a static file now.
 *
 * @var modX $modx
 * @package romanesco
 */

$eventName = $modx->event->name;

switch($eventName) {
    case 'ClientConfig_ConfigChange':
        $corePath = $modx->getOption('clientconfig.core_path', null, $modx->getOption('core_path') . 'components/clientconfig/');
        $clientConfig = $modx->getService('clientconfig','ClientConfig', $corePath . 'model/clientconfig/', array('core_path' => $corePath));
        $imgMediaSource = $modx->getObject('sources.modMediaSource', 15);
        $output = array();

        // Get saved values
        $savedSettings = (!empty($_POST['values'])) ? $_POST['values'] : '[]';
        $savedSettings = json_decode($savedSettings, true);
        if (!is_array($savedSettings)) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[UpdateStyling] No values array available');
            break;
        }

        // Get current configuration settings (before save) for active context
        $currentContext = $savedSettings['context'] ?? '';
        $currentSettings = $clientConfig->getSettings($currentContext);
        if ($clientConfig instanceof ClientConfig) {
            $cacheOptions = array(xPDO::OPT_CACHE_KEY => 'system_settings');
            $settings = $modx->getCacheManager()->get('clientconfig', $cacheOptions);
        }

        // Continue with theme related settings only
        if (!function_exists('filterThemeSettings')) {
            function filterThemeSettings($settings): array
            {
                return array_filter(
                    $settings,
                    function ($key) {
                        if (strpos($key, 'theme_') === 0 || strpos($key, 'logo_') === 0) {
                            return $key;
                        }
                        else {
                            return false;
                        }
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }
        }
        $currentSettingsTheme = filterThemeSettings($currentSettings);
        $savedSettingsTheme = filterThemeSettings($savedSettings);

        // Remove leading '/' slash from path values
        // This somehow gets added by MODX, resulting in these keys being incorrectly flagged as changed
        $currentSettingsTheme['logo_path'] = ltrim($currentSettingsTheme['logo_path'],'/') ?? '';
        $currentSettingsTheme['logo_inverted_path'] = ltrim($currentSettingsTheme['logo_inverted_path'],'/') ?? '';
        $currentSettingsTheme['logo_badge_path'] = ltrim($currentSettingsTheme['logo_badge_path'],'/') ?? '';
        $currentSettingsTheme['logo_badge_inverted_path'] = ltrim($currentSettingsTheme['logo_badge_inverted_path'],'/') ?? '';

        // Add media source to saved paths
        if (isset($savedSettingsTheme['logo_path'])) {
            $savedSettingsTheme['logo_path'] = $imgMediaSource->prepareOutputUrl($savedSettingsTheme['logo_path']);
        }
        if (isset($savedSettingsTheme['logo_inverted_path'])) {
            $savedSettingsTheme['logo_inverted_path'] = $imgMediaSource->prepareOutputUrl($savedSettingsTheme['logo_inverted_path']);
        }
        if (isset($savedSettingsTheme['logo_badge_path'])) {
            $savedSettingsTheme['logo_badge_path'] = $imgMediaSource->prepareOutputUrl($savedSettingsTheme['logo_badge_path']);
        }
        if (isset($savedSettingsTheme['logo_badge_inverted_path'])) {
            $savedSettingsTheme['logo_badge_inverted_path'] = $imgMediaSource->prepareOutputUrl($savedSettingsTheme['logo_badge_inverted_path']);
        }

        // Compare saved settings to current settings
        $updatedSettings = array_diff_assoc($savedSettingsTheme, $currentSettingsTheme);

        // Regenerate styling elements if theme settings were updated or deleted
        if ($updatedSettings) {
            $corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
            $romanesco = $modx->getService('romanesco','Romanesco', $corePath . 'model/romanescobackyard/', array('core_path' => $corePath));
            if (!($romanesco instanceof Romanesco)) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Romanesco] Class not found!');
                break;
            }

            // Clear cache, to ensure build process uses the latest values
            $modx->getCacheManager()->delete('clientconfig',array(xPDO::OPT_CACHE_KEY => 'system_settings'));

            // Grab variables after cache rebuild
            $latestSettings = $clientConfig->getSettings($currentContext);

            // Generate theme.variables file
            if (!$romanesco->generateThemeVariables($latestSettings, $currentContext)) {
                $modx->log(modX::LOG_LEVEL_ERROR, "[Romanesco] Could not generate theme.variables for context $currentContext");
                break;
            }

            // Generate custom CSS for this context
            if (!$romanesco->generateCustomCSS($currentContext, 1)) {
                $modx->log(modX::LOG_LEVEL_ERROR, "[Romanesco] Could not generate custom CSS for context $currentContext");
                break;
            }

            // Generate favicon if a new logo image was provided
            if (isset($updatedSettings['logo_badge_path'])) {
                if (!$romanesco->generateFavicons($latestSettings)) {
                    $modx->log(modX::LOG_LEVEL_ERROR, "[Romanesco] Could not generate favicon for context $currentContext");
                    break;
                }
            }

            // Prevent favicons from being loaded if badge image is not present at this point
            if (!isset($latestSettings['logo_badge_path'])) {
                $version = $modx->getObject('modSystemSetting', array('key' => 'romanesco.favicon_version'));
                if ($version) {
                    $version->set('value', '');
                    $version->save();
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not find favicon_version setting');
                }
            }

            // Clear cache
            $modx->cacheManager->refresh();
        }

        // Report any validation errors in log
        if (array_filter($output)) {
            $errorMsg = '';
            foreach ($output as $line) {
                $errorMsg .= "\n" . $line;
            }
            return (" Report: " . $errorMsg);
        }

        break;
}