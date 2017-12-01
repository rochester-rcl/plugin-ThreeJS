<?php

abstract class AbstractThreeForm
{
  public $currentItem;
  protected $_apiKey;

  public function __construct($item)
  {
    $this->currentItem = $item;
    $this->_apiKey = get_user_api_key();
  }

  private function _initJS()
  {

  }

  public function render()
  {

  }
}
