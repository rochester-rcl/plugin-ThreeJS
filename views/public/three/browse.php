<?php
$pageTitle = __($this->browseTitle);
echo $this->partial('common/header-three.php', array('pageTitle' => 'browse'));
?>
<nav class="gallery-sort-filters">
    <?php if ($total_results > 0): ?>

    <?php
    $sortLinks[__('Title')] = 'Dublin Core,Title';
    $sortLinks[__('Creator')] = 'Dublin Core,Creator';
    $sortLinks[__('Date Added')] = 'added';
    ?>
    <div id="gallery-sort-links">
        <span class="sort-label"><?php echo __('Sort by: '); ?></span>
        <?php echo browse_sort_links($sortLinks); ?>
    </div>
    <?php echo item_search_filters(); ?>
    <?php echo pagination_links(); ?>
    <?php endif; ?>
</nav>
<h1 class="item-type-title">
  <?php echo $pageTitle . ' (' . $total_results . ')'; ?>
</h1>

<div class="item-records" id="item-records-block">
<?php foreach (loop('items') as $item): ?>
<?php $itemTitle = metadata('item', array('Dublin Core', 'Title'));
      $itemURL = url('three/' . $item->_viewer_id);
 ?>
 <?php if (metadata('item', 'has files')): ?>
  <div class="item-flexbox-record">
      <span class="item-flexbox-record-title"><?php echo $itemTitle ?></span>
      <a class="item-flexbox-record-link" href="<?php echo $itemURL; ?>">
        <div class="item-img">
            <?php echo three_lazy_load_image('square_thumbnail', $item); ?>
        </div>
      </a>
  </div><!-- end class="item hentry" -->
  <?php endif; ?>
  <?php fire_plugin_hook('public_items_browse_each', array('view' => $this, 'item' =>$item)); ?>
<?php endforeach; ?>
</div>
<?php echo pagination_links(); ?>


<?php fire_plugin_hook('public_items_browse', array('items'=>$items, 'view' => $this)); ?>

<script type="text/javascript">
  var lazyLoad = new LazyLoad();
</script>

<?php echo foot(); ?>
