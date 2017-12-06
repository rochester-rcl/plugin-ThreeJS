<?php

class ThreeJSViewer extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
  public $id;
  public $item_id;
  public $three_file_id;
  public $skybox_id;
  public $enable_measurement;
  public $enable_shaders;
  public $enable_materials;
  public $enable_lights;
  public $model_units;
  public $needs_delete;

public function getResourceId()
{
    // This is typically the name of the plugin, an underscore, and the pluralized record type.
    return 'ThreeJS_Viewers';
}

}

?>
