<?php

function get_user_api_key() {
  // Key must be for "three" and the user must be authenticated
  $currentUser = current_user();
  if ($currentUser) {
    $db = get_db();
    $res = $db->getTable('Key')->findBy(array('user_id' => $currentUser->id));
    if (sizeof($res) > 0) {
      return $res[0]->key;
    }
  }
}

function checkExtension($ext) {
  $extensions = array('js', 'gz', 'gzip', 'json');
  return in_array($ext, $extensions);
}

function item_has_viewer($item)
{
  $db = get_db();
  $res = $db->getTable('ThreeJSViewer')->findBy(array('item_id' => $item->id));
  if (sizeof($res)) {
    return $res[0];
  } else {
    return NULL;
  }
}

function viewer_items()
{
  $db = get_db();
  // this will work for now but we probably need to do something more sophisticated
  $viewers = $db->getTable('ThreeJSViewer')->findAll();
  if (sizeof($viewers) > 0) {
    return array_map(function($viewer) {
      // must have an item id
      $item = get_record_by_id('Item', $viewer->item_id);
      $item->_viewer_id = $viewer->id;
      return $item;
    }, $viewers);
  } else {
    return NULL;
  }
}

function three_lazy_load_image($format, $item)
{
  $files = $item->getFiles();
  foreach($files as $file) {
    $ext = checkExtension($file->getExtension());
    if (!$ext) {
      $thumbnail = $file->getWebPath($format);
      break;
    }
  }

  if (!isset($thumbnail)) {
    $thumbnail = url('/application/views/scripts/images/fallback-file.png');
  }
  
  $thumbnailConstraint = get_option('square_thumbnail_constraint');
  $markup = '<img class="pre-loading" data-original="' . $thumbnail . '" width="' . $thumbnailConstraint . '" height="' . $thumbnailConstraint . '"/>';
  return $markup;
}

function file_has_viewer($file)
{
  $db = get_db();
  $res = $db->getTable('ThreeJSViewer')->findBy(array('three_file_id' => $file->id));
  if (sizeof($results) > 0) {
    return $res[0];
  } else {
    return NULL;
  }
}

function get_skybox_options($itemTypeId) {
  $db = get_db();
  $res = $db->getTable('Item')->findBy(array('item_type_id' => $itemTypeId));
  if (sizeof($res) > 0) {
    return array_map(function($result) {
      $record = get_record_by_id('Item', $result['id']);
      return array('value' => $record->id, 'label' => metadata($record, array('Dublin Core', 'Title')));
    }, (array) $res);
  } else {
    return array();
  }
}

function load_react_css($dryRun)
{
  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(THREE_BUNDLE_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  $files = [];

  foreach($iterator as $path => $dir) {
    if ($dir->isFile()) {
      $file = $dir;
      $ext = $file->getExtension();
      if ($ext === 'css') {
        if ($dryRun) {
          return ($file->getRealPath());
        } else {
          queue_css_file($file->getBasename('.css'), 'all', false, THREE_BUNDLE_STATIC_CSS);
        }
      }
    }
  }
}



// bundle has uuids and will break all of the relationships if we change the name
function load_js_bundle()
{
  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(THREE_BUNDLE_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  $files = [];

  foreach($iterator as $path => $dir) {
    if ($dir->isFile()) {
      $file = $dir;
      $ext = $file->getExtension();
      if ($ext === 'js') {
        return absolute_url(THREE_BUNDLE_STATIC_JS_URL . $file->getBasename('.js'));
      }
    }
  }
}
