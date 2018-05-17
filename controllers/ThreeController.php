<?php

class ThreeJS_ThreeController extends Omeka_Controller_AbstractActionController
{
  public function showAction()
  {

  }

  public function browseAction()
  {
    $items = viewer_items();
    $total = sizeof($items);
    $this->view->assign(array('items' => $items, 'total_results' => $total,
      'browseTitle' => '3D Scans'));
  }

  public function fullscreenAction()
  {
    
  }
}
?>
