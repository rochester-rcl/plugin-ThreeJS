<?php
    echo head(array('title' => __('ThreeJS Viewer')));
    echo flash();
?>
<div>
  <table class="full">
    <thead>
        <tr>
            <?php $browseHeadings[__('Title')] = 'Title'; ?>
            <?php echo browse_sort_links($browseHeadings, array(
                'link_tag' => 'th scope="col"', 'list_tag' => ''));
            ?>
        </tr>
    </thead>
    <tbody>
<?php foreach(loop('ThreeViewer') as $viewer): ?>
    <tr>
            <td>
                <span class="title">
                    <a href="<?php echo html_escape(record_url('ThreeViewer')); ?>">
                        <?php echo metadata('ThreeViewer', 'viewer_name'); ?>
                    </a>
                </span>
                <ul class="action-links group">
                    <li><a class="edit" href="<?php echo html_escape(record_url('ThreeViewer', 'edit')); ?>">
                        <?php echo __('Edit'); ?>
                    </a></li>
                    <li><a class="delete-confirm" href="<?php echo html_escape(record_url('ThreeViewer', 'delete-confirm')); ?>">
                        <?php echo __('Delete'); ?>
                    </a></li>
                </ul>
            </td>
        </tr>
  <?php endforeach; ?>
</tbody>
</table>
  <a class="add-viewer button small green" href="<?php echo html_escape(url('three-viewer/index/add')); ?>"><?php echo __('Add a ThreeJS Viewer'); ?></a>
</div>
