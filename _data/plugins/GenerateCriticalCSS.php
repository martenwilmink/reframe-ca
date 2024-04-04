id: 41
name: GenerateCriticalCSS
description: 'Determine which CSS styles are used above the fold and write them to a custom CSS file. This needs NPM and the critical package to be installed.'
category: c_performance
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:43:"romanesco.generatecriticalcss.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:6:"review";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * GenerateCriticalCSS
 *
 * Determine which CSS styles are used above the fold and write them to a custom
 * CSS file. This needs NPM and the critical package to be installed.
 *
 * Because generating these stylesheets is very resource intensive, it is
 * inadvisable to run this in parallel (which is what would happen if you
 * trigger a save action on a batch of resources).
 *
 * To prevent performance issues, you should also install the Scheduler extra.
 * The plugin will then add each task to the Scheduler work queue, so they can
 * be executed serially (or in small batches).
 *
 * https://github.com/addyosmani/critical
 * https://github.com/modmore/Scheduler
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

$basePath = $modx->getOption('base_path');
$context = $modx->resource->get('context_key');

// Abort if critical is not enabled for current context
if (!$romanesco->getConfigSetting('critical_css', $context)) return;

$cssPath = $romanesco->getContextSetting('romanesco.custom_css_path', $context);
$criticalPath = $romanesco->getContextSetting('romanesco.critical_css_path', $context);
$distPath = $romanesco->getContextSetting('romanesco.semantic_dist_path', $context);

switch ($modx->event->name) {
    case 'OnDocFormSave':
        /**
         * @var modResource $resource
         * @var int $id
         */

        $globalTemplates = [0,8,9,10,11,19,20,27]; // 0 = empty template
        $excludedTemplates = explode(',', $romanesco->getConfigSetting('critical_excluded_templates', $context));
        $excludedTemplates = array_merge($globalTemplates, $excludedTemplates);
        $sharedTemplates = explode(',', $romanesco->getConfigSetting('critical_shared_templates', $context));

        $template = $modx->getObject('modTemplate', array('id' => $resource->get('template')));
        $url = $modx->makeUrl($id,'','','full');
        $uri = ltrim($resource->get('uri'),'/');
        $uri = rtrim($uri,'/');
        $uri = str_replace('.html','',$uri);
        $criticalPath = rtrim($criticalPath,'/');

        // Empty and excluded templates
        if (in_array($resource->get('template'), $excludedTemplates) || !is_object($template)) {
            $resource->setTVValue('critical_css_uri', '');
            break;
        }

        // Templates with shared CSS
        if (in_array($resource->get('template'), $sharedTemplates)) {
            $uri = strtolower($template->get('templatename'));
            $uri = str_replace(' ', '', $uri);
        }

        // Store full path to css file in a TV
        $resource->setTVValue('critical_css_uri', "$criticalPath/$uri.css");

        // Use Scheduler for adding task to queue (if available)
        /** @var Scheduler $scheduler */
        $schedulerPath = $modx->getOption('scheduler.core_path', null, $modx->getOption('core_path') . 'components/scheduler/');
        $scheduler = $modx->getService('scheduler', 'Scheduler', $schedulerPath . 'model/scheduler/');

        if (!($scheduler instanceof Scheduler)) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[GenerateCriticalCSS] Scheduler not found. Generating CSS directly.');

            // NB: this will delay the save action significantly!
            $romanesco->generateCriticalCSS(array(
                'id' => $id,
                'uri' => $uri,
                'cssPath' => $cssPath,
                'criticalPath' => $criticalPath,
                'distPath' => $distPath,
            ));

            break;
        }

        $task = $scheduler->getTask('romanesco', 'GenerateCriticalCSS');

        // Create task first if it doesn't exist
        if (!($task instanceof sTask)) {
            $task = $modx->newObject('sSnippetTask');
            $task->fromArray(array(
                'class_key' => 'sSnippetTask',
                'content' => 'generateCriticalCSS',
                'namespace' => 'romanesco',
                'reference' => 'GenerateCriticalCSS',
                'description' => 'Extract above-the-fold styling into custom CSS file.'
            ));
            if (!$task->save()) {
                return 'Error saving GenerateCriticalCSS task';
            }
        }

        // Check if task is not already scheduled
        $pendingTasks = $modx->getCollection('sTaskRun', array(
            'task' => $task->get('id'),
            'status' => 0,
            'executedon' => NULL,
        ));
        foreach ($pendingTasks as $pendingTask) {
            $data = $pendingTask->get('data');
            if (isset($data['id']) && $data['id'] == $id && isset($data['url']) && $data['url'] == $url) {
                return true;
            }
        }

        // Schedule a new run
        $task->schedule('+1 minutes', array(
            'id' => $id,
            'url' => $url,
            'uri' => $uri,
        ));

        break;

//    case 'OnWebPagePrerender':
//        if ($_SERVER['HTTPS'] === 'on') {
//            $cssFile = $modx->resource->getTVValue('critical_css_uri');
//
//            // Create array of objects for the header
//            $linkObjects = array();
//            if ($cssFile && file_exists($basePath . $cssFile)) {
//                $linkObjects[] = "</{$cssFile}>; as=style; rel=preload;";
//            }
//
//            // Set PHP header
//            header('Link: ' . implode(',',$linkObjects));
//        }
//
//        break;
}