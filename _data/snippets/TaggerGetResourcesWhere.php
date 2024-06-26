id: 100054
name: TaggerGetResourcesWhere
category: Tagger
properties: 'a:6:{s:4:"tags";a:7:{s:4:"name";s:4:"tags";s:4:"desc";s:34:"tagger.getresourceswhere.tags_desc";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:17:"tagger:properties";s:4:"area";s:0:"";}s:6:"groups";a:7:{s:4:"name";s:6:"groups";s:4:"desc";s:36:"tagger.getresourceswhere.groups_desc";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:17:"tagger:properties";s:4:"area";s:0:"";}s:5:"where";a:7:{s:4:"name";s:5:"where";s:4:"desc";s:35:"tagger.getresourceswhere.where_desc";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:17:"tagger:properties";s:4:"area";s:0:"";}s:14:"likeComparison";a:7:{s:4:"name";s:14:"likeComparison";s:4:"desc";s:44:"tagger.getresourceswhere.likeComparison_desc";s:4:"type";s:11:"numberfield";s:7:"options";s:0:"";s:5:"value";s:1:"0";s:7:"lexicon";s:17:"tagger:properties";s:4:"area";s:0:"";}s:8:"tagField";a:7:{s:4:"name";s:8:"tagField";s:4:"desc";s:38:"tagger.getresourceswhere.tagField_desc";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"alias";s:7:"lexicon";s:17:"tagger:properties";s:4:"area";s:0:"";}s:8:"matchAll";a:7:{s:4:"name";s:8:"matchAll";s:4:"desc";s:38:"tagger.getresourceswhere.matchAll_desc";s:4:"type";s:11:"numberfield";s:7:"options";s:0:"";s:5:"value";s:1:"0";s:7:"lexicon";s:17:"tagger:properties";s:4:"area";s:0:"";}}'

-----

/**
 * TaggerGetResourcesWhere
 *
 * DESCRIPTION
 *
 * This snippet generate SQL Query that can be used in WHERE condition in getResources snippet
 *
 * PROPERTIES:
 *
 * &tags            string  optional    Comma separated list of Tags for which will be generated a Resource query. By default Tags from GET param will be loaded
 * &groups          string  optional    Comma separated list of Tagger Groups. Only from those groups will Tags be allowed
 * &where           string  optional    Original getResources where property. If you used where property in your current getResources call, move it here
 * &likeComparison  int     optional    If set to 1, tags will compare using LIKE
 * &tagField        string  optional    Field that will be used to compare with given tags. Default: alias
 * &matchAll        int     optional    If set to 1, resource must have all specified tags. Default: 0
 * &errorOnInvalidTags bool optional    If set to true, will 404 on an invalid tag name request. Default: false
 * &field           string  optional    modResource field that will be used to compare with assigned resource ID
 *
 * USAGE:
 *
 * [[!getResources? &where=`[[!TaggerGetResourcesWhere? &tags=`Books,Vehicles` &where=`{"isfolder": 0}`]]`]]
 *
 */

$tagger = $modx->getService('tagger','Tagger',$modx->getOption('tagger.core_path',null,$modx->getOption('core_path').'components/tagger/').'model/tagger/',$scriptProperties);
if (!($tagger instanceof Tagger)) return '';

$tags = $modx->getOption('tags', $scriptProperties, '');
$where = $modx->getOption('where', $scriptProperties, '');
$tagField = $modx->getOption('tagField', $scriptProperties, 'alias');
$likeComparison = (int) $modx->getOption('likeComparison', $scriptProperties, 0);
$matchAll = (int) $modx->getOption('matchAll', $scriptProperties, 0);
$errorOnInvalidTags = (int) $modx->getOption('errorOnInvalidTags', $scriptProperties, 0);
$field = $modx->getOption('field', $scriptProperties, 'id');
$where = $modx->fromJSON($where);
if ($where == false) {
    $where = array();
}

$tagsCount = 0;

if ($tags == '') {
    $gc = $modx->newQuery('TaggerGroup');
    $gc->select($modx->getSelectColumns('TaggerGroup', '', '', array('alias')));

    $groups = $modx->getOption('groups', $scriptProperties, '');
    $groups = $tagger->explodeAndClean($groups);
    if (!empty($groups)) {
        $gc->where(array(
            'name:IN' => $groups,
            'OR:alias:IN' => $groups,
            'OR:id:IN' => $groups,
        ));
    }

    $gc->prepare();
    $gc->stmt->execute();
    $groups = $gc->stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $conditions = array();
    foreach ($groups as $group) {
        if (isset($_GET[$group])) {
            $groupTags = $tagger->explodeAndClean($_GET[$group]);
            if (!empty($groupTags)) {
                $like = array('AND:alias:IN' => $groupTags);

                if ($likeComparison == 1) {
                    foreach ($groupTags as $tag) {
                        $like[] = array('OR:alias:LIKE' => '%' . $tag . '%');
                    }
                }

                $conditions[] = array(
                    'OR:Group.alias:=' => $group,
                    $like
                );
                $tagsCount += count($groupTags);
            }
        }
    }

    if (count($conditions) == 0) {
        return $modx->toJSON($where);
    }

    $c = $modx->newQuery('TaggerTag');
    $c->leftJoin('TaggerGroup', 'Group');

    $c->where($conditions);
} else {
    $tags = $tagger->explodeAndClean($tags);

    if (empty($tags)) {
        return $modx->toJSON($where);
    }

    $tagsCount = count($tags);

    $groups = $modx->getOption('groups', $scriptProperties, '');

    $groups = $tagger->explodeAndClean($groups);

    $c = $modx->newQuery('TaggerTag');
    $c->select($modx->getSelectColumns('TaggerTag', 'TaggerTag', '', array('id')));

    $compare = array(
        $tagField . ':IN' => $tags
    );

    if ($likeComparison == 1) {
        foreach ($tags as $tag) {
            $compare[] = array('OR:' . $tagField . ':LIKE' => '%' . $tag . '%');
        }
    }

    $c->where($compare);

    if (!empty($groups)) {
        $c->leftJoin('TaggerGroup', 'Group');
        $c->where(array(
            'Group.id:IN' => $groups,
            'OR:Group.name:IN' => $groups,
            'OR:Group.alias:IN' => $groups,
        ));
    }
}

$c->prepare();
$c->stmt->execute();
$tagIDs = $c->stmt->fetchAll(PDO::FETCH_COLUMN, 0);

if (count($tagIDs) == 0) {
    $tagIDs[] = 0;
    if($errorOnInvalidTags == 1)
        $modx->sendForward($modx->getOption('error_page'),array('response_code' => 'HTTP/1.1 404 Not Found'));
}

if ($matchAll == 0) {
    $where[] = "EXISTS (SELECT 1 FROM {$modx->getTableName('TaggerTagResource')} r WHERE r.tag IN (" . implode(',', $tagIDs) . ") AND r.resource = modResource." . $field . ")";
} else {
    $where[] = "EXISTS (SELECT 1 as found FROM {$modx->getTableName('TaggerTagResource')} r WHERE r.tag IN (" . implode(',', $tagIDs) . ") AND r.resource = modResource." . $field . " GROUP BY found HAVING count(found) = " . $tagsCount . ")";
}

return $modx->toJSON($where);