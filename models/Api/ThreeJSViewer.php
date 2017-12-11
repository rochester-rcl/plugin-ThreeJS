<?php
class Api_ThreeJSViewer extends Omeka_Record_Api_AbstractRecordAdapter
{
    // Get the REST representation of a record.
    protected function _loadSkybox($skyboxId) {
      $skyboxRecord = get_record_by_id('Item', $skyboxId);
      $skyboxInfo = array();
      if ($skyboxRecord) {
        $skybox = array();
        $files = $skyboxRecord->getFiles();
        $elements = item_type_elements($skyboxRecord);
        if (sizeof($files) > 0) {
          $skyboxInfo['file'] = $files[0]->getWebPath('original');
        }
        if (sizeof($elements) > 0) {
          $skyboxInfo['gradient'] = array('innerColor' => $elements['Skybox Radial Gradient Inner Color'],
          'outerColor' => $elements['Skybox Radial Gradient Outer Color']);
        }
        if (array_key_exists('file', $skyboxInfo) || array_key_exists('gradient', $skyboxInfo)) {
          return $skyboxInfo;
        } else {
          throw new Exception('Skybox Record has no associated files and no gradient values - viewer will use the default background!');
        }
      } else {
        throw new Exception('Skybox record is not public! To fix this, you can make item ' . $skyboxId . ' public');
      }
    }

    protected function _loadThreeFile($threeFileId, $itemId) {
      $threeFileRecord = get_record_by_id('File', $threeFileId);
      if ($threeFileRecord) {
        return $threeFileRecord->getWebPath('original');
      } else {
        throw new Exception('Three file record parent item is not public! To fix this, you can make item ' . $itemId . ' public.');
      }
    }

    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        if ($record->skybox_id !== -1) {
          try {
            $skybox = $this->_loadSkybox($record->skybox_id);
          } catch (Exception $error) {
            $skybox = array('error' => $error->getMessage(), 'status' => 500);
          }
        } else {
          $skybox = array('file' => null);
        }

        try {
          $threeFile = $this->_loadThreeFile($record->three_file_id, $record->item_id);
        } catch (Exception $error) {
          $threeFile = array('error' => $error->getMessage(), 'status' => 500);
        }

        // Return a PHP array, representing the passed record.
        $representation = array(
          'id' => $record->id,
          'item_url' => absolute_url('api/items/' . $record->item_id),
          'three_file' => $threeFile,
          'skybox' => $skybox,
          'enable_lights' => $record->enable_lights,
          'enable_materials' => $record->enable_materials,
          'enable_shaders' => $record->enable_shaders,
          'enable_measurement' => $record->enable_measurement,
          'model_units' => $record->model_units,
        );
        return $representation;
    }

    // Set data to a record during a POST request.
    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {
        // Set properties directly to a new record.
        $record->item_id = $data->item_id;
        $record->three_file_id = $data->three_file_id;
        $record->skybox_id = $data->skybox_id;
        $record->enable_lights = $data->enable_lights;
        $record->enable_materials = $data->enable_materials;
        $record->enable_shaders = $data->enable_shaders;
        $record->enable_measurement = $data->enable_measurement;
        $record->model_units = $data->model_units;
        $record->needs_delete = $data->needs_delete;
    }

    // Set data to a record during a PUT request.
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {
        if ($data->needs_delete) {
          // Don't care about setting anything else as this will be deleted on the next after_save_record hook
          $record->needs_delete = $data->needs_delete;
        } else {
          // Set properties directly to an existing record.
          if ($data->three_file_id) {
            $current = get_record_by_id('File', $record->three_file_id);
            $record->three_file_id = $data->three_file_id;
            $current->delete();
          }
          $record->item_id = $data->item_id;
          $record->skybox_id = $data->skybox_id;
          $record->enable_lights = $data->enable_lights;
          $record->enable_materials = $data->enable_materials;
          $record->enable_shaders = $data->enable_shaders;
          $record->enable_measurement = $data->enable_measurement;
          $record->model_units = $data->model_units;
          $record->needs_delete = $data->needs_delete;
        }
    }
}
