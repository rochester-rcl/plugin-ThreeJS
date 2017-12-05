function addThreeViewer(itemId, fileEndpoint, saveEndpoint, action){

    // AJAX related functions
    function sendFile(endpoint, formData, callback) {
      var fileRequest = new XMLHttpRequest();
      fileRequest.onerror = function(error) { console.log(error) };
      fileRequest.onload = function() {
        var res = JSON.parse(fileRequest.responseText);
        if (res) {
          callback(res);
        }
      }
      fileRequest.open("POST", endpoint);
      if (formData.has('file')) {
        fileRequest.send(formData);
      } else {
        callback(res={})
      }
    }

    function showMessage(request) {
      var form = document.getElementById('three-viewer-form');
      var messageContainer = document.createElement('div');
      var messages = document.createElement('ul');
      var message = document.createElement('li');
      messageContainer.id = "flash";
      if (request.status === 201) {
        var text = document.createTextNode("The ThreeJS Viewer was successfully created!");
        message.className = "success";
        message.appendChild(text);
      }

      if (request.status === 200) {
        var text = document.createTextNode("The ThreeJS Viewer was successfully updated!");
        message.className = "success";
        message.appendChild(text);
      }

      if (request.status === 204) {
        var text = document.createTextNode("The ThreeJS Viewer was successfully deleted!");
        message.className = "success";
        message.appendChild(text);
      }

      messages.appendChild(message);
      messageContainer.appendChild(messages);
      form.prepend(messageContainer);
    }

    function saveViewer(endpoint, currentFile, formData) {
      var request = new XMLHttpRequest();
      if (action === "edit") {
        request.open("PUT", endpoint);
      } else {
        request.open("POST", endpoint);
      }
      request.onerror = function(error) { console.error(error) };
      request.onreadystatechange = function() { console.log(request) }
      request.onload = function() {
        showMessage(request);
      }
      var viewerData = {
        item_id: itemId,
        three_file_id: currentFile.id ? currentFile.id : null,
        skybox_id: formData.skybox,
        enable_measurement: formData.measurement,
        enable_materials: formData.materials,
        enable_lights: formData.lights,
        enable_shaders: formData.shaders,
        model_units: formData.units,
        needs_delete: false,
      }
      request.send(JSON.stringify(viewerData));
    }

    function deleteViewer(event) {
      event.preventDefault();
      event.stopPropagation();
      var request = new XMLHttpRequest();
      request.open("PUT", saveEndpoint);
      request.onload = function() {
        showMessage(request);
      }
      request.send(JSON.stringify({ needs_delete: true }));
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
          if (child.files[0]) {
            formData.append("file", child.files[0]);
            formData.append("data", JSON.stringify({
              item: {
                id: itemId,
              },
            }));
          } else {
            console.log("No file attached.");
          }
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
      var fileData = fileFormData(form);
      var formData = parseFieldset(form);

      sendFile(fileEndpoint, fileData, function(currentFile) {
        saveViewer(saveEndpoint, currentFile, formData);
      });
    }
    var submit = document.getElementById("three-viewer-form-submit");
    submit.onclick = function (event) { getFormData(event) };

    var doDelete = document.getElementById("three-viewer-form-delete");
    if (doDelete) {
      doDelete.onclick = function (event) { deleteViewer(event) };
    }
}
