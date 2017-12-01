<?php
require 'AbstractThreeForm.php';

class AddThreeViewerForm extends AbstractThreeForm
{
  private function _initJS()
  {

    $viewerSaveAction = public_url('/api/threejs_viewers') . '?key=' . $this->_apiKey;

    $fileSaveAction = public_url('/api/files') . '?key=' . $this->_apiKey;

    $js = '<script type="text/javascript">
            var currentItemId = ' . js_escape($this->currentItem->id) . ';

            function parseFieldset(fieldset) {
              var data = {}
              var parseFields = function(fieldset) {
                var children = fieldset.children;
                for (var i=0; i < children.length; i++) {
                  var child = children[i];
                  var tagName = child.tagName;

                  if (tagName === "FIELDSET") {
                    parseFields(child);
                  }

                  if (tagName === "INPUT") {
                    if (child.type === "checkbox") {
                      if (child.checked === true) {
                        data[child.name] = true;
                      } else {
                        data[child.name] = false;
                      }
                    }
                  }

                  if (tagName === "SELECT") {
                    data[child.name] = child.value;
                  }

                }
              }
              parseFields(fieldset);
              return data;
            }

            function fileFormData(fieldset) {
              var formData = new FormData();
              var children = fieldset.children;
              for (var i=0; i < children.length; i++) {
                var child = children[i];
                if (child.type === "file") {
                  formData.append("file", child.files[0]);
                  formData.append("data", JSON.stringify({
                    item: {
                      id: currentItemId,
                    },
                  }));
                }
              }
              return formData;
            }

            function getFormData(event) {
              event.preventDefault();
              event.stopPropagation();
              var form = document.getElementById("three-viewer-form");

              var formData = parseFieldset(form);

              var fileRequest = new XMLHttpRequest();
              fileRequest.onerror = function(error) { console.log(error) };
              fileRequest.onload = function() {
                var request = new XMLHttpRequest();
                request.open("POST", ' . js_escape($viewerSaveAction) .');
                request.onerror = function(error) { console.log(error) };
                request.onload = function() {
                  console.log(request);
                }
                var res = JSON.parse(fileRequest.responseText);
                console.log(res);
                var viewerData = {
                  item_id: currentItemId,
                  three_file_id: res.id,
                  background_url: formData.skybox,
                  enable_measurement: formData.measurement,
                  enable_materials: formData.materials,
                  enable_lights: formData.lights,
                  enable_shaders: formData.shaders,
                  model_units: formData.units,
                }

                request.send(JSON.stringify(viewerData));
              }

              // Send the file first, then do the rest

              fileRequest.open("POST", ' . js_escape($fileSaveAction) . ');
              var fileData = fileFormData(form);
              fileRequest.send(fileData);

            }
            var submit = document.getElementById("three-viewer-form-submit");
            submit.onclick = function (event) { getFormData(event) };
          </script>';

    return $js;
  }

  public function render()
  {
    $html = '';
    $html .= '<section class="seven columns alpha" id="edit-form"><fieldset class="set">';

    $form = '<div id="three-viewer-form">
              <label for="three-file-input">Upload a file in ThreeJS format.</label>
              <input class="three-form-input" type="file" name="three-file-input" id="three-file-input">
              <fieldset class="three-options-fieldset">
                <h4>Tool Options</h4>
                <label for="three-measurement-input">Enable Measurement Tools</label>
                <input class="three-form-input" id="three-measurement-input" type="checkbox" name="measurement">
                <label for="three-materials-input">Enable Materials Tools</label>
                <input class="three-form-input" id="three-materials-input" type="checkbox" name="materials">
                <label for="three-lights-input">Enable Light Tools</label>
                <input class="three-form-input" id="three-lights-input" type="checkbox" name="lights">
                <label for="three-shaders-input">Enable Shader Tools</label>
                <input class="three-form-input" id="three-shaders-input" type="checkbox" name="shaders">
              </fieldset>
              <fieldset class="three-options-fieldset">
                <h4>Viewer Options</h4>
                <label for="three-background-input">Select Skybox</label>
                <select class="three-form-input" id="three-skybox-input" type="select" name="skybox">
                </select>
                <label for="three-units-input">Model Units</label>
                <select class="three-form-input" id="three-units-input" name="units">
                  <option value="mm">mm</option>
                  <option value="cm">cm</option>
                  <option value="m">m</option>
                  <option value="in">in</option>
                </select>
              </fieldset>
              <button class="submit" id="three-viewer-form-submit">Add ThreeJS Viewer</button>
            </div>';
    $html .=  $form;
    $html .= '</fieldset></section>';

    $html .= $this->_initJS();

    return $html;
  }
}
