<?php
class Api_ThreeJSViewer extends Omeka_Record_Api_AbstractRecordAdapter
{
    // Get the REST representation of a record.
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        // Return a PHP array, representing the passed record.
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
    }

    // Set data to a record during a PUT request.
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {
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
    }
}
