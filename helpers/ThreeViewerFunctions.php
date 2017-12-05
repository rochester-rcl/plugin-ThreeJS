<?php

function get_user_api_key() {
  // Key must be for "three" and the user must be authenticated
  $currentUser = current_user();
  if ($currentUser) {
    $db = get_db();
    $query = $db->query("SELECT `key` FROM `{$db->prefix}keys` WHERE user_id={$currentUser->id} and label='three'");
    $results = $query->fetchAll();
    if ($results) {
      return $results[0]['key'];
    }
  }
}

function item_has_viewer($item) {
  $db = get_db();
  $query = $db->query("SELECT DISTINCT * FROM `{$db->prefix}three_js_viewers` WHERE item_id={$item->id}");
  $results = $query->fetchAll();
  if ($results) {
    return $results[0];
  } else {
    return NULL;
  }
}

function file_has_viewer($file) {
  $db = get_db();
  $query = $db->query("SELECT DISTINCT * FROM `{$db->prefix}three_js_viewers` WHERE three_file_id={$file->id}");
  $results = $query->fetchAll();
  if ($results) {
    return $results[0];
  } else {
    return NULL;
  }
}

function get_skybox_options($itemTypeId) {
  $db = get_db();
  $query = $db->query("SELECT DISTINCT * FROM `{$db->prefix}items` WHERE item_type_id={$itemTypeId}");
  $results = $query->fetchAll();
  return array_map(function($result) {
    $record = get_record_by_id('Item', $result['id']);
    $files = $record->getFiles();
    return array('value' => $record->id, 'label' => $files[0]->original_filename);
  }, $results);

}
