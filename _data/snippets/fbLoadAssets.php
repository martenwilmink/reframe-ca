id: 58
name: fbLoadAssets
category: f_formblocks
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:36:"romanesco.fbloadassets.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:37:"romanesco.fbloadassets.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * fbLoadAssets snippet
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * @package romanesco
 */

$corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
$romanesco = $modx->getService('romanesco','Romanesco',$corePath . 'model/romanescobackyard/',array('core_path' => $corePath));
if (!($romanesco instanceof Romanesco)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[Romanesco] Class not found!');
    return;
}

$assetsPathCSS = $modx->getOption('romanesco.semantic_css_path', $scriptProperties, '');
$assetsPathJS = $modx->getOption('romanesco.semantic_js_path', $scriptProperties, '');
$assetsPathVendor = $modx->getOption('romanesco.semantic_vendor_path', $scriptProperties, '');
$assetsPathDist = $modx->getOption('romanesco.semantic_dist_path', $scriptProperties, '');
$devMode = $modx->getOption('romanesco.dev_mode', $scriptProperties, 0);
$uploadFile = $modx->getOption('uploadFile', $scriptProperties, 0);
$validation = $modx->getOption('frontendValidation', $scriptProperties, $modx->getOption('formblocks.frontend_validation'));
$validationTpl = $modx->getOption('validationTpl', $scriptProperties, 'fbValidation');
$antiSpamHooks = $modx->getOption('antiSpamHooks', $scriptProperties, $modx->getOption('formblocks.antispam_hooks'));
$ajax = $modx->getOption('ajaxMode', $scriptProperties, $modx->getOption('formblocks.ajax_mode'));
$ajaxTpl = $modx->getOption('submitAjaxTpl', $scriptProperties, 'fbSubmitAjax');
$recaptchaTpl = $modx->getOption('loadRecaptchaTpl', $scriptProperties, 'recaptchaLoadAssets');

// Load strings to insert in asset paths when cache busting is enabled
$cacheBusterCSS = $romanesco->getCacheBustingString('CSS');
$cacheBusterJS = $romanesco->getCacheBustingString('JS');

// Load component asynchronously if critical CSS is enabled
$async = '';
if ($romanesco->getConfigSetting('critical_css', $modx->resource->get('context_key'))) {
    $async = ' media="print" onload="this.media=\'all\'"';
}

// Load CSS
$modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/checkbox.min' . $cacheBusterCSS . '.css"' . $async . '>');
$modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/dropdown.min' . $cacheBusterCSS . '.css"' . $async . '>');
$modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/popup.min' . $cacheBusterCSS . '.css"' . $async . '>');
$modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/form.min' . $cacheBusterCSS . '.css"' . $async . '>');
$modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/calendar.min' . $cacheBusterCSS . '.css"' . $async . '>');
$modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/table.min' . $cacheBusterCSS . '.css"' . $async . '>');

// Load JS
$modx->regClientHTMLBlock('<script defer src="' . $assetsPathDist . '/components/checkbox.min' . $cacheBusterJS . '.js"></script>');
$modx->regClientHTMLBlock('<script defer src="' . $assetsPathDist . '/components/dropdown.min' . $cacheBusterJS . '.js"></script>');
$modx->regClientHTMLBlock('<script defer src="' . $assetsPathDist . '/components/popup.min' . $cacheBusterJS . '.js"></script>');
$modx->regClientHTMLBlock('<script defer src="' . $assetsPathDist . '/components/form.min' . $cacheBusterJS . '.js"></script>');
$modx->regClientHTMLBlock('<script defer src="' . $assetsPathDist . '/components/calendar.min' . $cacheBusterJS . '.js"></script>');
$modx->regClientHTMLBlock('<script defer src="' . $assetsPathJS . '/formblocks.min' . $cacheBusterJS . '.js"></script>');

// Load additional assets for file upload field, if present
if ($uploadFile) {
    $modx->regClientHTMLBlock('<script defer src="' . $assetsPathVendor . '/arrive/arrive.min' . $cacheBusterJS . '.js"></script>');
    $modx->regClientHTMLBlock('<script defer src="' . $assetsPathJS . '/fileupload.min' . $cacheBusterJS . '.js"></script>');
}

// Load assets for Recaptcha v3, if enabled
if (str_contains($antiSpamHooks, 'recaptchav3') && !$devMode) {
    $modx->regClientHTMLBlock($modx->getChunk($recaptchaTpl));
}

// Load frontend validation, if enabled
if ($validation) {
    $modx->regClientHTMLBlock($modx->getChunk($validationTpl));
}

// Submit form via AJAX (only if frontend validation is disabled)
if (!$validation && $ajax) {
    $modx->regClientHTMLBlock($modx->getChunk($ajaxTpl));
}

// Load custom assets, if present
// @todo: make this more dynamic
if (is_file('assets/js/formblocks.min.js')) {
    $modx->regClientHTMLBlock('<script defer src="assets/js/formblocks.min' . $cacheBusterJS . '.js"></script>');
} elseif (is_file('assets/js/formblocks.js')) {
    $modx->regClientHTMLBlock('<script defer src="assets/js/formblocks' . $cacheBusterJS . '.js"></script>');
}
if (is_file('assets/js/form-validation.min.js')) {
    $modx->regClientHTMLBlock('<script defer src="assets/js/form-validation.min' . $cacheBusterJS . '.js"></script>');
} elseif (is_file('assets/js/form-validation.js')) {
    $modx->regClientHTMLBlock('<script defer src="assets/js/form-validation' . $cacheBusterJS . '.js"></script>');
}

return '';