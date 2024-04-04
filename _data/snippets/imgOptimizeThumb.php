id: 158
name: imgOptimizeThumb
description: 'Post hook for pThumb, that runs after the thumbnail is generated. It uses the Squoosh library from Google to create a WebP version of the image and optimize the original.'
category: f_performance
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:40:"romanesco.imgoptimizethumb.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:41:"romanesco.imgoptimizethumb.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * imgOptimizeThumb
 *
 * Output modifier for pThumb, to further optimize the generated thumbnail.
 *
 * It uses the Squoosh library from Google to create a WebP version of the image
 * and optimize the original. You need to install the Squoosh CLI package on
 * your server with NPM: 'npm install -g @squoosh/cli'
 *
 * If the Scheduler extra is installed, the Squoosh command is added there as an
 * individual task. This means it takes a little while for all the images to be
 * generated. Without Scheduler they're created when the page is requested,
 * but the initial request will take a lot longer (the thumbnails are
 * also being generated here).
 *
 * To serve the WebP images in the browser, use Nginx to intercept the image
 * request and redirect it to the WebP version. It will do so by setting a
 * different header with the correct mime type, but only if the WebP
 * image is available (and if the browser supports it). So you don't need to
 * change the image paths in MODX or provide any fallbacks in HTML.
 *
 * This guide perfectly explains this little trick:
 * https://alexey.detr.us/en/posts/2018/2018-08-20-webp-nginx-with-fallback/
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @var object $task
 * @var string $input
 * @var string $options
 */

use Jcupitt\Vips;

$corePath = $modx->getOption('romanescobackyard.core_path', null, $modx->getOption('core_path') . 'components/romanescobackyard/');
$romanesco = $modx->getService('romanesco','Romanesco',$corePath . 'model/romanescobackyard/',array('core_path' => $corePath));

if (!($romanesco instanceof Romanesco)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[Romanesco] Class not found!');
    return;
}

// Get image path from task properties, pThumb properties or input
$imgPath = $modx->getOption('img_path', $scriptProperties, $input ?? null);
$imgPathFull = str_replace('//','/', MODX_BASE_PATH . $imgPath);
$imgName = pathinfo($imgPathFull, PATHINFO_FILENAME);
$imgType = pathinfo($imgPathFull, PATHINFO_EXTENSION);
$imgType = strtolower($imgType);
$outputDir = dirname($imgPathFull);

// Check if path or file exist
if (!$imgPath || !file_exists($imgPathFull)) {
    $modx->log(modX::LOG_LEVEL_WARN, '[imgOptimizeThumb] Image not found: ' . $imgPathFull);
    return $imgPath;
}

// Look for resource context key
$context = $modx->getOption('context', $scriptProperties, '');
if (is_object($modx->resource) && !$context) {
    $context = $modx->resource->get('context_key');
}

// Abort if optimization is disabled for this context
if (!$romanesco->getConfigSetting('img_optimize', $context)) {
    return $imgPath;
}

// Abort if file format is not supported
if ($imgType == 'svg') {
    return $imgPath;
}

// And if WebP version is already created
if (file_exists($outputDir . '/' . $imgName . '.webp')) {
    return $imgPath;
}

// Get image quality from output modifier option, task properties or corresponding context setting
$imgQuality = $options ?? $modx->getOption('img_quality', $scriptProperties);
if (!$imgQuality) {
    $imgQuality = $romanesco->getConfigSetting('img_quality', $context);
}
$imgQuality = (int) $imgQuality;

$configWebP = [
    "Q" => $imgQuality,
];

$configJPG = [
    "Q" => $imgQuality,
];

$configPNG = [
    "Q" => $imgQuality,
];

// Use Scheduler for adding task to queue (if available)
    /** @var Scheduler $scheduler */
$schedulerPath = $modx->getOption('scheduler.core_path', null, $modx->getOption('core_path') . 'components/scheduler/');
if (file_exists($schedulerPath)) {
    $scheduler = $modx->getService('scheduler', 'Scheduler', $schedulerPath . 'model/scheduler/');
} else {
    $modx->log(modX::LOG_LEVEL_INFO, '[imgOptimizeThumb] Scheduler is not installed. Generating images directly.');
}

// Generate CSS directly if snippet is run as scheduled task, or if Scheduler is not installed
if (!($scheduler instanceof Scheduler) || isset($task)) {
    try {
        $image = Vips\Image::newFromFile($imgPathFull);
    }
    catch (Vips\Exception $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[Vips] ' . $e->getMessage());
        return $imgPath;
    }

    // Create WebP version
    $image->webpsave($outputDir . '/' . $imgName . '.webp', $configWebP);

    // Overwrite original with optimized version
    if ($imgType == 'png') {
        $image->pngsave($imgPathFull, $configPNG);
    }
    if ($imgType == 'jpg' || $imgType == 'jpeg') {
        $image->jpegsave($imgPathFull, $configJPG);
    }

    return $imgPath;
}

// From here on, we're scheduling a task
$task = $scheduler->getTask('romanesco', 'ImgOptimizeThumb');

// Create task first if it doesn't exist
if (!($task instanceof sTask)) {
    $task = $modx->newObject('sSnippetTask');
    $task->fromArray(array(
        'class_key' => 'sSnippetTask',
        'content' => 'imgOptimizeThumb',
        'namespace' => 'romanesco',
        'reference' => 'ImgOptimizeThumb',
        'description' => 'Create WebP version and reduce file size of thumbnail image.'
    ));
    if (!$task->save()) {
        return 'Error saving ImgOptimizeThumb task';
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
    if (isset($data['img_path']) && $data['img_path'] == $imgPath && isset($data['img_quality']) && $data['img_quality'] == $imgQuality) {
        return;
    }
}

// Schedule a new run
$task->schedule('+1 minutes', array(
    'img_path' => $imgPath,
    'img_quality' => $imgQuality,
    'context' => $context,
));

return $imgPath;