id: 100063
name: migxGetClassKeys
description: 'Return object class keys for images, links and notes based on MIGX config name. This way, EarthBrain grids for these objects can be reused in the other brain halves.'
category: E6_data
snippet: "/**\n * Return object class keys based on MIGX config name.\n *\n * Some foreign invaders are included here, for the sake of productivity.\n *\n * @var modX $modx\n * @var array $scriptProperties\n * @return array;\n */\n\n$classKeys = [\n    'image' => '',\n    'note' => '',\n    'link' => '',\n];\n\nswitch ($scriptProperties['config']) {\n    case 'earthbrain_taxonomy:earthbrain':\n        $classKeys = [\n            'note' => 'earthNoteTaxon',\n            'link' => 'earthLinkTaxon',\n        ];\n        break;\n    case 'earthbrain_seeds:earthbrain':\n        $classKeys = [\n            'image' => 'earthImageSeed',\n            'note' => 'earthNoteSeed',\n        ];\n        break;\n    case 'earthbrain_plants:earthbrain':\n    case 'forestbrain_plants:forestbrain':\n        $classKeys = [\n            'image' => 'earthImagePlant',\n            'note' => 'earthNotePlant',\n        ];\n        break;\n    case 'earthbrain_plantings:earthbrain':\n        $classKeys = [\n            'image' => 'earthImagePlanting',\n            'note' => 'earthNotePlanting',\n        ];\n        break;\n    case 'earthbrain_sources:earthbrain':\n        $classKeys = [\n            'image' => 'earthImageSource',\n            'note' => 'earthNoteSource',\n            'link' => 'earthLinkSource',\n        ];\n        break;\n    case 'earthbrain_exchanges:earthbrain':\n        $classKeys = [\n            'image' => 'earthImageExchange',\n            'note' => 'earthNoteSource',\n        ];\n        break;\n    case 'forestbrain_images:forestbrain':\n        $classKeys = [\n            'image' => 'forestImage',\n        ];\n        break;\n    case 'forestbrain_features:forestbrain':\n        $classKeys = [\n            'image' => 'forestImageFeature',\n        ];\n        break;\n    case 'forestbrain_components:forestbrain':\n        $classKeys = [\n            'image' => 'forestImageComponent',\n        ];\n        break;\n    case 'forestbrain_writings:forestbrain':\n        $classKeys = [\n            'image' => 'forestImageWriting',\n        ];\n        break;\n    case 'releafbrain_needs:releafbrain':\n        $classKeys = [\n            'image' => 'releafImageNeed',\n            'note' => 'releafNoteNeed',\n            'link' => 'releafLinkNeed',\n        ];\n        break;\n    case 'releafbrain_offers:releafbrain':\n        $classKeys = [\n            'image' => 'releafImageOffer',\n            'note' => 'releafNoteOffer',\n            'link' => 'releafLinkOffer',\n        ];\n        break;\n    case 'releafbrain_nodes:releafbrain':\n        $classKeys = [\n            'image' => 'releafImageNode',\n            'note' => 'releafNoteNode',\n            'link' => 'releafLinkNode',\n        ];\n        break;\n}\n\nreturn $classKeys;"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/snippets/e6_formulas/e6_data/migxgetclasskeys.snippet.php'

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
            'link' => 'earthLinkSource',
        ];
        break;
    case 'earthbrain_exchanges:earthbrain':
        $classKeys = [
            'image' => 'earthImageExchange',
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