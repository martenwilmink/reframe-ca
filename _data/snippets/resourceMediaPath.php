id: 71
name: resourceMediaPath
description: 'Standalone version of a snippet that comes with MIGX. Generates subfolders in media sources. Keeps your folder structure tidy when adding lots of images in lots of resources (e.g. galleries).'
category: f_resource
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:41:"romanesco.resourcemediapath.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:11:"conflicting";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:42:"romanesco.resourcemediapath.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * resourceMediaPath
 *
 * Dynamically calculates the upload path for a given resource.
 *
 * This Snippet is meant to dynamically calculate your baseBath attribute
 * for custom Media Sources. This is useful if you wish to shepard uploaded
 * images to a folder dedicated to a given resource. E.g. page 123 would
 * have its own images that page 456 could not reference.
 *
 * USAGE
 * [[resourceMediaPath? &pathTpl=`assets/businesses/{id}/`]]
 * [[resourceMediaPath? &pathTpl=`assets/resourceimages/{id}/` &checkTVs=`mymigxtv`]]
 * [[resourceMediaPath? &pathTpl=`assets/test/{breadcrumb}`]]
 * [[resourceMediaPath? &pathTpl=`assets/test/{breadcrumb}` &breadcrumbdepth=`2`]]
 *
 * PARAMETERS
 * &pathTpl string formatting string specifying the file path.
 *		Relative to MODX base_path
 *		Available placeholders: {id}, {pagetitle}, {parent}
 * &docid (optional) integer page id
 * &createFolder (optional) boolean whether to create folder or not
 * &checkTVs (optional) comma-separated list of TVs to check, before directory is created
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$pathTpl = $modx->getOption('pathTpl', $scriptProperties, '');
$docid = $modx->getOption('docid', $scriptProperties, '');
$createfolder = $modx->getOption('createFolder', $scriptProperties, false);
$tvname = $modx->getOption('tvname', $scriptProperties, '');
$checktvs = $modx->getOption('checkTVs', $scriptProperties, false);

$path = '';
$fullpath = '';
$createpath = false;
$fallbackpath = $modx->getOption('fallbackPath', $scriptProperties, 'assets/migxfallback/');

if (empty($pathTpl)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[resourceMediaPath]: pathTpl not specified.');
    return;
}

if (empty($docid) && $modx->getPlaceholder('mediasource_docid')) {
    // placeholder was set by some script
    // warning: the parser may not render placeholders, e.g. &docid=`[[*parent]]` may fail
    $docid = $modx->getPlaceholder('mediasource_docid');
}

if (empty($docid) && $modx->getPlaceholder('docid')) {
    // placeholder was set by some script
    // warning: the parser may not render placeholders, e.g. &docid=`[[*parent]]` may fail
    $docid = $modx->getPlaceholder('docid');
}
if (empty($docid)) {

    //on frontend
    if (is_object($modx->resource)) {
        $docid = $modx->resource->get('id');
    }
    //on manager resource/update page
    else {
        $createpath = $createfolder;

        // Read the &id param from an Ajax request
        $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $parsedQuery);
        }

        // Avoid docid to be set to parent container
        $requestAction = $_REQUEST['a'] ?? '';
        $action = $parsedQuery['a'] ?? '';
        if (!$action && $requestAction || $action == $requestAction) {
            $docid = $_REQUEST['id'] ?? '';
        }
        elseif ($action === 'resource/update') {
            $docid = (int)$parsedQuery['amp;id'] ?? (int)$parsedQuery['id'] ?? 0;
        }
    }
}

if (empty($docid)) {
    $modx->log(modX::LOG_LEVEL_DEBUG, '[resourceMediaPath]: docid could not be determined.');
}

if (empty($docid) || empty($pathTpl)) {
    $path = $fallbackpath;
    $fullpath = $modx->getOption('base_path') . $fallbackpath;
    $createpath = true;
}

if (empty($fullpath) && $resource = $modx->getObject('modResource', $docid)) {
    $path = $pathTpl;
    $ultimateParent = '';
    if (strstr($path, '{breadcrumb}') || strstr($path, '{ultimateparent}')) {
        $depth = $modx->getOption('breadcrumbdepth', $scriptProperties, 10);
        $ctx = $resource->get('context_key');
        $parentids = $modx->getParentIds($docid, $depth, array('context' => $ctx));
        $breadcrumbdepth = $modx->getOption('breadcrumbdepth', $scriptProperties, count($parentids));
        $breadcrumbdepth = $breadcrumbdepth > count($parentids) ? count($parentids) : $breadcrumbdepth;
        if (count($parentids) > 1) {
            $parentids = array_reverse($parentids);
            $parentids[] = $docid;
            $ultimateParent = $parentids[1];
        } else {
            $ultimateParent = $docid;
            $parentids = array();
            $parentids[] = $docid;
        }
    }

    if (strstr($path, '{breadcrumb}')) {
        $breadcrumbpath = '';
        for ($i = 1; $i <= $breadcrumbdepth; $i++) {
            $breadcrumbpath .= $parentids[$i] . '/';
        }
        $path = str_replace('{breadcrumb}', $breadcrumbpath, $path);
    }

    if (!empty($tvname)){
        $path = str_replace('{tv_value}', $resource->getTVValue($tvname), $path);
    }
    $path = str_replace('{id}', $docid, $path);
    $path = str_replace('{pagetitle}', $resource->get('pagetitle'), $path);
    $path = str_replace('{alias}', $resource->get('alias'), $path);
    $path = str_replace('{parent}', $resource->get('parent'), $path);
    $path = str_replace('{context_key}', $resource->get('context_key'), $path);
    $path = str_replace('{ultimateparent}', $ultimateParent, $path);
    if ($template = $resource->getOne('Template')) {
        $path = str_replace('{templatename}', $template->get('templatename'), $path);
    }
    if ($user = $modx->user) {
        $path = str_replace('{username}', $modx->user->get('username'), $path);
        $path = str_replace('{userid}', $modx->user->get('id'), $path);
    }

    $fullpath = $modx->getOption('base_path') . $path;

    if ($createpath && $checktvs){
        $createpath = false;
        if ($template) {
            $tvs = explode(',',$checktvs);
            foreach ($tvs as $tv){
                if ($template->hasTemplateVar($tv)){
                    $createpath = true;
                }
            }
        }

    }

} else {
    $modx->log(modX::LOG_LEVEL_DEBUG, sprintf('[resourceMediaPath]: resource not found (page id %s).', $docid));
}

if ($createpath && !file_exists($fullpath)) {

    $permissions = octdec('0' . (int)($modx->getOption('new_folder_permissions', null, '755', true)));
    if (!@mkdir($fullpath, $permissions, true)) {
        $modx->log(modX::LOG_LEVEL_DEBUG, sprintf('[resourceMediaPath]: could not create directory %s).', $fullpath));
    } else {
        chmod($fullpath, $permissions);
    }
}

return $path;