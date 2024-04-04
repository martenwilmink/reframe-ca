id: 100012
name: SuperBoxSelect
description: 'SuperBoxSelect runtime hooks - registers custom TV input types and includes javascripts on document edit pages.'
category: SuperBoxSelect
properties: 'a:0:{}'

-----

/**
 * SuperBoxSelect Plugin
 *
 * @package superboxselect
 * @subpackage plugin
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$className = 'TreehillStudio\SuperBoxSelect\Plugins\Events\\' . $modx->event->name;

$corePath = $modx->getOption('superboxselect.core_path', null, $modx->getOption('core_path') . 'components/superboxselect/');
/** @var SuperBoxSelect $superboxselect */
$superboxselect = $modx->getService('superboxselect', 'SuperBoxSelect', $corePath . 'model/superboxselect/', [
    'core_path' => $corePath
]);

if ($superboxselect) {
    if (class_exists($className)) {
        $handler = new $className($modx, $scriptProperties);
        if (get_class($handler) == $className) {
            $handler->run();
        } else {
            $modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' could not be initialized!', '', 'SuperBoxSelect Plugin');
        }
    } else {
        $modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' was not found!', '', 'SuperBoxSelect Plugin');
    }
}

return;