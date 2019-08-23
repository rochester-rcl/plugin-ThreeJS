<?php

class ThreeJS_ThreeController extends Omeka_Controller_AbstractActionController
{
  public function showAction()
  {
    $fullscreen = $this->_request->getQuery('embed');
    $this->view->isThreeView = true;
    $this->view->fullscreen = ($fullscreen === 'true' ? true : false);
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
    $params = $this->getRequest()->getUserParams();
    $url = '/three/embed/' . $params['models'] . '?embed=true';
    $this->_redirect(absolute_url($url));
  }
}
?>
