<div class="field">
    <div id="threejs_skybox_upload_container" class="two columns alpha">
        <label for="threejs_skybox_upload"><?php echo __('Upload Skyboxes'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php
          $fileValidators = array();
          $extensionValidator = new Omeka_Validate_File_Extension();
          $fileValidators[] = $extensionValidator;
          echo $this->formFile('threejs_skybox_upload'); ?>
    </div>
</div>
