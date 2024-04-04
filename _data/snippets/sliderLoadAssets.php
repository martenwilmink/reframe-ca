id: 75
name: sliderLoadAssets
description: 'Load CSS and JS dependencies for Swiper slider. It also initializes a Swiper instance for each slider, with it''s own parameters. This means you can use multiple sliders on one page.'
category: f_presentation
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:40:"romanesco.sliderloadassets.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:41:"romanesco.sliderloadassets.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * sliderLoadAssets
 *
 * Loads dependencies for the Swiper carousel (https://swiperjs.com/).
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

$uid = $modx->getOption('uid', $scriptProperties, 0);
$init = $modx->getOption('init', $scriptProperties, 'true');
$columns = $modx->getOption('columns', $scriptProperties, 1);
$scroll = $modx->getOption('slidesToScroll', $scriptProperties, 1);
$direction = $modx->getOption('direction', $scriptProperties, 'horizontal');
$spacing = $modx->getOption('spacing', $scriptProperties, 'none');
$overflow = $modx->getOption('watchOverflow', $scriptProperties, 'true');
$behaviour = $modx->getOption('behaviour', $scriptProperties, '');
$transition = $modx->getOption('transition', $scriptProperties, 'slide');
$pagination = $modx->getOption('pagination', $scriptProperties, 'none');
$responsive = $modx->getOption('responsive', $scriptProperties, 0);
$mobile = $modx->getOption('mobileOnly', $scriptProperties, 0);
$lightbox = $modx->getOption('lightbox', $scriptProperties, 0);
$tpl = $modx->getOption('tpl', $scriptProperties, 'sliderInitJS');

// Convert option values to JS settings
// Keep in mind that 'true' / 'false' needs to be a string here
// -----------------------------------------------------------------------------

// Set element ID and variable name
$id = 'swiper-' . $uid;
$var = 'Swiper' . $uid;

// Convert semantic padding to numeric value
switch ($spacing) {
    case 'relaxed':
        $spacing = 20;
        break;
    case 'very relaxed':
        $spacing = 30;
        break;
    default:
        $spacing = 0;
        break;
}

// Create variable for each behaviour setting
$behaviour = explode(',', $behaviour);
foreach ($behaviour as $option) {
    $$option = 'true';
}

// Only bullet pagination can be clickable
$clickable = ($pagination == 'bullets') ? 'true' : 'false';

// Effects
$effects = array(
    'fade' => '
        fadeEffect: {
            crossFade: true
        },
    ',
    'coverflow' => '
        coverflowEffect: {
            rotate: 30,
            slideShadows: false,
        },
    ',
    'flip' => '
        flipEffect: {
            rotate: 30,
            slideShadows: false,
        },
    ',
    'cube' => '
        cubeEffect: {
            slideShadows: false,
        },
    ',
);

// Responsive
if ($responsive) {
    $breakpoints = "
    breakpoints: {
        '@0.75': {
            slidesPerView: " . round($columns / 2) . ",
            spaceBetween: " . $spacing / 2 . ",
        },
        '@1.00': {
            slidesPerView: " . round($columns * 0.75) . ",
            spaceBetween: $spacing,
        },
        '@1.50': {
            slidesPerView: $columns,
            spaceBetween: " . $spacing * 1.5 . ",
        },
    },
    ";

    // This feature is mobile-first, so set columns for smallest screens
    $columns = round($columns / 4);
}

// Init lightbox modals with Swiper inside
if ($lightbox == 1) {
    $init = 'false';
    $initLightbox = "
    $('#gallery-$uid .ui.lightbox.image').click(function () {
        var idx = $(this).data('idx');
        var modalID = '#lightbox-$uid';
        var lazyLoadLightbox = new LazyLoad({
            elements_selector: modalID + ' .lazy'
        });

        $(modalID)
            .modal({
                onVisible: function() {
                    lazyLoadLightbox.loadAll();
                    lazyLoadInstance.update();
                }
            })
            .modal('show')
        ;
        $var.init();
        $var.slideTo(idx, 0, false);
    });
    ";
}

// Use different tpl chunk for mobile only JS
if ($mobile) {
    $init = 'true';
    $tpl = 'sliderMobileInitJS';
}

// Load assets in head and footer
// -----------------------------------------------------------------------------

// Check if minify assets setting is activated in Configuration
$minify = '';
if ($modx->getObject('cgSetting', array('key' => 'minify_css_js'))->get('value') == 1) {
    $minify = '.min';
}

// Paths
$assetsPathCSS = $modx->getOption('romanesco.semantic_css_path', $scriptProperties, '');
$assetsPathJS = $modx->getOption('romanesco.semantic_js_path', $scriptProperties, '');
$assetsPathVendor = $modx->getOption('romanesco.semantic_vendor_path', $scriptProperties, '');
$assetsPathDist = $modx->getOption('romanesco.semantic_dist_path', $scriptProperties, '');

// Load strings to insert in asset paths when cache busting is enabled
$cacheBusterCSS = $romanesco->getCacheBustingString('CSS');
$cacheBusterJS = $romanesco->getCacheBustingString('JS');

// Load component asynchronously if critical CSS is enabled
$async = '';
if ($romanesco->getConfigSetting('critical_css', $modx->resource->get('context_key'))) {
    $async = ' media="print" onload="this.media=\'all\'"';
}

// Head
$modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathCSS . '/swiper' . $minify . $cacheBusterCSS . '.css"' . $async . '>');

// Footer
$modx->regClientHTMLBlock('<script defer src="' . $assetsPathVendor . '/swiper/swiper-bundle.min' . $cacheBusterJS . '.js"></script>');
$modx->regClientHTMLBlock($modx->getChunk($tpl, array(
    'var' => $var,
    'id' => $id,
    'init' => $init,
    'cols' => $columns,
    'slides_to_scroll' => $scroll,
    'direction' => $direction,
    'spacing' => $spacing,
    'overflow' => $overflow ?? 'true',
    'loop' => $loop ?? 'false',
    'free' => $free ?? 'false',
    'center' => $center ?? 'false',
    'auto_height' => $autoHeight ?? 'false',
    'autoplay' => $autoplay ?? 'false',
    'keyboard' => $keyboard ?? 'false',
    'transition' => $transition,
    'pagination' => $pagination ?? '',
    'clickable' => $clickable,
    'breakpoints' => $breakpoints ?? '',
    'effects' => $effects[$transition] ?? '',
    'init_lightbox' => $initLightbox ?? '',
)));

// Load modal assets if lightbox is active
if ($lightbox == 1) {
    $modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/dimmer.min' . $cacheBusterCSS . '.css"' . $async . '>');
    $modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="' . $assetsPathDist . '/components/modal.min' . $cacheBusterCSS . '.css"' . $async . '>');
    $modx->regClientHTMLBlock('<script defer src="' . $assetsPathDist . '/components/dimmer.min' . $cacheBusterJS . '.js"></script>');
    $modx->regClientHTMLBlock('<script defer src="' . $assetsPathDist . '/components/modal.min' . $cacheBusterJS . '.js"></script>');
}

return '';