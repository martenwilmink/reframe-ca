id: 100016
name: Redactor
description: 'Redactor WYSIWYG editor plugin for MODX Revolution'
properties: null

-----

/**
 * Redactor WYSIWYG Editor Plugin
 *
 * Events: OnManagerPageBeforeRender, OnRichTextEditorRegister, OnRichTextBrowserInit, OnDocFormPrerender
 *
 * @author JP DeVries <mail@devries.jp>
 *
 * @package redactor
 */

$corePath = $modx->getOption('redactor.core_path', null, $modx->getOption('core_path').'components/redactor/');

switch ($modx->event->name) {
    case 'OnTVInputRenderList':
        $modx->event->output($corePath.'elements/tvs/input/');
        break;

    case 'OnTVInputPropertiesList':
        $modx->event->output($corePath.'elements/tvs/inputoptions/');
        break;

    case 'OnRichTextEditorRegister':
        $modx->event->output('Redactor');
        break;

    case 'OnFileManagerFileRename':
        /**
         * @var string $path
         */
        $redactor = $modx->getService('redactor', 'Redactor', $corePath . 'model/redactor/');
        if (!($redactor instanceof Redactor)) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[Redactor] Error loading Redactor service class.');
            return;
        }
        $redactor->renames[] = $path;

        break;

    case 'OnRichTextEditorInit':
        /**
         * @var string $editor
         * @var array $elements
         *
         * Only load up the editor if the editor is Redactor, and use_editor is enabled.
         */
        $rte = isset($editor) ? $editor : $modx->getOption('which_editor', null, '');
        if ($rte !== 'Redactor' || !$modx->getOption('use_editor', null, true)) {
            return;
        }

        /**
         * Attempt to load the Redactor service class. Log error and halt processing if it fails.
         */
        $redactor = $modx->getService('redactor', 'Redactor', $corePath . 'model/redactor/');
        if (!($redactor instanceof Redactor)) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[Redactor] Error loading Redactor service class.');
            return;
        }

        if (isset($resource) && ($resource instanceof modResource || $resource instanceof \MODX\Revolution\modResource)) {
            $redactor->setResource($resource);
        }
        elseif ($modx->resource && ($modx->resource instanceof modResource || $modx->resource instanceof \MODX\Revolution\modResource)) {
            $redactor->setResource($modx->resource);
        }
        elseif ($modx->controller && isset($modx->controller->resource) && ($modx->controller->resource instanceof modResource || $modx->controller->resource instanceof \MODX\Revolution\modResource)) {
            $redactor->setResource($modx->controller->resource);
        }

        // Make sure global assets are loaded
        $redactor->initialize();

        $set = (int)$redactor->getOption('redactor.configuration_set', null, 1, true);
        if (isset($resource)
            && ($resource instanceof modResource || $resource instanceof \MODX\Revolution\modResource)
            && ($template = $resource->getOne('Template'))
            && ($template instanceof modTemplate || $template instanceof \MODX\Revolution\modTemplate)
        ) {
            $props = $template->getProperties();
            $templateSet = array_key_exists('redactor.configuration_set', $props) ? (int)$props['redactor.configuration_set'] : 0;
            if ($templateSet > 0) {
                $set = $templateSet;
            }
        }

        $html = <<<HTML
<script type="text/javascript">
var RedactorConfigurationSet = $set;
MODx.loadRTE = MODx.loadRedactorRTE;
</script>
HTML;

        $modx->event->output($html);
        break;

    case 'ContentBlocks_RegisterInputs':
        /**
         * @var modX $modx
         * @var ContentBlocks $contentBlocks
         * @var array $scriptProperties
         */
        if (class_exists('cbBaseInput')) {
            require_once($corePath . 'elements/inputs/redactor_input.class.php');
            $modx->event->output([
                'redactor' => new RedactorInput($contentBlocks)
            ]);
        }
        break;

    case 'FredBeforeRender':
        /**
         * @var string $path
         */
        $redactor = $modx->getService('redactor', 'Redactor', $corePath . 'model/redactor/');
        if (!($redactor instanceof Redactor)) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[Redactor] Error loading Redactor service class.');
            return;
        }

        $assetsUrl = $redactor->config['assetsUrl'];

        $version = '?v=' . $this->redactor->version;
        $css = <<<HTML
<link rel="stylesheet" href="{$assetsUrl}vendor/imperavi/redactor/redactor.min.css{$version}">
<link rel="stylesheet" href="{$assetsUrl}vendor/codemirror/codemirror.css{$version}">
<link rel="stylesheet" href="{$assetsUrl}dist/modxredactor.min.css{$version}">
HTML;
        if ($customCss = $redactor->getOption('redactor.css')) {
            $customCss = array_filter(array_map('trim', explode(',', $customCss)));
            foreach ($customCss as $url) {
                $css .= '<link rel="stylesheet" href="' . $url . $version . '">';
            }
        }

        $js = <<<HTML
<script type="text/javascript" src="{$assetsUrl}dist/imperavi/redactor.min.js{$version}"></script>
<script type="text/javascript" src="{$assetsUrl}dist/plugins.min.js{$version}"></script>
<script type="text/javascript" src="{$assetsUrl}dist/codemirror.min.js{$version}"></script>
<script type="text/javascript" src="{$assetsUrl}js/fredactor.js{$version}"></script>
HTML;
        if ($customJs = $redactor->getOption('redactor.js')) {
            $customJs = array_filter(array_map('trim', explode(',', $customJs)));
            foreach ($customJs as $url) {
                $js .= '<script type="text/javascript" src="' . $url . $version . '"></script>';
            }
        }
        // Primary redactor scripts and the generated configuration sets
        $js .= '<script type="text/javascript" src="' . $assetsUrl . 'dist/modxredactor.min.js' . $version . '"></script>';
        $js .= $redactor->getGeneratedConfigurationSets([Redactor::OPT_IS_FRED => true]);

        // Make the resource/wctx available
        if (isset($modx->resource) && ($modx->resource instanceof modResource || $modx->resource instanceof \MODX\Revolution\modResource)) {
            $redactor->setResource($modx->resource);
        }

        // Get the default configuration set to use from template or setting. This can be overriden with a Fred RTE Config.
        $set = (int)$redactor->getOption('redactor.configuration_set', null, 1, true);
        if (isset($modx->resource)
            && ($modx->resource instanceof modResource || $modx->resource instanceof \MODX\Revolution\modResource)
            && ($template = $modx->resource->getOne('Template'))
            && ($template instanceof modTemplate || $template instanceof \MODX\Revolution\modTemplate)
        ) {
            $props = $template->getProperties();
            $templateSet = array_key_exists('redactor.configuration_set', $props) ? (int)$props['redactor.configuration_set'] : 0;
            if ($templateSet > 0) {
                $set = $templateSet;
            }
        }
        $js .= '<script type="text/javascript">var RedactorConfigurationSet = ' . $set . ';</script>';

        // Instruct Fred that we offer a "Fredactor" function as "Redactor" RTE.
        $beforeRender = '
    this.registerRTE("Redactor", Fredactor);
';
        // Output it all. Woof.
        $modx->event->_output = [
            'includes' => $css.$js,
            'beforeRender' => $beforeRender,
            'lexicons' => ['redactor:default']
        ];
        return true;
}

return;