id: 40
name: GenerateSiteCSS
description: 'Creates site.css file for each context, with their own global backgrounds. If you want a context to have its own set of backgrounds, you need to add a child page under Global Backgrounds.'
category: c_performance
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:39:"romanesco.generatesitecss.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:6:"review";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * GenerateSiteCSS
 *
 * Creates site.css file for each context, with their own global backgrounds.
 *
 * If you want a context to have its own set of backgrounds, you need to create
 * a child page under the Global Backgrounds container for it. Make sure the
 * template is GlobalBackgrounds too and that the alias matches the context_key!
 *
 * A default stylesheet (site.css) is also generated, containing only the
 * backgrounds at root level of the Global Backgrounds container.
 *
 * CSS files are regenerated each time a GlobalBackgrounds resource is saved.
 *
 * NB! The plugin priority should be set to something higher than 0. Otherwise,
 * users will need to save the resource twice to see their changes reflected.
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

// Css validator should be loaded through Romanesco
if (!class_exists(CssLint\Linter::class)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[CssLint] Class not found!');
    return;
}

use CssLint\Linter;

switch ($modx->event->name) {
    case 'OnBeforeDocFormSave':
        /**
         * @var modResource $resource
         * @var int $id
         */

        // Exit if resource template is not GlobalBackground(s)
        $templateID = $resource->get('template');
        if ($templateID != 27 && $templateID != 8) {
            break;
        }

        // Clear event output to avoid rogue messages popping up again
        $modx->event->_output = '';

        // Init CSS linter
        $cssLinter = new Linter();

        // Validate the CSS gradient field
        if ($templateID == 27)
        {
            // Prepare an array with submitted ContentBlocks data
            $cbData = $resource->get('contentblocks');
            $cbData = json_decode($cbData, true);

            // It's probably just 1 background field, but let's not assume anything
            $fields = $cbData[0]['content']['main'] ?? [];
            foreach ($fields as $field) {
                if ($field['field'] != 109) continue;
                $i = 0;

                foreach ($field['rows'] as $row) {
                    $i++;

                    $image = 'url(' . $row['image']['url'] . ')';
                    $position = $row['position']['value'] ? : 'center center';
                    $size = $row['size']['value'] ? : 'cover';
                    $repeat = $row['repeat']['value'] ? : 'no-repeat';
                    $attachment = $row['attachment']['value'] ? : 'scroll';
                    $gradient = $row['gradient']['value'];
                    $background = $row['image']['url'] ? $image : $gradient;
                    $css = "
.background::before {
    background:
        $background
        $position /
        $size
        $repeat
        $attachment
        !important
    ;
}";

                    // Validate CSS
                    if ($cssLinter->lintString($css) !== true) {
                        $errors = implode("\n", $cssLinter->getErrors());
                        $modx->log(modX::LOG_LEVEL_ERROR, "CSS for background $id is not valid:" . $css . "\n" . $errors);
                        $modx->event->output("The CSS in layer $i is not valid! Please check the error log for details.<br>");
                    }
                }
            }
        }

        break;

    case 'OnDocFormSave':
        /**
         * @var modResource $resource
         * @var int $id
         */

        $exit = false;

        // Exit if resource template is not GlobalBackground(s)
        $templateID = $resource->get('template');
        if ($templateID != 27 && $templateID != 8) {
            $exit = true;
        }

        // ...but continue if a header background is (being) set
        if ($resource->getTVValue('header_background_img')) {
            $exit = false;
        }

        // Leave the EU?
        if ($exit) break;

        // Generate CSS
        $romanesco->generateBackgroundCSS();

        // Bump CSS version number to force refresh
        $romanesco->bumpVersionNumber();

        // Clear cache
        $modx->cacheManager->refresh();

        break;
}