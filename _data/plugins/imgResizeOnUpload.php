id: 100025
name: imgResizeOnUpload
category: E7_plumbing
plugincode: "/**\n * imgResizeOnUpload plugin\n *\n * IMPORTANT: Only works when uploading images inside MIGX grids!\n *\n * @var modX $modx\n * @var array $scriptProperties\n */\n\nuse Jcupitt\\Vips;\n\nif ($modx->event->name != 'OnFileManagerUpload') return;\n\n$file = $modx->event->params['files']['file'];\n$directory = $modx->event->params['directory'];\n$source = $modx->event->params['source'];\n\nif ($file['error'] != 0) return;\n\n$fileName = $file['name'];\n$basePath = '';\n$imageExtensions = '';\n$resizeConfigs = [];\n$result = [];\n\nif ($source instanceof modMediaSource)\n{\n    $source->initialize();\n    $basePath = str_replace('/./', '/', $source->getBasePath());\n    $baseUrl = $modx->getOption('site_url') . $source->getBaseUrl();\n    $sourceProperties = $source->getPropertyList();\n\n    $allowedExtensions = $modx->getOption('upload_images');\n    $resizeConfigs = $modx->getOption('resizeConfigs', $sourceProperties, '');\n    $resizeConfigs = $modx->fromJson($resizeConfigs);\n    $thumbsContainer = $modx->getOption('thumbsContainer', $sourceProperties, 'thumbs/');\n    $imageExtensions = $modx->getOption('imageExtensions', $sourceProperties, $allowedExtensions);\n    $imageExtensions = explode(',', $imageExtensions);\n}\n\nif (is_array($resizeConfigs) && count($resizeConfigs) > 0)\n{\n    foreach ($resizeConfigs as $rc) {\n        if (isset($rc['alias'])) {\n            $filePath = $basePath . $directory;\n            $filePath = str_replace('//','/', $filePath);\n            $srcPath = $filePath . $fileName; // pin source image at root level, before altering file path\n\n            if ($rc['alias'] != 'origin') {\n                $filePath = str_replace($rc['alias'] . '/', '' , $filePath); // prevent nested alias folders\n                $filePath = $filePath . $rc['alias'] . '/';\n                if (!file_exists($filePath)) {\n                    $permissions = octdec('0' . (int)($modx->getOption('new_folder_permissions', null, '755', true)));\n                    if (!@mkdir($filePath, $permissions, true)) {\n                        $modx->log(modX::LOG_LEVEL_ERROR, '[imgResizeOnUpload] Could not create directory '. $filePath);\n                    } else {\n                        chmod($filePath, $permissions);\n                    }\n                }\n            }\n\n            $fullPath = $filePath . $fileName;\n            $tempFile = $filePath . '_' . $fileName;\n            $name = pathinfo($fileName, PATHINFO_FILENAME);\n            $ext = pathinfo($fileName, PATHINFO_EXTENSION);\n\n            if (in_array($ext, $imageExtensions)) {\n                $width = $rc['w'] ?? 3000;\n                $height = $rc['h'] ?? 3000;\n                $quality = $rc['q'] ?? $modx->getOption('romanesco.img_quality', null, 65);\n                $stripMeta = (bool)$rc['stripMeta'];\n\n                // Quality setting can be a placeholder\n                if (!is_numeric($quality)) {\n                    $uniqid = uniqid();\n                    $chunk = $modx->newObject('modChunk', array('name' => \"{tmp}-{$uniqid}\"));\n                    $chunk->setCacheable(false);\n                    $quality = $chunk->process(null, $quality);\n                }\n\n                // Resize with libvips.\n                // Existing files can't be replaced, because libvips streams the\n                // source image in parallel with writing new output.\n                try {\n                    $image = Vips\\Image::thumbnail($srcPath, $width, [\n                        'height' => $height,\n                        'size' => 'down', // don't enlarge\n                    ]);\n                    $image\n                        ->sharpen(['m2'=>10])\n                        ->writeToFile($tempFile, [\n                            'Q' => $quality,\n                            'strip' => $stripMeta,\n                        ]\n                    );\n                }\n                catch (Vips\\Exception $e) {\n                    $modx->log(modX::LOG_LEVEL_ERROR, '[Vips] ' . $e->getMessage());\n                    return ['error' => $e->getMessage()];\n                }\n\n                // Create WebP version.\n                // Be aware: this slows the save process down considerably.\n                //try {\n                //    $image = Vips\\Image::thumbnail($fullPath, 3000, ['height'=>3000]);\n                //    $image = $image->sharpen();\n                //    $image->writeToFile($filePath . $name . '.webp', [\"Q\"=>50]);\n                //}\n                //catch (Vips\\Exception $e) {\n                //    $modx->log(modX::LOG_LEVEL_ERROR, '[Vips] ' . $e->getMessage());\n                //    return;\n                //}\n\n                // Replace original with resized version\n                if (file_exists($tempFile)) {\n                    unlink($fullPath);\n                    rename($tempFile, $fullPath);\n                }\n            }\n        }\n    }\n}\n\nreturn true;"
properties: 'a:0:{}'

