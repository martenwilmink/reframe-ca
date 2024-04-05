id: 100021
name: ResourceEditCreatedBy
category: E7_connexion
plugincode: "/**\n * ResourceEditCreatedBy\n *\n * Add a \"created by\" field to a resource form.\n *\n * @var modX $modx\n * @var array $scriptProperties\n *\n * @event OnDocFormPrerender\n * @author Romain Tripault\n *\n * @link https://gist.github.com/rtripault/7306c8487a39fd1ce0db5f334c99be57\n */\n\n$modx->controller->addHtml(<<<HTML\n<script>\n    // We are targeting the right column in the settings tab\n    Ext.ComponentMgr.onAvailable('modx-page-settings-right', function(right) {\n        right.on('beforerender', function() {\n            // page is a reference to the whole form panel\n            var page = Ext.getCmp('modx-panel-resource')\n                // record is a reference to our resource fields\n                ,record = page.record\n            ;\n            \n            // Let's insert our new field at position 1\n            right.insert(0,{\n                xtype: 'modx-combo-user'\n                ,name: 'createdby'\n                ,hiddenName: 'createdby'\n                ,value: record.createdby\n                ,editable: true\n                ,typeAhead: true\n                ,anchor: '100%'\n                ,layout: 'anchor'\n                ,fieldLabel: 'Steward'\n            });\n        })\n    });\n</script>\nHTML\n);"
properties: 'a:0:{}'
static: 1
static_file: '[[++earthbrain.core_path]]elements/plugins/e7_computations/e7_connexion/resourceeditcreatedby.plugin.php'

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