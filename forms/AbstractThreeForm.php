<?php

abstract class AbstractThreeForm
{
  public $currentItem;
  protected $_apiKey;
  protected $_adminView;
  protected $_formOptions;

  public function __construct($item, $view, $formOptions)
  {
    $this->currentItem = $item;
    $this->_apiKey = get_user_api_key();
    $this->_adminView = $view;
    $this->_formOptions = $formOptions;
  }

  private function _initJS()
  {

  }

  public function render()
  {

  }
}
