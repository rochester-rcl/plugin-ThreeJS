function addThreeViewer(itemId, fileEndpoint, saveEndpoint, action){

    // AJAX related functions
    function sendFile(endpoint, formData, callback) {
      var fileRequest = new XMLHttpRequest();
      fileRequest.onerror = function(error) { console.log(error); console.log(fileRequest) };
      fileRequest.onload = function() {
        var res = JSON.parse(fileRequest.responseText);
        if (res) {
          callback(res);
        }
      }
      fileRequest.open("POST", endpoint);
      fileRequest.send(formData);
    }

    function saveViewer(endpoint, currentFile, formData) {
      var request = new XMLHttpRequest();
      if (action === "edit") {
        request.open("PUTS", endpoint);
      } else {
        request.open("POST", endpoint);
      }
      request.onerror = function(error) { console.error(error) };
      request.onload = function() {
        console.log(request);
      }
      var viewerData = {
        item_id: itemId,
        three_file_id: currentFile.id,
        background_url: formData.skybox,
        enable_measurement: formData.measurement,
        enable_materials: formData.materials,
        enable_lights: formData.lights,
        enable_shaders: formData.shaders,
        model_units: formData.units,
      }
      request.send(JSON.stringify(viewerData));
    }

    /*
    * function parseFieldset
    * @param fieldset
    */

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

    /*
    * function fileFormData
    * @param fieldset
    */

    function fileFormData(fieldset) {
      var formData = new FormData();
      var children = fieldset.children;
      for (var i=0; i < children.length; i++) {
        var child = children[i];
        if (child.type === "file") {
          formData.append("file", child.files[0]);
          formData.append("data", JSON.stringify({
            item: {
              id: itemId,
            },
          }));
        }
      }
      return formData;
    }

    /*
    *
    * function getFormData
    * @param event
    */

    function getFormData(event) {
      event.preventDefault();
      event.stopPropagation();
      var form = document.getElementById("three-viewer-form");
      var fileData = fileFormData(fieldset);
      var formData = parseFieldset(form);
      if (fileData) {
        sendFile(fileEndpoint, fileData, function(currentFile) {
          saveViewer(saveEndpoint, currentFile, formData);
        });
      }
    }
    var submit = document.getElementById("three-viewer-form-submit");
    submit.onclick = function (event) { getFormData(event) };
}
