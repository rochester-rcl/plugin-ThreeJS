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
define('THREE_BUNDLE_DIR', THREE_VIEWER_ROOT . '/views/shared/js/ThreeJSPlugin/build');
define('THREE_BUNDLE_STATIC_JS_URL', 'plugins/' . basename(__DIR__) . '/views/shared/js/ThreeJSPlugin/build/static/js/');
define('THREE_BUNDLE_STATIC_MEDIA_URL', 'plugins/' . basename(__DIR__) . '/views/shared/js/ThreeJSPlugin/build/static/media/');
define('THREE_BUNDLE_STATIC_CSS', 'js/ThreeJSPlugin/build/static/css');

class ThreeJSPlugin extends Omeka_Plugin_AbstractPlugin
{
  protected $_SKYBOX_ITEM_TYPE_NAME = 'Skybox';
  protected $_SKYBOX_ELEMENT_INNER_GRADIENT = 'Skybox Radial Gradient Inner Color';
  protected $_SKYBOX_ELEMENT_OUTER_GRADIENT = 'Skybox Radial Gradient Outer Color';

  protected $_hooks = array(
    'install',
    'uninstall',
    'initialize',
    'define_routes',
    'define_acl',
    'admin_head',
    'before_save_item',
    'before_delete_item',
  );

  protected $_addedMimeTypes = array(
    'application/json',
    'application/javascript',
  );

  protected $_addedFileExtensions = array(
    'json',
    'js'
  );

  protected $_filters = array(
    'admin_items_form_tabs',
    'api_resources',
    'file_ingest_validators',
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
      $this->_patchMediaAssets();

   }

