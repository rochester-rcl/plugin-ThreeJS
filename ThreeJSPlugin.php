<?php
/**
* ThreeViewer is used to view 3d models in Omeka
*
*/

/**
*
* ThreeViewer plugin
* @package Omeka\Plugins\ThreeViewer
*/

require_once dirname(__FILE__) . '/helpers/ThreeViewerFunctions.php';
$appRoot = getcwd();
define('THREE_VIEWER_ROOT', dirname(__FILE__));

class ThreeJSPlugin extends Omeka_Plugin_AbstractPlugin
{
  protected $_hooks = array(
    'install',
    'uninstall',
    'initialize',
    'define_acl',
  );

  protected $_filters = array(
    'admin_items_form_tabs',
    'api_resources',
  );

  public function hookInstall()
  {
      $db = $this->_db;
      $initViewers = "
      CREATE TABLE IF NOT EXISTS `{$db->prefix}three_js_viewers` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `item_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
      `three_file_id` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
      `model_units` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
      `background_url` varchar(500) COLLATE utf8_unicode_ci,
      `enable_measurement` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      `enable_shaders` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      `enable_materials` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      `enable_lights` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $db->query($initViewers);

      $this->_installOptions();
   }

   public function hookUninstall()
   {
       // Drop the table.
       $db = $this->_db;
       $dropViewers = "DROP TABLE IF EXISTS `{$db->prefix}three_js_viewers`";

       $db->query($dropViewers);

       $this->_uninstallOptions();
   }

   public function hookInitialize()
   {
       add_translation_source(dirname(__FILE__) . '/languages');
       get_view()->addHelperPath(dirname(__FILE__) . '/views/helpers', 'ThreeJS_View_Helper');
   }

   public function hookDefineAcl($args)
   {
      $acl = $args['acl']; // get the Zend_Acl

      $acl->addResource('ThreeJS_Viewers');

   }

   public function filterAdminItemsFormTabs($tabs, $args)
   {
     $item = $args['item'];
     if (item_has_viewer($item)) {
       $tabs['ThreeJSViewer'] = $this->_getMainForm($item, 'edit');
     } else {
       $tabs['ThreeJSViewer'] = $this->_getMainForm($item, 'add');
     }

     return $tabs;
   }

   public function filterApiResources($apiResources)
   {
     $apiResources['threejs_viewers'] = array(
       'record_type' => 'ThreeJSViewer',
       'actions' => array(
         'index',
         'get',
         'post',
         'put',
         'delete'
       ),
     );
     return $apiResources;
   }

   protected function _getMainForm($item, $formType)
   {
      if ($formType == 'add') {
        require_once THREE_VIEWER_ROOT . '/forms/AddThreeViewerForm.php';
        $form = new AddThreeViewerForm($item);
        return $form->render();
      } else {
        return '<div>you got one</div>';
      }

   }

}
