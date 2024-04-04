id: 100021
name: migxSwitchDetailChunk
category: MIGX
properties: 'a:0:{}'

-----

//[[migxSwitchDetailChunk? &detailChunk=`detailChunk` &listingChunk=`listingChunk`]]


$properties['migx_id'] = $modx->getOption('migx_id',$_GET,'');

if (!empty($properties['migx_id'])){
    $output = $modx->getChunk($detailChunk,$properties);
}
else{
    $output = $modx->getChunk($listingChunk);
}

return $output;