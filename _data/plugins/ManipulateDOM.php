id: 39
name: ManipulateDOM
description: 'Manipulate DOM elements with HtmlPageDom. Yes, that''s exactly what jQuery does... But now we can do it server side, before the page is rendered. Much faster and more reliable.'
category: c_content
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:37:"romanesco.manipulatedom.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * ManipulateDOM plugin
 *
 * This plugin utilizes HtmlPageDom, a page crawler that can manipulate DOM
 * elements for us. Yes, that is exactly what jQuery does... But now we can do
 * it server side, before the page is rendered. Much faster and more reliable.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @package romanesco
 */

if (!class_exists(\Wa72\HtmlPageDom\HtmlPageCrawler::class)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[HtmlPageDom] Class not found!');
    return;
}

use \Wa72\HtmlPageDom\HtmlPageCrawler;

switch ($modx->event->name) {
    case 'OnWebPagePrerender':

        // Check if content type is text/html
        if (!in_array($modx->resource->get('content_type'), [1,11])) {
            break;
        }

        // Get processed output of resource
        $output = &$modx->resource->_output;

        // Feed output to HtmlPageDom
        $dom = new HtmlPageCrawler($output);

        // Add non-white class to body if custom background is set
        try {
            if ($modx->getObject('cgSetting', array('key' => 'theme_page_background_color'))->get('value') !== 'ffffff') {
                $dom->filter('body')->addClass('non-white');
            }
        }
        catch (Error $e) {
            $modx->log(modX::LOG_LEVEL_ERROR, $e);
        }

        // Read inverted parameter from URL (for testing purposes)
        $invertLayouts = $_GET['inverted'] ?? 0;
        if ($invertLayouts) {
            $dom->filter('.ui.menu')
                ->addClass('inverted')
            ;
            $dom->filter('.vertical.stripe.segment.white')
                ->removeClass('white')
                ->addClass('inverted')
            ;
            $dom->filter('.vertical.backyard.segment.secondary')
                ->removeClass('secondary')
                ->addClass('inverted black')
            ;
        }

        // Add header classes to HTML headings
        $dom->filter('h1:not(.header):not(.title)')->addClass('ui header');
        $dom->filter('h2:not(.header):not(.title)')->addClass('ui header');
        $dom->filter('h3:not(.header):not(.title)')->addClass('ui header');
        $dom->filter('h4:not(.header):not(.title)')->addClass('ui header');
        $dom->filter('h5:not(.header):not(.title)')->addClass('ui header');

        // Inject inverted classes to elements inside inverted segments
        $dom->filter('.inverted.segment')
            ->each(function (HtmlPageCrawler $segment) {

                // Define elements that need to receive the inverted class
                $elements = array(
                    '.header',
                    '.grid',
                    'a:not(.button)',
                    '.button:not(.primary):not(.secondary)',
                    '.subtitle',
                    '.lead',
                    '.list',
                    '.quote',
                    '.divider:not(.hidden)',
                    '.accordion:not(.styled)',
                    '.text.menu',
                    '.secondary.menu',
                    '.basic.form',
                    '.basic.segment',
                    '.table',
                    '.steps',
                );

                // Revert inverted styling inside these nested elements
                $exceptions = array(
                    '.segment:not(.inverted):not(.transparent)',
                    '.card',
                    '.message',
                    '.accordion:not(.inverted)',
                    '.popup:not(.inverted)',
                    '.tabbed.menu',
                    '.form:not(.basic)',
                    '.leaflet-container',
                );

                // Prevent elements from having the same color as their parent background
                if ($segment->hasClass('primary-color') || $segment->hasClass('secondary-color') || $segment->hasClass('secondary')) {
                    $segment
                        ->filter('.primary.button')
                        ->addClass('white inverted')
                    ;
                    $segment
                        ->filter('.secondary.button')
                        ->removeClass('secondary')
                        ->addClass('inverted')
                    ;
                    $segment
                        ->filter('.bottom.attached.primary.button')
                        ->removeClass('primary')
                        ->addClass('secondary')
                    ;
                }

                // Elements
                foreach ($elements as $element) {
                    $segment
                        ->filter($element)
                        ->addClass('inverted')
                    ;
                }

                // Exceptions
                foreach ($exceptions as $exception) {
                    $segment
                        ->filter($exception)
                        ->each(function(HtmlPageCrawler $node) {
                            $node
                                ->filter('.inverted')
                                ->removeClass('inverted')
                            ;
                            $node
                                ->filter('.ui.white.button')
                                ->removeClass('white')
                            ;
                        })
                    ;
                }
            })
        ;

        // Remove rows from grids that have a reversed column order on mobile
        $dom->filter('.ui.reversed.grid > .row')->unwrapInner();

        // If grids are stackable on tablet, also hide (or show) designated mobile elements
        $dom->filter('.ui[class*="stackable on tablet"].grid [class*="mobile hidden"]')
            ->removeClass('mobile')
            ->removeClass('hidden')
            ->addClass('tablet or lower hidden')
        ;
        $dom->filter('.ui[class*="stackable on tablet"].grid [class*="mobile only"]')
            ->addClass('tablet only')
        ;

        // Responsive image sizes might be incorrect in responsive grids
        $dom->filter('.ui.stackable.grid, .ui.doubling.grid, .ui.stackable.cards')
            ->each(function(HtmlPageCrawler $grid) {
                $targetImg = '.row > .column > .ui.image > img, .column > .ui.image > img';
                if ($grid->matches('.cards')) {
                    $targetImg = '.card > .image > img';
                }

                // Tag images in stackable on tablet and two column doubling grids
                if ($grid->matches('[class*="stackable on tablet"]') || $grid->matches('[class*="two column"].doubling')) {
                    $grid->children($targetImg)->addClass('tablet-expand-full');
                }
                // Do the same for doubling grids with more than two columns
                else if ($grid->matches('.doubling:not([class*="two column"]):not([class*="equal width"])')) {
                    $grid->children($targetImg)->addClass('tablet-expand-half');

                    if ($grid->matches('.doubling:not(.stackable)')) {
                        $grid->children($targetImg)->addClass('mobile-expand-half');
                    }
                }

                // Only target direct descendants
                $grid->children($targetImg)
                    ->each(function(HtmlPageCrawler $img) {
                        $dataSizes = $img->getAttribute('data-sizes');
                        $sizes = $dataSizes ?? $img->getAttribute('sizes');

                        if (!$sizes) return;

                        // If lazy load is enabled, sizes are stored in data-sizes
                        $attribute = 'sizes';
                        if ($dataSizes) $attribute = 'data-sizes';

                        // Set mobile breakpoints to 100vw, because stacked means full width
                        $stackedSizes = preg_replace('/\(min-width: 360px\).+/','(min-width: 360px) 100vw,', $sizes);
                        $stackedSizes = preg_replace('/\(max-width: 359px\).+/','(max-width: 359px) 100vw', $stackedSizes);

                        // Set optional sizes, if indicated
                        if ($img->matches('.tablet-expand-full')) {
                            $stackedSizes = preg_replace('/\(min-width: 768px\).+/','(min-width: 768px) 100vw,', $stackedSizes);
                        }
                        if ($img->matches('.tablet-expand-half')) {
                            $stackedSizes = preg_replace('/\(min-width: 768px\).+/','(min-width: 768px) 50vw,', $stackedSizes);
                        }
                        if ($img->matches('.mobile-expand-half')) {
                            $stackedSizes = preg_replace('/\(min-width: 360px\).+/','(min-width: 360px) 50vw,', $stackedSizes);
                            $stackedSizes = preg_replace('/\(max-width: 359px\).+/','(max-width: 359px) 50vw', $stackedSizes);
                        }

                        $img->setAttribute($attribute, $stackedSizes);
                    })
                ;
            })
        ;

        // Add class to empty grid columns
        $dom->filter('.ui.grid .column')
            ->each(function(HtmlPageCrawler $column) {
                if($column->getInnerHtml() === '') {
                    $column->addClass('empty');
                }
            })
        ;

        // Add column class to nested grids
        //
        // If a nested grid contains multiple columns, these columns are
        //  arranged according to their size, combined with the 'equal width'
        //  classes on the grid container. This works well in most cases, but
        //  some grids don't scale down nicely on tablet / computer breakpoints
        //  because the parent doesn't know how many columns it should count on.
        // This addition sets these classes on the parent by counting the number
        //  of columns being parsed.
        // Only applies to nested grids containing a single row, as different
        //  column counts can be applied to multiple rows in the same grid.
        $dom->filter('.ui.nested.equal.width.grid')
            ->each(function(HtmlPageCrawler $grid) {
                $firstRow = $grid->filter('.row')->first()->filter('.column');
                $allRows = $grid->filter('.column');

                // Only operate on grids with a single row
                if ($firstRow->length == $allRows->length) {
                    $columns = $firstRow->length;
                    switch ($columns) {
                        case 4:
                            $grid->addClass('four column');
                            break;
                        case 3:
                            $grid->addClass('three column');
                            break;
                        case 2:
                            $grid->addClass('two column');
                            break;
                    }
                }
            })
        ;

        // Display bullets above list items in centered lists
        $dom->filter('.ui.center.aligned')
            ->each(function(HtmlPageCrawler $container) {
                $container->filter('.ui.list')->addClass('vertical');
                $container->filter('.aligned:not(.center) .ui.vertical.list')->removeClass('vertical');
            })
        ;

        // Turn HR into divider
        $dom->filter('hr')
            ->addClass('ui divider')
        ;

        // Make regular divider headers smaller
        $dom->filter('span.ui.divider.header')
            ->addClass('tiny')
        ;

        // Place accordion icons right of title
        $dom->filter('#content .ui.accordion .title > .icon')
            ->addClass('right')
        ;

        // Transform regular tables into SUI tables
        $dom->filter('table:not(.ui.table)')
            ->addClass('ui table')
        ;

        // Apply Swiper classes to appropriate slide elements
        $dom->filter('.swiper')
            ->each(function (HtmlPageCrawler $slider) {
                $slider
                    ->children('.nested.overview')
                    ->removeClass('stackable')
                    ->removeClass('doubling')
                    ->addClass('swiper-wrapper')
                ;
                $slider
                    ->children('.gallery')
                    ->addClass('swiper-wrapper')
                ;
                $slider
                    ->children('.cards')
                    ->addClass('swiper-wrapper')
                ;
                $slider
                    ->children('.swiper-wrapper > *')
                    ->each(function (HtmlPageCrawler $slide) {
                        if ($slide->hasClass('card')) {
                            $slide
                                ->addClass('ui fluid')
                                ->wrap('<div class="swiper-slide"></div>')
                            ;
                        }
                        elseif ($slide->hasClass('image')) {
                            $slide
                                ->removeClass('content')
                                ->removeClass('rounded')
                                ->addClass('swiper-slide')
                            ;
                        }
                        else {
                            $slide->addClass('swiper-slide');
                        }
                    })
                ;
                // Move prev/next buttons out of container
                // No longer used, but kept here as reference for how to find parent elements
                //$slider->parents()->each(function (HtmlPageCrawler $parent) {
                //    if ($parent->hasClass('nested','slider')) {
                //        $parent->filter('.swiper-button-prev')->appendTo($parent);
                //        $parent->filter('.swiper-button-next')->appendTo($parent);
                //    }
                //});
            })
        ;

        // Fill lightbox with gallery images
        $lightbox = array();
        $lightbox =
            $dom->filter('.gallery.with.lightbox')
                ->each(function (HtmlPageCrawler $gallery) {
                    global $modx;

                    // Grab images sources from data attributes
                    $images =
                        $gallery
                            ->filter('.lightbox > img')
                            ->each(function (HtmlPageCrawler $img) {
                                global $modx;
                                return $modx->getChunk('galleryRowImageLightbox', array(
                                    'src' => $img->attr('data-lightbox-img'),
                                    'caption' => $img->attr('data-caption'),
                                    'title' => $img->attr('alt'),
                                    'classes' => 'swiper-slide',
                                ));
                            })
                    ;

                    // Create lightbox for each gallery
                    return $modx->getChunk('lightboxOuter', array(
                        'uid' => $gallery->attr('data-uid'),
                        'output' => implode($images),
                    ));
                })
        ;

        // Add lightbox to HTML
        $dom->filter('#footer')
            ->after(implode($lightbox))
        ;

        // Manipulate images / SVGs
        $dom->filter('.ui.image, .ui.svg.image > svg, .ui.svg.image > img')
            ->each(function (HtmlPageCrawler $img) {
                $width = $img->getAttribute('width');
                $height = $img->getAttribute('height');

                // Remove empty width & height
                if (!$width) $img->removeAttr('width');
                if (!$height) $img->removeAttr('height');
            })
        ;

        // Fix inline form fields
        $dom->filter('.ui.form .inline.fields > .field')
            ->removeClass('horizontal')
            ->removeClass('vertical')
            ->filter('label')
            ->each(function(HtmlPageCrawler $label) {
                if($label->getInnerHtml() === '') {
                    $label->addClass('hidden');
                }
            })
        ;
        $dom->filter('.ui.form .inline.fields > .wide.field > .dropdown')
            ->addClass('fluid')
        ;

        // Format inline (equal width) forms
        // Counter to what you'd expect, the fields in this form shouldn't have
        //  class inline. So they need to be removed, and the fields need to be
        //  wrapped in a .fields container.
        // Special treatment for the submit button: it can be positioned inline
        //  via CB settings, after which it's inserted after the last form field.
        $dom->filter('form[id*="form-"].equal.width')
            ->each(function (HtmlPageCrawler $form) {
                $form
                    ->filter('fieldset:not(:last-child)')
                    ->wrapInner('<div class="fields"></div>')
                    ->filter('.field')
                    ->removeClass('inline')
                ;
                $form
                    ->filter('fieldset.submission')
                    ->removeClass('submission')
                    ->filter('input[type="submit"].inline')
                    ->appendTo($form->filter('fieldset .fields')->last())
                    ->wrap('<div class="compact submission field">')
                    ->before('<label>')
                ;
            })
        ;

        // Disable steps following an active step
        $dom->filter('.ui.consecutive.steps .active.step')
            ->each(function (HtmlPageCrawler $step) {
                $step
                    ->nextAll()
                    ->addClass('disabled')
                ;
            })
        ;
        // Mark previous steps as completed
        $dom->filter('.ui.completable.steps .active.step')
            ->each(function (HtmlPageCrawler $step) {
                $step
                    ->previousAll()
                    ->addClass('completed')
                ;
            })
        ;
        // Completed steps can't be disabled
        $dom->filter('.ui.steps .completed.step')->removeClass('disabled');

        // Make sure AjaxUpload scripts are run after jQuery is loaded
        $dom->filter('script')
            ->each(function (HtmlPageCrawler $script) {
                $src = $script->getAttribute('src');
                $code = $script->getInnerHtml();

                // Defer loading of AjaxUpload JS file
                if (strpos($src,'ajaxupload') !== false) {
                    $script->setAttribute('defer','');
                }

                // Wait for DOMContentLoaded event instead of using document.ready
                if (strpos($code,'$(document).ready') !== false) {
                    $code = str_replace('/* <![CDATA[ */', '', $code);
                    $code = str_replace('/* ]]> */', '', $code);

                    $script->setInnerHtml(
                        str_replace(
                            '$(document).ready(function ()',
                            'window.addEventListener(\'DOMContentLoaded\', function()',
                            $code
                        )
                    );
                }
            })
        ;

        // Fix ID conflicts in project hub
        $dom->filter('#hub .pattern.segment#content')->setAttribute('id','content-global');
        $dom->filter('#hub .pattern.segment#css')->setAttribute('id','css-global');
        $dom->filter('#hub .pattern.segment#footer')->setAttribute('id','footer-global');
        $dom->filter('#hub .pattern.segment#head')->setAttribute('id','head-global');
        $dom->filter('#hub .pattern.segment#script')->setAttribute('id','script-global');

        // Change links to fixed IDs
        $dom->filter('#hub .pattern.segment .list a.item')
            ->each(function (HtmlPageCrawler $link) {
                $href = $link->getAttribute('href');
                switch ($href) {
                    case ($href == 'patterns/organisms#content'):
                    case ($href == 'patterns/organisms#css'):
                    case ($href == 'patterns/organisms#footer'):
                    case ($href == 'patterns/organisms#head'):
                    case ($href == 'patterns/organisms#script'):
                        $link->setAttribute('href', $href . '-global');
                        break;
                }
            })
        ;

        // Save manipulated DOM
        $output = $dom->saveHTML();

        break;
}