-----


/**
 * imgResizeOnUpload plugin
 *
 * IMPORTANT: Only works when uploading images inside MIGX grids!
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

use Jcupitt\Vips;

if ($modx->event->name != 'OnFileManagerUpload') return;

$file = $modx->event->params['files']['file'];
$directory = $modx->event->params['directory'];
$source = $modx->event->params['source'];

if ($file['error'] != 0) return;

$fileName = $file['name'];
$basePath = '';
$imageExtensions = '';
$resizeConfigs = [];
$result = [];

if ($source instanceof modMediaSource)
{
    $source->initialize();
    $basePath = str_replace('/./', '/', $source->getBasePath());
    $baseUrl = $modx->getOption('site_url') . $source->getBaseUrl();
    $sourceProperties = $source->getPropertyList();

    $allowedExtensions = $modx->getOption('upload_images');
    $resizeConfigs = $modx->getOption('resizeConfigs', $sourceProperties, '');
    $resizeConfigs = $modx->fromJson($resizeConfigs);
    $thumbsContainer = $modx->getOption('thumbsContainer', $sourceProperties, 'thumbs/');
    $imageExtensions = $modx->getOption('imageExtensions', $sourceProperties, $allowedExtensions);
    $imageExtensions = explode(',', $imageExtensions);
}

if (is_array($resizeConfigs) && count($resizeConfigs) > 0)
{
    foreach ($resizeConfigs as $rc) {
        if (isset($rc['alias'])) {
            $filePath = $basePath . $directory;
            $filePath = str_replace('//','/', $filePath);
            $srcPath = $filePath . $fileName; // pin source image at root level, before altering file path

            if ($rc['alias'] != 'origin') {
                $filePath = str_replace($rc['alias'] . '/', '' , $filePath); // prevent nested alias folders
                $filePath = $filePath . $rc['alias'] . '/';
                if (!file_exists($filePath)) {
                    $permissions = octdec('0' . (int)($modx->getOption('new_folder_permissions', null, '755', true)));
                    if (!@mkdir($filePath, $permissions, true)) {
                        $modx->log(modX::LOG_LEVEL_ERROR, '[imgResizeOnUpload] Could not create directory '. $filePath);
                    } else {
                        chmod($filePath, $permissions);
                    }
                }
            }

            $fullPath = $filePath . $fileName;
            $tempFile = $filePath . '_' . $fileName;
            $name = pathinfo($fileName, PATHINFO_FILENAME);
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

            if (in_array($ext, $imageExtensions)) {
                $width = $rc['w'] ?? 3000;
                $height = $rc['h'] ?? 3000;
                $quality = $rc['q'] ?? $modx->getOption('romanesco.img_quality', null, 65);
                $stripMeta = (bool)$rc['stripMeta'];

                // Quality setting can be a placeholder
                if (!is_numeric($quality)) {
                    $uniqid = uniqid();
                    $chunk = $modx->newObject('modChunk', array('name' => "{tmp}-{$uniqid}"));
                    $chunk->setCacheable(false);
                    $quality = $chunk->process(null, $quality);
                }

                // Resize with libvips.
                // Existing files can't be replaced, because libvips streams the
                // source image in parallel with writing new output.
                try {
                    $image = Vips\Image::thumbnail($srcPath, $width, [
                        'height' => $height,
                        'size' => 'down', // don't enlarge
                    ]);
                    $image
                        ->sharpen(['m2'=>10])
                        ->writeToFile($tempFile, [
                            'Q' => $quality,
                            'strip' => $stripMeta,
                        ]
                    );
                }
                catch (Vips\Exception $e) {
                    $modx->log(modX::LOG_LEVEL_ERROR, '[Vips] ' . $e->getMessage());
                    return ['error' => $e->getMessage()];
                }

                // Create WebP version.
                // Be aware: this slows the save process down considerably.
                //try {
                //    $image = Vips\Image::thumbnail($fullPath, 3000, ['height'=>3000]);
                //    $image = $image->sharpen();
                //    $image->writeToFile($filePath . $name . '.webp', ["Q"=>50]);
                //}
                //catch (Vips\Exception $e) {
                //    $modx->log(modX::LOG_LEVEL_ERROR, '[Vips] ' . $e->getMessage());
                //    return;
                //}

                // Replace original with resized version
                if (file_exists($tempFile)) {
                    unlink($fullPath);
                    rename($tempFile, $fullPath);
                }
            }
        }
    }
}

return true;