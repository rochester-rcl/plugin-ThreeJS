<?php
require 'AbstractThreeForm.php';

class EditThreeViewerForm extends AbstractThreeForm
{
  private function _initJS()
  {
    $viewerSaveAction = public_url('/api/threejs_viewers') . '/' . $this->_formOptions['viewerId'] . '?key=' . $this->_apiKey;
    $fileSaveAction = public_url('/api/files') . '?key=' . $this->_apiKey;
    $fileDeleteAction = public_url('/api/files') . '/' . $this->_formOptions['threeFileId'] . '?key=' . $this->_apiKey;
    return($this->_adminView->partial('common/admin/three-plugin-api.php', array(
      'itemId' => $this->currentItem->id,
      'fileEndpoint' => $fileSaveAction,
      'saveEndpoint' => $viewerSaveAction,
      'action' => 'edit'
    )));
  }

  public function render()
  {
    $html = $this->_adminView->partial('common/admin/three-plugin-form.php', array('formOptions' => $this->_formOptions));
    $html .= $this->_initJS();
    return $html;
  }
}
