id: 33
name: setResourceGroup
description: 'Add resource to a specific group, based on certain conditions or variables.'
category: c_content
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:40:"romanesco.setresourcegroup.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * setResourceGroup
 *
 * Add resource to a specific group, based on certain conditions or variables.
 *
 * @var modX $modx
 * @package romanesco
 */

switch ($modx->event->name) {
    case 'OnDocFormSave':
        /**
         * @var modResource $resource
         */

        $resourceGroup = $modx->getObject('modResourceGroup',1);

        if (!is_object($resourceGroup)) {
            $modx->log(modX::LOG_LEVEL_INFO, '[setResourceGroup] No resource group was found.');
            break;
        } else {
            $resourceGroupName = $resourceGroup->get('name');
        }

        // Tickets
        if ($resource->get('class_key','Ticket')) {

            // If resource is a private ticket, add it to the KB private resource group
            if ($resource->get('privateweb') && !$resource->isMember($resourceGroupName)) {
                $resource->joinGroup(1);
                $modx->log(modX::LOG_LEVEL_INFO, '[setResourceGroup] Resource "' . $resource->get('pagetitle') . '" joined resource group: ' . $resourceGroupName);
            }

            // If the ticket is not private, remove it from the resource group
            if (!$resource->get('privateweb') && $resource->isMember($resourceGroup->get('name'))) {
                $resource->leaveGroup(1);
                $modx->log(modX::LOG_LEVEL_INFO, '[setResourceGroup] Resource "' . $resource->get('pagetitle') . '" left resource group: ' . $resourceGroupName);
            }
        }

        break;
}