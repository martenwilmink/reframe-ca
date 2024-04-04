id: 106
name: modifiedIf
description: 'Customized If snippet with additional ''contains'', ''containsnot'' and ''isnumeric'' operators, output to placeholder and option to prevent chunks from parsing before If statement is evaluated.'
category: f_framework
properties: 'a:2:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:34:"romanesco.modifiedif.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}s:14:"elementExample";a:7:{s:4:"name";s:14:"elementExample";s:4:"desc";s:35:"romanesco.modifiedif.elementExample";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * If
 *
 * Simple if (conditional) snippet.
 *
 * Copyright 2009-2010 by Jason Coward <jason@modx.com> and Shaun McCormick
 * <shaun@modx.com>
 *
 * If is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * If is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * If; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

if (!empty($debug)) {
    print_r($scriptProperties);
    if (!empty($die)) die();
}
$modx->parser->processElementTags('',$subject,true,true);

$output = '';
$operator = !empty($operator) ? $operator : '';
$operand = !isset($operand) ? '' : $operand;
if (isset($subject)) {
    if (!empty($operator)) {
        $operator = strtolower($operator);
        switch ($operator) {
            case '!=':
            case 'ne':
            case 'neq':
            case 'not':
            case 'isnot':
            case 'isnt':
            case 'unequal':
            case 'notequal':
                $output = (($subject != $operand) ? $then : (isset($else) ? $else : ''));
                break;
            case '<':
            case 'lt':
            case 'less':
            case 'lessthan':
                $output = (($subject < $operand) ? $then : (isset($else) ? $else : ''));
                break;
            case '>':
            case 'gt':
            case 'greater':
            case 'greaterthan':
                $output = (($subject > $operand) ? $then : (isset($else) ? $else : ''));
                break;
            case '<=':
            case 'lte':
            case 'lessthanequals':
            case 'lessthanorequalto':
                $output = (($subject <= $operand) ? $then : (isset($else) ? $else : ''));
                break;
            case '>=':
            case 'gte':
            case 'greaterthanequals':
            case 'greaterthanequalto':
                $output = (($subject >= $operand) ? $then : (isset($else) ? $else : ''));
                break;
            case 'isempty':
            case 'empty':
                $output = empty($subject) ? $then : (isset($else) ? $else : '');
                break;
            case '!empty':
            case 'notempty':
            case 'isnotempty':
                $output = !empty($subject) && $subject != '' ? $then : (isset($else) ? $else : '');
                break;
            case 'isnull':
            case 'null':
                $output = $subject == null || strtolower($subject) == 'null' ? $then : (isset($else) ? $else : '');
                break;
            case 'iselement':
            case 'element':
                if (empty($operand) && $operand == '') break;
                $operand = str_replace('mod','',$operand);
                $query = $modx->newQuery('mod'.ucfirst($operand), array(
                    $operand == 'template' ? 'templatename' : 'name' => $subject
                ));
                $query->select('id');
                $output = $modx->getValue($query->prepare()) ? $then : (isset($else) ? $else : '');
                break;
            case 'inarray':
            case 'in_array':
            case 'ia':
                $operand = explode(',',$operand);
                $output = in_array($subject,$operand) ? $then : (isset($else) ? $else : '');
                break;
            case 'containsnot':
            case 'includesnot':
                $output = strpos($subject,$operand) == false ? $then : (isset($else) ? $else : '');
                break;
            case 'contains':
            case 'includes':
                $output = strpos($subject,$operand) !== false ? $then : (isset($else) ? $else : '');
                break;
            case 'numeric':
            case 'isnumeric':
                $output = is_numeric($subject) !== false ? $then : (isset($else) ? $else : '');
                break;
            case '==':
            case '=':
            case 'eq':
            case 'is':
            case 'equal':
            case 'equals':
            case 'equalto':
            default:
                $output = (($subject == $operand) ? $then : (isset($else) ? $else : ''));
                break;
        }
    }
}
if (!empty($debug)) { var_dump($output); }
unset($subject);

// Prevent chunks or snippets from parsing before the If statement is evaluated.
// You can also use the mosquito technique, but that may cause issues in more complex scenarios.
$outputAsTpl = $modx->getOption('outputAsTpl', $scriptProperties, false);
if ($outputAsTpl) {
    $output = $modx->getChunk($output, $scriptProperties);
}

// Output either to placeholder, or directly
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
if ($toPlaceholder) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}
return $output;