id: 100024
name: filterbytag
category: MIGX
properties: 'a:0:{}'

-----

if (!is_array($subject)) {
    $subject = explode(',',str_replace(array('||',' '),array(',',''),$subject));
}

return (in_array($operand,$subject));