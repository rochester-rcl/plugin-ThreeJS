<!DOCTYPE html>
<html class="<?php echo get_theme_option('Style Sheet'); ?>" lang="<?php echo get_html_lang(); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=yes" />
    <?php if ($description = option('description')): ?>
    <meta name="description" content="<?php echo $description; ?>" />
    <?php endif; ?>

    <?php
    if (isset($title)) {
        $titleParts[] = strip_formatting($title);
    }
    $titleParts[] = option('site_title');
    ?>
    <title><?php echo implode(' &middot; ', $titleParts); ?></title>

    <?php echo auto_discovery_link_tags(); ?>

    <?php fire_plugin_hook('public_head',array('view'=>$this)); ?>
    <!-- Stylesheets -->
    <?php
    queue_css_file('three-viewer-header');
    queue_css_file('three-viewer');
    queue_css_file(array('iconfonts', 'skeleton','style'));
    load_react_css(FALSE);
    if ($this->pageTitle === 'browse') {
      queue_css_file('three-viewer-browse');
    }
    echo head_css();
    ?>
    <?php
      queue_js_url("https://use.fontawesome.com/aadd731529.js");
    ?>
    <?php queue_js_file('vendor/selectivizr', 'javascripts', array('conditional' => '(gte IE 6)&(lte IE 8)')); ?>
    <?php queue_js_file('vendor/respond'); ?>
    <?php queue_js_file('vendor/jquery-accessibleMegaMenu'); ?>
    <?php queue_js_file('globals'); ?>
    <?php if ($this->pageTitle === 'browse') {
      queue_js_file('lazyload.min', 'js/lazyload');
      }
    ?>
    <?php echo head_js(); ?>
</head>
 <?php echo body_tag(array('id' => @$bodyid, 'class' => @$bodyclass)); ?>
    <?php fire_plugin_hook('public_body', array('view'=>$this)); ?>
<div id="root" class="viewer-embed">
