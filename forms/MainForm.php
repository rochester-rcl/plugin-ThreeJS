<?php

class ThreeViewer_Form_Main extends Omeka_Form_Admin
{
  public $threeFileDir;
  public $threeFileUrl;
  public $currentItemID;
  private $_requiredJSMimeTypes = array('application/javascript', 'application/json');
  private $_requiredJSExtensions = array('js', 'json');
  private $_requiredImageMimeTypes = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/webp');
  private $_requiredImageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp');
  protected $_record = 'ThreeViewer';
  protected $_type = 'ThreeViewer';

  public function init()
  {
    parent::init();
    $this->currentItemID = $this->_record['id'];
    $this->_addThreeFileElement();
    $this->_addEnableLightsElement();
    $this->_addEnableMaterialsElement();
    $this->_addEnableShadersElement();
    $this->_addEnableMeasurementElement();
    $this->_addSubmit();
    $this->_addSave();
    $this->applyOmekaStyles();
    $this->setAutoApplyOmekaStyles(false);
  }

  protected function _addThreeFileElement()
  {
    $fileValidators = array();
    $mimeValidator = new Omeka_Validate_File_MimeType('application/javascript');
    $extensionValidator = new Omeka_Validate_File_Extension('js');
    $fileValidators[] = $extensionValidator;

    $this->addElement('file', 'three_file', array(
        'label' => __('Upload ThreeJS File'),
        'required' => false,
        'validators' => $fileValidators,
        'description' => 'Upload a file exported from the OBJ -> ThreeJS converter.',
        'destination' => THREE_FILE_DIRECTORY_SYSTEM,
    ));

  }

  protected function _addEnableLightsElement()
  {
    $this->addElement('checkbox', 'enable_lights', array (
      'label' => __('Enable Light Tools'),
      'description' => 'Enable to allow users to control the dynamic lighting options in the viewer.',
      'checked_value' => 1,
      'unchecked_value' => 0,
    ));
  }

  protected function _addEnableMaterialsElement()
  {
    $this->addElement('checkbox', 'enable_materials', array (
      'label' => __('Enable Materials Tools'),
      'description' => 'Enable to allow users to control the materials options in the viewer.',
      'checked_value' => 1,
      'unchecked_value' => 0,
    ));
  }

  protected function _addEnableShadersElement()
  {
    $this->addElement('checkbox', 'enable_shaders', array (
      'label' => __('Enable Shaders Tools'),
      'description' => 'Enable to allow users to control the shaders options in the viewer.',
      'checked_value' => 1,
      'unchecked_value' => 0,
    ));
  }

  protected function _addEnableMeasurementElement()
  {
    $this->addElement('checkbox', 'enable_measurement', array (
      'label' => __('Enable Measurement Tools'),
      'description' => 'Enable to allow users to control the measurement options in the viewer.',
      'checked_value' => 1,
      'unchecked_value' => 0,
    ));
  }

  protected function _addSubmit()
  {
   $this->addElement('submit', 'save', array('label' => 'Add ThreeJS Viewer'));
  }

  protected function _addSave()
  {
    $this->setAction(url('three-viewer/index/save'))
    ->setMethod('post');
  }

}
