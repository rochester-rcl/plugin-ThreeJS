<?php
class Api_ThreeJSViewer extends Omeka_Record_Api_AbstractRecordAdapter
{
    // Get the REST representation of a record.
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        // Return a PHP array, representing the passed record.
        $representation = array(
          'item_id' => $record->item_id,
          'three_file_id' => $record->three_file_id,
          'background_url' => THREE_SKYBOX_URL . '/' . $record->background_url,
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
        $record->background_url = $data->background_url;
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
          $record->background_url = $data->background_url;
          $record->enable_lights = $data->enable_lights;
          $record->enable_materials = $data->enable_materials;
          $record->enable_shaders = $data->enable_shaders;
          $record->enable_measurement = $data->enable_measurement;
          $record->model_units = $data->model_units;
          $record->needs_delete = $data->needs_delete;
        }
    }
}
