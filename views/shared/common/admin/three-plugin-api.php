<?php
  $itemId = js_escape($this->itemId);
  $fileEndpoint = js_escape($this->fileEndpoint);
  $saveEndpoint = js_escape($this->saveEndpoint);
  $fileDeleteEndpoint = js_escape((isset($this->fileDeleteEndpoint) ? $this->fileDeleteEndpoint : NULL));
  $action = js_escape($this->action);
?>
<script type="text/javascript">
  addThreeViewer(<?=$itemId?>, <?=$fileEndpoint?>, <?=$saveEndpoint?>, <?=$fileDeleteEndpoint?>, <?=$action?>);
</script>
