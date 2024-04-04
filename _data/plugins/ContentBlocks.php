id: 100015
name: ContentBlocks
description: 'The main plugin for ContentBlocks, responsible for handling generating the form as well as saving the resource. (Part of ContentBlocks)'
category: ContentBlocks
properties: null

-----

/**
 * ContentBlocks
 *
 * Events: OnDocFormRender, OnDocFormSave
 *
 * @package contentblocks
 *
 * @var ContentBlocks $ContentBlocks
 * @var modResource $resource
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('contentblocks.core_path', null, $modx->getOption('core_path') . 'components/contentblocks/');
$assetsUrl = $modx->getOption('contentblocks.assets_url', null, $modx->getOption('assets_url') . 'components/contentblocks/');

$ContentBlocks = $modx->getService('contentblocks', 'ContentBlocks', $corePath . 'model/contentblocks/');
if (!$ContentBlocks) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load ContentBlocks service from plugin. Reinstalling ContentBlocks might fix this.');
    return;
}

switch ($modx->event->name) {
    case 'OnDocFormPrerender':
        if ($modx->controller
            && isset($modx->controller->resource)
            && ($modx->controller->resource instanceof modResource || $modx->controller->resource instanceof \MODX\Revolution\modResource)
        ) {
            $resource = $modx->controller->resource;
            $ContentBlocks->setResource($modx->controller->resource);
        }
        else {
            return;
        }

        // Default settings
        $disabled = (int)$modx->getOption('contentblocks.disabled', null, false);
        $acceptedResourceTypes = $modx->getOption('contentblocks.accepted_resource_types', null, 'modDocument,mgResource');

        // Fake the wctx variable for loading the working context to get settings
        if (!isset($_GET['wctx'])) {
            $_GET['wctx'] = $resource->get('context_key');
        }

        // If we got the working context, get some settings
        if ($modx->controller->loadWorkingContext()) {
            $disabled = (int)$modx->controller->workingContext->getOption('contentblocks.disabled', $disabled);
            $acceptedResourceTypes = $modx->controller->workingContext->getOption('contentblocks.accepted_resource_types', $acceptedResourceTypes);
        }

        // Check if we're using an allowed resource type
        $acceptedType = false;
        $acceptedResourceTypes = explode(',', $acceptedResourceTypes);
        foreach ($acceptedResourceTypes as $type) {
            $m3Type = '\\MODX\\Revolution\\' . $type;
            if ($resource instanceof $type || $resource instanceof $m3Type) {
                $acceptedType = true;
            }
        }

        /** @var modTemplate $template */
        if ($template = $resource->getOne('Template')) {
            $props = $template->getProperties();
            $disabledForTemplate = array_key_exists('contentblocks.disabled', $props) ? (int)$props['contentblocks.disabled'] : null;
            if ($disabledForTemplate !== null) {
                $disabled = $disabledForTemplate;
            }
        }


        // If contentblocks is disabled or this is not an accepted resource, we can stop here.
        if ($disabled || !$acceptedType) {
            return;
        }

        // Load the lexicon
        $modx->controller->addLexiconTopic('contentblocks:default');
        $isContentBlocks = $resource->getProperty('_isContentBlocks', 'contentblocks', null);

        // Load the use_contentblocks setting
        if ($ContentBlocks->getOption('contentblocks.show_resource_option', null, true)) {
            $modx->controller->addJavascript($assetsUrl . 'cmp/js/widgets/booleancombo.js');
            $settingChunk = $modx->newObject('modChunk', array(
                'content' => file_get_contents($corePath . 'templates/setting.tpl')
            ));

            $modx->controller->addHtml($settingChunk->process(array(
                'value' => ($isContentBlocks !== false) ? '1' : '0'
            )));
        }

        // Halt if we're not using contentblocks here
        if ($isContentBlocks === false) {
            // we're done here
            return;
        }

        // Prepare the content
        $contents = array();
        if ($isContentBlocks) {
            // When reloading, the contentblocks field will contain the JSON data
            $contents = $resource->get('contentblocks');
            if (empty($contents)) {
                // If it's empty, we get the data from the resource property instead
                $contents = $resource->getProperty('content', 'contentblocks');
            }

            // Try to decode the JSON
            $contents = $modx->fromJSON($contents);
        }

        if (!is_array($contents) || empty($contents)) {
            $content = $resource->get('content');
            $contents = $ContentBlocks->getDefaultCanvas(false, $content);
        }

        // Generate a wrapper class to apply, so we can target stuff in CSS
        $wrapperCls = array();
        $wrapperCls[] = 'type_' . strtolower($resource->get('class_key'));
        $wrapperCls[] = 'position_' . $ContentBlocks->config['canvas_position'];
        $modxVersion = $modx->getVersionData();
        if (version_compare($modxVersion['full_version'], '2.3.0-dev', '>=')) {
            $wrapperCls[] = 'modx_v23';
        }
        if (version_compare($modxVersion['full_version'], '3.0.0-dev', '>=')) {
            $wrapperCls[] = 'modx_v30';
        }
        $wrapperCls = implode(' ', $wrapperCls);

        // Grab objects for building the canvas
        $objects = $ContentBlocks->getObjectsForCanvas($resource);
        $categories = $modx->toJSON($objects['categories']);
        $fields = $modx->toJSON($objects['fields']);
        $layouts = $modx->toJSON($objects['layouts']);
        $templates = $modx->toJSON($objects['templates']);

        $contents = $modx->toJSON($contents);
        $resourceInfo = $modx->toJSON($resource->get(array('id', 'pagetitle', 'context_key')));
        $config = $modx->toJSON($ContentBlocks->config);

        $modx->controller->addHtml(<<<HTML
<script type="text/javascript">
    var ContentBlocksCategories = $categories,
        ContentBlocksFields = $fields,
        ContentBlocksLayouts = $layouts,
        ContentBlocksTemplates = $templates,
        ContentBlocksContents = $contents,
        ContentBlocksConfig = $config,
        ContentBlocksWrapperCls = "$wrapperCls",
        ContentBlocksResource = $resourceInfo;

    var cbGenerated = false,
        ContentBlocksWillRenderContent = true;
    MODx.on('ready', function () {
        // Prevent double-generation
        if (cbGenerated) return;
        cbGenerated = true;

        ContentBlocks.render();

        // Hook up to beforesubmit to fetch the values and fetch the content blocks
        Ext.getCmp('modx-panel-resource').on('beforesubmit', function(o) {
            o.form.baseParams['contentblocks'] = ContentBlocks.getData();
        });
    });
</script>
HTML
        );
        $scriptTags = $ContentBlocks->getAssets();
        $modx->controller->addHtml($scriptTags);

        break;

    case 'OnDocFormSave':
        $ContentBlocks->setResource($resource);
        $modx->resource = $resource;

        $cbJson = $resource->get('contentblocks');

        $cbContent = $modx->fromJSON($cbJson);

        // RenderContent Event
        $response = $modx->invokeEvent('ContentBlocks_RenderContent', array(
            'cbContent' => $cbContent,
            'cbJson' => $cbJson,
            'resource' => $resource
        ));
        // If multiple responses were returned, only keep filled values
        if (is_array($response)) {
            $response = array_values(array_filter($response));
        }
        if (!empty($response) && is_array($response) && !empty($response[0]['cbContent'])) {
            $cbContent = $response[0]['cbContent'];
            $cbJson = $response[0]['cbJson'];
        }

        if (!empty($cbJson) && $cbContent !== false && is_array($cbContent)) {
            $summary = $ContentBlocks->summarizeContent($cbContent);
            $resource->setProperties(array(
                'content' => $cbJson,
                'linear' => $summary['linear'],
                'fieldcounts' => $summary['fieldcounts'],
                '_isContentBlocks' => true,
            ), 'contentblocks', true);

            // We save the CB data as soon as possible ...
            $resource->save();
            // ... then we parse it to HTML which is stored in the content ...
            try {
                $ContentBlocks->loadParser();
                $resource->setContent($ContentBlocks->generateHtml($cbContent));
                $ContentBlocks->restoreParser();
            } catch (Exception $e) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Exception while trying to parse the content of resource ' . $resource->id . ': ' . $e->getMessage());
            }
            // ... to make sure parse errors don't lose the content.
        }
        $resource->set('contentblocks', '');

        // Make sure we need to continue to use contentblocks
        $useCb = $resource->get('use_contentblocks');
        if (in_array($useCb, array('0', '1'), true)) {
            $resource->setProperty('_isContentBlocks', (bool)$useCb, 'contentblocks');
        }
        $resource->save();
        break;

    /**
     * @var string $path
     */
    case 'OnFileManagerFileRename':
        $ContentBlocks->renames[] = $path;
        break;

    /**
     * @var modResource $resource
     */
    case 'OnResourceMagicPreview':
        $cbJson = $resource->get('contentblocks');
        if (!$cbJson) {
            break; // Avoid attempting to load CB data when CB is disabled.
        }

        $cbContent = $modx->fromJSON($cbJson);

        try {
            $ContentBlocks->loadParser();
            $resource->setContent($ContentBlocks->generateHtml($cbContent));
            $ContentBlocks->restoreParser();
        } catch (Exception $e) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Exception while trying to use Magic Preview on resource ' . $resource->id . ': ' . $e->getMessage());
        }

        break;
}

return;