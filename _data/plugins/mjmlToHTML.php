id: 100019
name: mjmlToHTML
description: 'Execute a shell command to transform the MJML syntax into HTML. Requires NodeJS and MJML to be installed, and the PHP exec() function to be available on the server.'
category: MailBlocks
properties: 'a:2:{s:11:"contentType";a:7:{s:4:"name";s:11:"contentType";s:4:"desc";s:33:"mailblocks.mjmltohtml.contentType";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:21:"mailblocks:properties";s:4:"area";s:0:"";}s:8:"htmlPath";a:7:{s:4:"name";s:8:"htmlPath";s:4:"desc";s:30:"mailblocks.mjmltohtml.htmlPath";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:21:"mailblocks:properties";s:4:"area";s:0:"";}}'

-----

/**
 * Resource is parsed based on the code in the SaveProcessedContent gist by:
 *
 * @author @theboxer
 * @comments @sepiariver
 *
 * https://gist.github.com/sepiariver/a7d6fdc89e2050334086
 */

$htmlPath = $modx->getOption('htmlPath', null, MODX_BASE_PATH . '_newsletter/');
$fileName = $modx->resource->get('alias') . '.html';

switch ($modx->event->name) {
    case 'OnDocFormSave':
        if ($resource->get('content_type') == $scriptProperties['contentType']) {

            //$resource = $resource->get('content');

            //$tvName = $modx->getOption('tvName', $scriptProperties, 'processedContent', true);
            //$processTemplate = $modx->getOption('processTemplate', $scriptProperties, false);


            // Assign values from event parameters
            $modx->resource = $resource;
            $modx->resourceIdentifier = $resource->get('id');
            $modx->elementCache = array();

            // Parse the MODX resource, template included
            $resourceOutput = $modx->resource->process();
            $modx->parser->processElementTags('', $resourceOutput, true, false, '[[', ']]', array(), $maxIterations);
            $modx->parser->processElementTags('', $resourceOutput, true, true, '[[', ']]', array(), $maxIterations);


            // Save parsed resource to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'mjml_');
            //chmod($tempFile, 0644);

            $handle = fopen($tempFile, 'w');
            fwrite($handle, $resourceOutput);

            //$modx->log(modX::LOG_LEVEL_ERROR, 'Temp file "' . $tempFile . '"" created at ' . sys_get_temp_dir() );


            // Validate the MJML syntax
            $output = array();
            exec('mjml --validate ' . escapeshellarg($tempFile) . ' 2>&1', $output, $return_value);

            // Output the HTML
            exec('mjml -r ' . escapeshellarg($tempFile) . ' -o ' . escapeshellarg($htmlPath . $fileName));

            fclose($handle);
            unlink($tempFile); // this removes the file

            // Report any validation errors in log
            if (array_filter($output)) {
                $error = "";
                foreach ($output as $line) {
                    $error .= "\n" . $line;
                }
                return (" MJML validation failed:" . $error);
            }
        }

        break;
}

return;