   public function hookUninstall()
   {
       // Drop the table.
       $this->_deleteSkyboxType();
       $this->_uninstallOptions();
       $db = $this->_db;
       $dropViewers = "DROP TABLE IF EXISTS `{$db->prefix}three_js_viewers`";
       $db->query($dropViewers);
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

   public function hookDefineRoutes($args)
   {
     $router = $args['router'];
     $threeRoute = new Zend_Controller_Router_Route('three/*',
      array('module' => 'three-js', 'controller' => 'three', 'action' => 'show')
    );
    $router->addRoute('three', $threeRoute);
    return $router;
   }

   public function hookAdminHead()
   {
     queue_js_file('three-plugin-admin', 'js/ThreeJSPlugin');
     queue_css_file('admin-style');
   }

   public function hookBeforeSaveItem($args)
   {
     $item = $args['record'];
     if ($item->id) {
       $viewer = item_has_viewer($item);
       if ($viewer) {
         if ($viewer->needs_delete) {
          $viewerRecord = get_record_by_id('ThreeJSViewer', $viewer->id);
          $fileRecord = get_record_by_id('File', $viewerRecord->three_file_id);
          $viewerRecord->delete();
          $toDelete = array($fileRecord->id);
          if (array_key_exists('delete_files', $args['post'])) {
            $args['post']['delete_files'] = array_merge($args['post']['delete_files'], $toDelete);
          } else {
            $args['post']['delete_files'] = $toDelete;
          }
         }
         $toDelete = array();
         foreach($item->getFiles() as $fileRecord) {
           if ($fileRecord->getExtension() === 'js') {
             if ($fileRecord->id !== $viewer->three_file_id) {
               array_push($toDelete, $fileRecord->id);
             }
           }
         }
         if (array_key_exists('delete_files', $args['post'])) {
           $args['post']['delete_files'] = array_merge($deleteArray, $toDelete);
         } else {
           $args['post']['delete_files'] = $toDelete;
         }
       }
     }
   }

   public function hookBeforeDeleteItem($args)
   {
     $item = $args['record'];
      $viewer = item_has_viewer($item);
      if ($viewer) {
        $viewerRecord = get_record_by_id('ThreeJSViewer', $viewer->id);
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

   public function filterFileIngestValidators($validators)
   {
     // Keeps all the old validators and allows for JS / JSON to be uploaded
     $defaultExtensionWhitelist = Omeka_Validate_File_Extension::DEFAULT_WHITELIST;
     $defaultMimeTypeWhitelist = Omeka_Validate_File_MimeType::DEFAULT_WHITELIST;
     $defaultExtensionWhitelist .= implode(',', $this->_addedFileExtensions);
     $defaultMimeTypeWhitelist .= implode(',', $this->_addedMimeTypes);

     unset($validators['MIME type whitelist']);
     unset($validators['extension whitelist']);
     $validators['ThreeJSPlugin_MimeType_Validators'] = new Omeka_Validate_File_MimeType($defaultMimeTypeWhitelist);
     $validators['ThreeJSPlugin_Extension_Validators'] = new Omeka_Validate_File_Extension($defaultExtensionWhitelist);

     return $validators;
   }

   protected function hydrateOptions($viewer)
   {
     $viewer = (array) $viewer;
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
     $elementSet = $skyboxType->getItemTypeElementSet();
     $radialGradientInner = new Element();
     $radialGradientInner->name = "Skybox Radial Gradient Inner Color";
     $radialGradientInner->description = "Either the hex value (#ffffff) or rgb value (rgb(255, 255, 255))
      for the inner color of the skybox's radial gradient background.";
     $radialGradientInner->element_set_id = $elementSet->id;
     $radialGradientInner->save();

     $radialGradientOuter = new Element();
     $radialGradientOuter->name = "Skybox Radial Gradient Outer Color";
     $radialGradientOuter->description = "Either the hex value (#ffffff) or rgb value (rgb(255, 255, 255))
      for the outer color of the skybox's radial gradient background.";
     $radialGradientOuter->element_set_id = $elementSet->id;
     $radialGradientOuter->save();
     $elementIds = $this->_getSkyboxElementIds();

     $skyboxType->addElementById($elementIds['innerGradientId']);
     $skyboxType->addElementById($elementIds['outerGradientId']);
     $skyboxType->save();
   }

   protected function _deleteSkyboxType()
   {
     $skyboxType = get_record_by_id('ItemType', $this->_getSkyboxItemTypeId());
     // Also need to delete all skyboxes
     $db = get_db();
     $res = $db->getTable('Item')->findBy(array('item_type_id' => $skyboxType->id));
     if (sizeof($res) > 0) {
       foreach($res as $skybox) {
         $skybox->delete();
       }
     }
     $skyboxType->delete();
     $elements = $this->_getSkyboxElementIds();
     if (sizeof($elements) > 0) {
       $inner = get_record_by_id('Element', $elements['innerGradientId']);
       $inner->delete();
       $outer = get_record_by_id('Element', $elements['outerGradientId']);
       $outer->delete();
     }
   }

   protected function _getSkyboxItemTypeId()
   {
     $db = get_db();
     $query = $db->query("SELECT DISTINCT id FROM `{$db->prefix}item_types` WHERE name='{$this->_SKYBOX_ITEM_TYPE_NAME}'");
     $results = $query->fetchAll();
     if ($results) {
       return $results[0]['id'];
     }
   }

   protected function _getSkyboxElementIds()
   {
     $elements = [];
     $db = get_db();
     $inner = $db->query("SELECT id FROM `{$db->prefix}elements` WHERE name='{$this->_SKYBOX_ELEMENT_INNER_GRADIENT}'");
     $results = $inner->fetchAll();
     if (sizeof($results > 0)) {
       $elements['innerGradientId'] = $results[0]['id'];
     }

     $outer = $db->query("SELECT id FROM `{$db->prefix}elements` WHERE name='{$this->_SKYBOX_ELEMENT_OUTER_GRADIENT}'");
     $results = $outer->fetchAll();

     if (sizeof($results > 0)) {
       $elements['outerGradientId'] = $results[0]['id'];
     }

     return $elements;

   }

   protected function _patchMediaAssets()
   {
     // file must be writable by www-data
     $css = load_react_css(TRUE);
     $cssString = file_get_contents($css);
     $res = str_replace('url(/static/media/', 'url(' . public_url(THREE_BUNDLE_STATIC_MEDIA_URL), $cssString);
     file_put_contents($css, $res);
   }

}
