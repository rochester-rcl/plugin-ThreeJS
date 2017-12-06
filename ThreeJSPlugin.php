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
define('THREE_SKYBOX_DIR', dirname(__FILE__) . '/../../files/skyboxes');
define('THREE_SKYBOX_URL', absolute_url(public_url('/views/shared/skyboxes')));

class ThreeJSPlugin extends Omeka_Plugin_AbstractPlugin
{
  protected $_hooks = array(
    'install',
    'uninstall',
    'initialize',
    'define_acl',
    'admin_head',
    'after_save_item',
    'before_delete_item',
  );

  protected $_filters = array(
    'admin_items_form_tabs',
    'api_resources',
  );

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
        'model_prop' => 'skybox_id',
        'options' => array(array('value' => -1, 'label' => 'None')),
        'value' => NULL,
      ),
      'units' => array(
        'label' => 'Model Units',
        'type' => 'select',
        'id' => 'three-units-input',
        'model_prop' => 'model_units',
        'options' => array(
          array(
            'value' => 'mm',
            'label' => 'mm',
          ),
          array(
            'value' => 'cm',
            'label' => 'cm',
          ),
          array(
            'value' => 'in',
            'label' => 'in',
          )
        ),
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
      `item_id` int(20) COLLATE utf8_unicode_ci NOT NULL,
      `three_file_id` int(20) COLLATE utf8_unicode_ci NOT NULL,
      `model_units` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
      `skybox_id` int(20) COLLATE utf8_unicode_ci,
      `enable_measurement` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      `enable_shaders` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      `enable_materials` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      `enable_lights` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      `needs_delete` tinyint(1) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $db->query($initViewers);
      $this->_installOptions();
      $this->_createSkyboxType();

   }

   public function hookUninstall()
   {
       // Drop the table.
       $db = $this->_db;
       $dropViewers = "DROP TABLE IF EXISTS `{$db->prefix}three_js_viewers`";

       $db->query($dropViewers);
       $this->_deleteSkyboxType();
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
      $acl->allow(null, 'ThreeJS_Viewers', 'show');

   }

   public function hookAdminHead()
   {
     queue_js_file('three-plugin-admin', 'js/ThreeJSPlugin');
     queue_css_file('admin-style');
   }

   public function hookAfterSaveItem($args)
   {
      $item = $args['record'];
      $viewer = item_has_viewer($item);
      if ($viewer['needs_delete']) {
        $viewerRecord = get_record_by_id('ThreeJSViewer', $viewer['id']);
        $fileRecord = get_record_by_id('File', $viewerRecord->three_file_id);
        $viewerRecord->delete();
        $fileRecord->delete();
      }
   }

   public function hookBeforeDeleteItem($args)
   {
     $item = $args['record'];
     $viewer = item_has_viewer($item);
     if ($viewer) {
       $viewerRecord = get_record_by_id('ThreeJSViewer', $viewer['id']);
       $viewerRecord->delete();
     }
   }

   public function filterAdminItemsFormTabs($tabs, $args)
   {
     $skyboxOptions = $this->_formOptions['viewerOptions']['skybox']['options'];
     $this->_formOptions['viewerOptions']['skybox']['options'] = array_merge($skyboxOptions, get_skybox_options($this->_getSkyboxItemTypeId()));
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
         'get',
         'index',
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

   protected function _createSkyboxType()
   {
     $skyboxType = new ItemType();
     $skyboxType->name = 'Skybox';
     $skyboxType->description = 'An item type to be used specifically with the ThreeJS Plugin.
       By assigning an item this type, you will be able to use equirectangular images as 360 panoramic
       backgrounds for your viewers. If you choose not to attach an image, you can add rgb values for
       a linear gradient skybox. Currently only works with a single equirectangular (spherical) image.';
     $skyboxType->save();
   }

   protected function _deleteSkyboxType()
   {
     $skyboxType = get_record_by_id('ItemType', $this->_getSkyboxItemTypeId());
     $skyboxType->delete();
   }

   protected function _getSkyboxItemTypeId()
   {
     $db = get_db();
     $query = $db->query("SELECT DISTINCT id FROM `{$db->prefix}item_types` WHERE name='Skybox'");
     $results = $query->fetchAll();
     if ($results) {
       return $results[0]['id'];
     }
   }

}
