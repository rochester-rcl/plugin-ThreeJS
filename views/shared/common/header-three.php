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
    <div id="primary-nav-viewer" role="navigation">
      <?php
          echo link_to_home_page(theme_logo(), array("class" => "nav-viewer-home-icon"));
          echo public_nav_main();
      ?>
      <div class="viewer-nav-search">
        <?php
          if (get_theme_option('use_advanced_search') === null || get_theme_option('use_advanced_search')) {
            echo search_form(array('show_advanced' => true));
          } else {
            echo search_form();
          }
        ?>
      </div>
    </div>
    <script type="text/javascript">
      // take care of the search icon first
      var searchSubmit = document.getElementById('submit_search');
      searchSubmit.innerHTML = '<i class="fa fa-search" aria-hidden="true"></i>';
      var navContainer = document.getElementById('primary-nav-viewer');
      var nav = navContainer.getElementsByClassName('navigation');
      var navItems = nav[0].childNodes;
      for (var i=0; i < navItems.length; i++) {
        var element = navItems[i];
        if (element.nodeType !== Node.TEXT_NODE) {
          checkDropdown(element);
        }
      }

      function checkDropdown(element) {
        var dropdown = element.getElementsByTagName('ul');
        if (dropdown.length > 0) {
            element.className = "hide";
            element.onmouseover = function(event) {
              event.preventDefault();
              if (element.className !== 'show') {
                setElementClassNames(dropdown, "fade-in");
                setTimeout(function() {
                  element.className =  "show";
                  setElementClassNames(dropdown, "fade-in show");
                }, 1);
              }
            }
            element.onmouseleave = function(event) {
              if (element.className === 'show') {
                setElementClassNames(dropdown, "hide");
                setTimeout(function() {
                  element.className =  "hide";
                  setElementClassNames(dropdown, "fade-out hide");
                }, 1);
              }
            }
            var ul = element.getElementsByTagName('ul')[0];
            ul.onmouseleave = function(event) {
              var element = this.parentNode;
              if (element.className === 'show') {
                setElementClassNames(dropdown, "hide");
                setTimeout(function() {
                  element.className =  "hide";
                  setElementClassNames(dropdown, "fade-out hide");
                }, 1);
              }
            }
            for (var i=0; i < dropdown.length; i++) {
              var children = dropdown[i].getElementsByTagName('li');
              for (var j=0; j < children.length; j++) {
                var child = children[j];
                child.onclick = function(event) {
                  var links = this.getElementsByTagName('a');
                  window.location = links[0].href;
                }
              }
            }
          }
        }

        function setElementClassNames(elements, className) {
          for (var i=0; i < elements.length; i++) {
            var element = elements[i];
            element.className = className;
          }
        }
    </script>
<div id="root">
