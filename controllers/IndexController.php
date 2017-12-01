<?php

class ThreeViewer_ApiController extends Omeka_Controller_AbstractActionController
{
  public function init()
  {
    $this->session = new Zend_Session_Namespace('ThreeViewer');
    $this->_helper->db->setDefaultModelName('ThreeViewer');
    $this->_setBrowseRecords();
  }

  public function indexAction()
  {

  }

  public function editAction()
  {
    /*$viewer = $this->_helper->db->findById();
    if(!$viewer){
    } else {
      $formOptions = array();
      $formOptions['type'] = 'OpenSeadragonTEIViewer';
      $form = $this->_getEditForm($viewer, $formOptions);
      $this->view->open_seadragon_tei_viewer = $viewer;
      $this->view->form = $form;
    }*/

  }

  public function addAction()
  {

  }

  public function browseAction()
  {
    $this->_helper->redirector('index');
  }

  protected function _setBrowseRecords()
  {
    $records = get_records('ThreeViewer', $params=array(), $limit=100);
    $this->view->setLoopRecords('ThreeViewer', $records);
  }

  protected function _getMainForm()
  {
      require_once THREE_VIEWER_ROOT . '/forms/MainForm.php';
      $form = new ThreeViewer_Form_Main();
      return $form;
  }

  protected function _getEditForm($viewer, $formOptions)
  {
    /*$form = new Omeka_Form_Admin($formOptions);

    $form->addElement('text', 'viewer_name', array(
      'label' => 'Viewer Name',
      'description' => 'Viewer name',
      'value' => $viewer->viewer_name,
    ));
    if($viewer->xsl_viewer_option == 1){
      $checkedState = TRUE;
    } else {
      $checkedState = FALSE;
    }
    $form->addElement('checkbox', 'xsl_viewer_option', array(
        'label' => __('Use an XSLT File for Rendering'),
        'description' => 'Check this box to add a rendering component for attached XML files.',
        'checked_value' => 1,
        'unchecked_value' => 0,
        'checked' => $checkedState,
      ));
      if($viewer->override_items_show_option == 1){
        $itemsShowCheckedState = TRUE;
      } else {
        $itemsShowCheckedState = FALSE;
      }
      $form->addElement('checkbox', 'override_items_show_option', array (
        'label' => __('Override Items Show Template'),
        'description' => 'Check this box to override the current theme\'s items show template.
                          Leaving it unchecked will create views at mysite/viewer/itemtype/itemid.',
        'checked_value' => 1,
        'unchecked_value' => 0,
        'checked' => $itemsShowCheckedState,
      ));


    $fileValidators = array();
    //$mimeValidator = new Omeka_Validate_File_MimeType('application/xml');
    $extensionValidator = new Omeka_Validate_File_Extension('xsl');
    $fileValidators[] = $extensionValidator;

    $form->addElement('file', 'xsl_file', array(
        'label' => __('Upload XSLT Transformation'),
        'required' => false,
        'validators' => $fileValidators,
        'description' => 'Upload a file to transform associated TEI files.
                          Leave blank to add a viewer for images only. Current file is ' . basename($viewer->xsl_url),
        'destination' => TRANSFORMATION_DIRECTORY_SYSTEM,
    ));

    $itemTypes = open_seadragon_tei_get_item_types();
    if(array_key_exists($viewer->item_type_id, $itemTypes)){
      $currentItemId = $viewer->item_type_id;
    }
    // Add ability to select item type
    $form->addElement('select', 'item_type', array(
      'label' => 'Select Item Type to Apply the Viewer to.',
      'multiOptions' => $itemTypes,
      'value' => $currentItemId,
    ));

    $form->addElement('submit', 'save', array('label' => 'Save'));
    $form->setAction(record_url($viewer, 'edit-save'))
    ->setMethod('post');

    return $form;
    */
  }

  public function saveAction()
  {

    if (!$this->getRequest()->isPost()) {
      return $this->_forward('index');
    }
    $form = $this->_getMainForm();

  }

  public function editSaveAction()
  {
    /*$viewer = $this->_helper->db->findById();
    if($viewer && $this->getRequest()->isPost()){
      $this->getRequest()->getPost();
      $formOptions = array();
      $formOptions['type'] = 'OpenSeadragonTEIViewer';
      $form = $this->_getEditForm($viewer, $formOptions);
      if($form->isValid($_POST)){
        if($form->xsl_file->receive()){
          $fileTest = new File();
          $filename = $form->xsl_file->getFilename();
          if((array) $filename !== $filename){
            $viewer->xsl_url = $filename;
          }
        }
        $viewerName = $form->viewer_name->getValue();
        $viewerItemType = $form->item_type->getValue();
        $xslOption = $form->xsl_viewer_option->getValue();
        $overrideItemsShowOption = $form->override_items_show_option->getValue();

        $viewer->viewer_name = $viewerName;
        $viewer->item_type_id = $viewerItemType;
        $viewer->xsl_viewer_option = $xslOption;
        $viewer->override_items_show_option = $overrideItemsShowOption;

        try {
            if ($viewer->save()) {
                $this->_helper->flashMessenger(__('The viewer "%s" has been updated.', $viewer->viewer_name), 'success');
            }
            // Catch validation errors.
        } catch (Omeka_Validate_Exception $e) {
            $this->_helper->flashMessenger($e);
        }

      }
   }

   $this->_helper->redirector('index');
   */
  }

  protected function _getDeleteSuccessMessage($viewer)
    {
        return __('The viewer "%s" has been deleted.', $viewer->viewer_name);
    }
}

?>
