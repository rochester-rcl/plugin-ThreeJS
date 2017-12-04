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

// TODO Need to find a way to delete the associated file when the item is updated

require_once dirname(__FILE__) . '/helpers/ThreeViewerFunctions.php';
$appRoot = getcwd();
define('THREE_VIEWER_ROOT', dirname(__FILE__));
define('THREE_SKYBOX_DIR', dirname(__FILE__) . '/views/shared/skyboxes');

class ThreeJSPlugin extends Omeka_Plugin_AbstractPlugin
{
  protected $_hooks = array(
    'install',
    'uninstall',
    'initialize',
    'define_acl',
    'admin_head',
  );

  protected $_filters = array(
    'admin_items_form_tabs',
    'api_resources',
  );

  //protected $_skyboxes = scandir(THREE_SKYBOX_DIR);

  protected $_formOptions = array(
    'fileOptions' => array (
      'three-file-input' => array(
        'label' => 'Upload a file in ThreeJS format',
        'type' => 'file',
        'id' => 'three-file-input',
        'model_prop' => 'three_file_id',
        'value' => NULL,
      ),
    ),
    'toolOptions' => array(
      'measurement' => array(
        'label' => 'Enable Measurement Tools',
        'id' => 'three-measurement-input',
        'type' => 'checkbox',
        'model_prop' => 'enable_measurement',
        'value' => NULL,
      ),
      'materials' => array(
        'label' => 'Enable Materials Tools',
        'id' => 'three-materials-input',
        'type' => 'checkbox',
        'model_prop' => 'enable_materials',
        'value' => NULL,
      ),
      'lights' => array(
        'label' => 'Enable Light Tools',
        'id' => 'three-lights-input',
        'type' => 'checkbox',
        'model_prop' => 'enable_lights',
        'value' => NULL,
      ),
      'shaders' => array(
        'label' => 'Enable Shader Tools',
        'id' => 'three-shaders-input',
        'model_prop' => 'enable_shaders',
        'type' => 'checkbox',
        'value' => NULL,
      ),
    ),
    'viewerOptions' => array(
      'skybox' => array(
        'label' => 'Select Skybox',
        'type' => 'select',
        'id' => 'three-skybox-input',
        'model_prop' => 'background_url',
        'options' => array(),
        'value' => NULL,
      ),
      'units' => array(
        'label' => 'Model Units',
        'type' => 'select',
        'id' => 'three-units-input',
        'model_prop' => 'model_units',
        'options' => array('mm','cm','in'),
        'value' => NULL,
      )
    )
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
      $this->_formOptions['viewerOptions']['skybox']['options'] = scandir(THREE_SKYBOX_DIR);
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

   public function hookAdminHead()
   {
     queue_js_file('three-plugin-admin', 'js/ThreeJSPlugin');
     queue_css_file('admin-style');
   }

   public function filterAdminItemsFormTabs($tabs, $args)
   {
     $item = $args['item'];
     if ($item->added) {
       $viewer = item_has_viewer($item);
       if ($viewer) {
         $tabs['ThreeJSViewer'] = $this->_getMainForm($item, 'edit', $this->hydrateOptions($viewer));
       } else {
         $tabs['ThreeJSViewer'] = $this->_getMainForm($item, 'add', $this->_formOptions);
       }
     } else {
       $tabs['ThreeJSViewer'] = '<h4>A ThreeJS Viewer requires an item id. Please add your item to add a viewer </h4>';
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

   protected function hydrateOptions($viewer)
   {
     $newOptions = $this->_formOptions;
     foreach($newOptions as $key => $group) {
       $newOptions[$key] = array_map(function($option) use (&$viewer) {
         $prop = $option['model_prop'];
         if ($prop === 'three_file_id') {
           $file = get_record_by_id('File', $viewer[$prop]);
           if ($file) {
             $option['label'] = 'Current attached file is ' . $file->original_filename;
           }
         } else {
           $option['value'] = $viewer[$prop];
         }
         return $option;
       }, $group);
     }
     $newOptions['viewerId'] = $viewer['id'];
     $newOptions['threeFileId'] = $viewer['three_file_id'];
     return $newOptions;
   }

   protected function _getMainForm($item, $formType, $options)
   {
      if ($formType == 'add') {
        require_once THREE_VIEWER_ROOT . '/forms/AddThreeViewerForm.php';
        $form = new AddThreeViewerForm($item, get_view(), $options);
        return $form->render();
      } else {
        require_once THREE_VIEWER_ROOT . '/forms/EditThreeViewerForm.php';
        $form = new EditThreeViewerForm($item, get_view(), $options);
        return $form->render();
      }
   }

}
