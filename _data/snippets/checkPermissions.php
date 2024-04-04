id: 125
name: checkPermissions
description: 'Check if user is allowed to access the given (or current) context and redirect to unauthorized page if that''s not the case.'
category: f_user
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:40:"romanesco.checkpermissions.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:41:"romanesco.checkpermissions.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * checkPermissions
 *
 * Check if user has access permissions for a certain context and redirect to
 * unauthorized page if that's not the case.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$context = $modx->getOption('context', $scriptProperties, $modx->context->get('key'));
$redirectTo = $modx->getOption('redirectTo', $scriptProperties, null);

// If a context is specified, make sure we're in it
if ($context !== $modx->context->get('key')) {
    return '';
}

// Exclude the unauthorized page from any redirects
if ($modx->resource->get('id') == $modx->getOption('unauthorized_page')) {
    return '';
}

if (!$modx->user->hasSessionContext($context)) {
    if (!empty($redirectTo)) {
        $redirectParams = !empty($redirectParams) ? $modx->fromJSON($redirectParams) : '';
        $url = $modx->makeUrl($redirectTo,'',$redirectParams,'full');
        $modx->sendRedirect($url);
    } else {
        $modx->sendUnauthorizedPage();
    }
}

return '';