id: 100063
name: migxGetClassKeys
description: 'Return object class keys for images, links and notes based on MIGX config name. This way, EarthBrain grids for these objects can be reused in the other brain halves.'
category: E6_data
properties: 'a:0:{}'

-----

/**
 * Return object class keys based on MIGX config name.
 *
 * Some foreign invaders are included here, for the sake of productivity.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @return array;
 */

$classKeys = [
    'image' => '',
    'note' => '',
    'link' => '',
];

switch ($scriptProperties['config']) {
    case 'earthbrain_taxonomy:earthbrain':
        $classKeys = [
            'note' => 'earthNoteTaxon',
            'link' => 'earthLinkTaxon',
        ];
        break;
    case 'earthbrain_seeds:earthbrain':
        $classKeys = [
            'image' => 'earthImageSeed',
            'note' => 'earthNoteSeed',
        ];
        break;
    case 'earthbrain_plants:earthbrain':
    case 'forestbrain_plants:forestbrain':
        $classKeys = [
            'image' => 'earthImagePlant',
            'note' => 'earthNotePlant',
        ];
        break;
    case 'earthbrain_plantings:earthbrain':
        $classKeys = [
            'image' => 'earthImagePlanting',
            'note' => 'earthNotePlanting',
        ];
        break;
    case 'earthbrain_sources:earthbrain':
        $classKeys = [
            'image' => 'earthImageSource',
            'note' => 'earthNoteSource',
        ];
        break;
    case 'forestbrain_images:forestbrain':
        $classKeys = [
            'image' => 'forestImage',
        ];
        break;
    case 'forestbrain_features:forestbrain':
        $classKeys = [
            'image' => 'forestImageFeature',
        ];
        break;
    case 'forestbrain_components:forestbrain':
        $classKeys = [
            'image' => 'forestImageComponent',
        ];
        break;
    case 'forestbrain_writings:forestbrain':
        $classKeys = [
            'image' => 'forestImageWriting',
        ];
        break;
    case 'releafbrain_needs:releafbrain':
        $classKeys = [
            'image' => 'releafImageNeed',
            'note' => 'releafNoteNeed',
            'link' => 'releafLinkNeed',
        ];
        break;
    case 'releafbrain_offers:releafbrain':
        $classKeys = [
            'image' => 'releafImageOffer',
            'note' => 'releafNoteOffer',
            'link' => 'releafLinkOffer',
        ];
        break;
    case 'releafbrain_nodes:releafbrain':
        $classKeys = [
            'image' => 'releafImageNode',
            'note' => 'releafNoteNode',
            'link' => 'releafLinkNode',
        ];
        break;
}

return $classKeys;