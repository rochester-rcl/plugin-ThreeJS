<?php
  $fileOptions = $this->formOptions['fileOptions'];
  $toolOptions = $this->formOptions['toolOptions'];
  $viewerOptions = $this->formOptions['viewerOptions'];
?>
<section class="seven columns alpha" id="edit-form">
  <fieldset class="set">
    <div id="three-viewer-form">
      <label for=<?=key($fileOptions)?>><?=$fileOptions['three-file-input']['label']?></label>
      <input class="three-form-input" type="file" name="three-file-input" id="three-file-input">
      <fieldset class="three-options-fieldset">
          <h4>Tool Options</h4>
          <?php foreach($toolOptions as $key => $toolOption): ?>
            <label for=<?=$key?>><?=$toolOption['label']?></label>
            <input
              class="three-form-input"
              id=<?=$toolOption['id']?>
              type=<?=$toolOption['type']?>
              <?=($toolOption['value'] ? 'checked' : '')?>
              name=<?=$key?>
            >
          <?php endforeach; ?>
        </fieldset>
        <fieldset class="three-options-fieldset">
          <h4>Viewer Options</h4>
          <?php foreach($viewerOptions as $key => $viewerOption): ?>
            <label for=<?=$key?>><?=$key?></label>
            <?php if($viewerOption['type'] === 'select'): ?>
              <select class="three-form-input" id=<?=$viewerOption['id']?> type="select" name=<?=$key?>>
                <?php foreach($viewerOption['options'] as $option): ?>
                  <option
                    value=<?=$option?>
                    <?=($viewerOption['value'] === $option ? 'selected' : '')?>
                    >
                    <?=$option?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          <?php endforeach; ?>
        </fieldset>
        <div class="three-form-button-container">
          <button
            class="submit"
            id="three-viewer-form-submit"><?=(array_key_exists('viewerId', $this->formOptions) ?
            'Update' : 'Add')?>
          </button>
          <?php if(array_key_exists('viewerId', $this->formOptions)) : ?>
            <button class="delete-confirm red" id="three-viewer-form-delete">Delete</button>
          <?php endif; ?>
        </div>
      </div>
  </fieldset>
</section>
