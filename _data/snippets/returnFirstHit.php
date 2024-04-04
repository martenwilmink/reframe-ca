id: 149
name: returnFirstHit
description: 'Feed it a bunch of properties, and it spits out the first one that''s not empty. Property names are irrelevant. Sort order is all that matters.'
category: f_basic
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:38:"romanesco.returnfirsthit.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:39:"romanesco.returnfirsthit.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * returnFirstHit snippet
 *
 * Feed it a bunch of properties, and it spits out the first one that's not empty.
 * Property names are irrelevant. Sort order is all that matters.
 *
 * [[!returnFirstHit?
 *     &1=`[[+redirect_id]]`
 *     &2=`[[+next_step]]`
 *     &3=`[[*fb_redirect_dynamic]]`
 *     &4=`[[*fb_redirect_id]]`
 *     &default=`Nothing there!`
 * ]]
 *
 * @var array $scriptProperties
 */

// Avoid hitting snippet properties
unset($scriptProperties['elementExample']);
unset($scriptProperties['elementStatus']);

foreach ($scriptProperties as $key => $value) {
    if ($value) return $value;
}
return '';