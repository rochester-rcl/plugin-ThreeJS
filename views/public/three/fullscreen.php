<?php
  echo $this->partial('common/header-three-fullscreen.php');
  $apiKey = js_escape(get_user_api_key());
  $publicUrl = js_escape(url('') . 'three');
  $root = js_escape(url('/'));
?>
</div>
<script type="text/javascript">
// this needs to be in a file
  var key = <?=$apiKey?>;
  window.rootUrl = <?=$root?>;
  window.publicUrl = <?=$publicUrl?>;
  var storageKey = localStorage.getItem("omekaApiKey");
  if (key) {
    if (storageKey !== key) {
      // different user, same browser? Or new key for some reason
      localStorage.setItem('omekaApiKey', key);
    }
    // do nothing
  } else {
    // if not logged in, remove the key
    localStorage.removeItem("omekaApiKey");
  }
</script>
<?php foreach(load_js_bundle() as $jsFile): ?>
  <script type="text/javascript" src=<?=$jsFile?>></script>
<?php endforeach; ?>
