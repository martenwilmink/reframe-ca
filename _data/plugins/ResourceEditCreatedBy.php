id: 100021
name: ResourceEditCreatedBy
category: E7_connexion
properties: 'a:0:{}'

-----

/**
 * ResourceEditCreatedBy
 *
 * Add a "created by" field to a resource form.
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * @event OnDocFormPrerender
 * @author Romain Tripault
 *
 * @link https://gist.github.com/rtripault/7306c8487a39fd1ce0db5f334c99be57
 */

$modx->controller->addHtml(<<<HTML
<script>
    // We are targeting the right column in the settings tab
    Ext.ComponentMgr.onAvailable('modx-page-settings-right', function(right) {
        right.on('beforerender', function() {
            // page is a reference to the whole form panel
            var page = Ext.getCmp('modx-panel-resource')
                // record is a reference to our resource fields
                ,record = page.record
            ;
            
            // Let's insert our new field at position 1
            right.insert(0,{
                xtype: 'modx-combo-user'
                ,name: 'createdby'
                ,hiddenName: 'createdby'
                ,value: record.createdby
                ,editable: true
                ,typeAhead: true
                ,anchor: '100%'
                ,layout: 'anchor'
                ,fieldLabel: 'Steward'
            });
        })
    });
</script>
HTML
);