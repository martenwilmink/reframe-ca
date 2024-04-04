id: 100011
name: Hits
description: 'Overwrites the default snippet inside the Hits package. Contains a fix for preventing fatal errors in PHP 8.'
category: f_framework
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:28:"romanesco.hits.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:6:"review";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:29:"romanesco.hits.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * Hits for MODX Revolution
 *
 * INCLUDED HERE, TO OVERWRITE DEFAULT SNIPPET AND FIX PHP8.1 COMPATIBILITY.
 *
 * USAGE: (assumes a chunk named hitID contains "[[+hit_key]]")
 *
 * Get a comma separated list of ids of the 10 most visited pages 10 levels down from the web context
 * [[!Hits? &parents=`0` &depth=`10` &limit=`10` &outputSeparator=`,` &chunk=`hitID`]]
 *
 * Get a comma seperated list of ids of the 4 least visited pages that are children of resource 2 and set results to a placeholder
 * [[!Hits? &parents=`2` limit=`4` &dir=`ASC`  &outputSeparator=`,` &chunk=`hitID` &toPlaceholder=`hits`]]
 *
 * Record a hit for resource 3
 * [[!Hits? &punch=`3`]]
 *
 * Record 20 hit for resource 4
 * [[!Hits? &punch=`4` &amount=`20`]]
 *
 * Remove 4 hit from resource 5
 * [[!Hits? &punch=`5` &amount=`-4`]]
 *
 * Get the four most hit resources, excluding the first
 * [[!Hits? &parents=`0` &limit=`4` &offset=`1` &outputSeparator=`,`]]
 *
 * Knockout resource 3 then add 2 hits (knockout zeros value before adding punches)
 * [[!Hits? &punch=`3` &amount=`2` &knockout=`1`]]
 *
 * @package Hits
 * @var modX $modx
 * @var array $scriptProperties
 * @var string $input
 * @var string $options
 __          __
/\ \      __/\ \__          	    come to us for your dirty work
\ \ \___ /\_\ \ ,_\   ____  		created by:
 \ \  _ `\/\ \ \ \/  /',__\ 		JP DeVries @jpdevries
  \ \ \ \ \ \ \ \ \_/\__, `\		YJ Tso @sepiariver
   \ \_\ \_\ \_\ \__\/\____/		Jason Coward @drumshaman
    \/_/\/_/\/_/\/__/\/__*/


// get the hit service
$defaultHitsCorePath = $modx->getOption('core_path').'components/hits/';
$hitsCorePath = $modx->getOption('hits.core_path',null,$defaultHitsCorePath);
$hitService = $modx->getService('hits','Hits',$hitsCorePath.'model/hits/',$scriptProperties);

if (!($hitService instanceof Hits)) return 'failed'; // you'll need another fool to do your dirty work

// setup default properties
$punch = $modx->getOption('punch',$scriptProperties,null);
(integer)$amount = $modx->getOption('amount',$scriptProperties,1);
$sort = $modx->getOption('sort',$scriptProperties,'hit_count');
$dir = $modx->getOption('dir',$scriptProperties,'DESC');
$parents = $modx->getOption('parents',$scriptProperties,null);
$hit_keys = explode(',',$modx->getOption('hit_keys',$scriptProperties,null));
$tpl = $modx->getOption('tpl',$scriptProperties,'hitTpl');
$limit = $modx->getOption('limit',$scriptProperties,5);
(integer)$depth = $modx->getOption('depth',$scriptProperties,10);
$outputSeparator = $modx->getOption('outputSeparator',$scriptProperties,"\n");
$toPlaceholder = $modx->getOption('toPlaceholder',$scriptProperties,"");
$offset = isset($offset) ? (integer) $offset : 0;
$knockout = (bool)$modx->getOption('knockout',$scriptProperties,false);

if (trim($parents) == '0') $parents = array(0); // i know, i know (and I hear ya)
else if ($parents) $parents = explode(',', $parents);

if ($depth < 1) $depth = 1;

// don't just go throwing punches blindly, only store a page hit if told to do so
if ($punch && $amount) {
    $hit = $modx->getObject('Hit', array(
        'hit_key' => $punch
    ));

    if ($hit) {
        // increment the amount
        $hit->set('hit_count', ($knockout ? 0 : (integer)$hit->get('hit_count')) + $amount);
    } else {
        // create a new hit record
        $hit = $modx->newObject('Hit');
        $hit->fromArray(array(
            'hit_key' => $punch,
            'hit_count' => $amount
        ));
    }
    $hit->save();
}

$s = '';

// create an array of child ids to compare hits
$hits = array();
$childIds = array();
if (is_array($parents)) { // don't use count here, because it throws a mean 500 in PHPunch 8
    foreach ($parents as $parent) {
        $childIds = array_merge($childIds, $modx->getChildIds($parent, $depth));
    }
    $childIds = array_unique($childIds);
    $hits = $hitService->getHits($childIds, $sort, $dir, $limit, $offset);
}

if (!is_null($hit_keys)) {
    $hit_keys = array_diff($hit_keys, $childIds);
    $hits = array_merge($hits, $hitService->getHits($hit_keys, $sort, $dir, $limit, $offset));
}

$hs = $hitService->processHits($hits, $tpl);
$s = implode($outputSeparator, $hs);

// would you like that for here or to go?
if ($toPlaceholder) {
    $modx->setPlaceholder($toPlaceholder, $s);
    return;
}

return $s;