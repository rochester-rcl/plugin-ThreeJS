<h2>Three JS Config Options</h2>
<div class="field">
  Check this box to run to attempt resolve media issues with the viewer (i.e. icons missing).
  You should only run this if the files weren't patched when the plugin was installed. You
  may need to clear your cache in order to see if the changes took.
  <br />
  <br />
  <i>The files in the directory ThreeJS/views/public/build/static/css must be writable by Omeka.</i>
</div>
<br />

<div class="field">
    <div class="inputs two columns omega">
        <label for="threejs_patch_media"><?php echo __('Patch media files'); ?></label>
        <?php echo $this->formCheckbox('threejs_patch_media', null,
        array('checked' => (bool) get_option('threejs_patch_media'))); ?>
    </div>
</div>
