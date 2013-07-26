<?php

/**
 * Preprocess and Process Functions SEE: http://drupal.org/node/254940#variables-processor
 * 1. Rename each function and instance of "soja" to match
 *    your subthemes name, e.g. if you name your theme "footheme" then the function
 *    name will be "footheme_preprocess_hook". Tip - you can search/replace
 *    on "soja".
 * 2. Uncomment the required function to use.
 */

/**
 * Override or insert variables into the html templates.
 */
function soja_preprocess_html(&$vars) {
	
	drupal_add_js(drupal_get_path('theme', 'soja') . '/js/gallery.js');
  // Load the media queries styles
  // Remember to rename these files to match the names used here - they are
  // in the CSS directory of your subtheme.
  $media_queries_css = array(
    'soja.responsive.style.css',
    'soja.responsive.gpanels.css'
  );
  //load_subtheme_media_queries($media_queries_css, 'soja');

 /**
  * Load IE specific stylesheets
  * AT automates adding IE stylesheets, simply add to the array using
  * the conditional comment as the key and the stylesheet name as the value.
  *
  * See our online help: http://adaptivethemes.com/documentation/working-with-internet-explorer
  *
  * For example to add a stylesheet for IE8 only use:
  *
  *  'IE 8' => 'ie-8.css',
  *
  * Your IE CSS file must be in the /css/ directory in your subtheme.
  */
  /* -- Delete this line to add a conditional stylesheet for IE 7 or less.
  $ie_files = array(
    'lte IE 7' => 'ie-lte-7.css',
  );
  load_subtheme_ie_styles($ie_files, 'soja');
  // */
}

/* -- Delete this line if you want to use this function
function soja_process_html(&$vars) {
}
// */

/**
 * Override or insert variables into the page templates.
 */
/* -- Delete this line if you want to use these functions
function soja_preprocess_page(&$vars) {
}

function soja_process_page(&$vars) {
}
// */

/**
 * Override or insert variables into the node templates.
 */
/* -- Delete this line if you want to use these functions
function soja_preprocess_node(&$vars) {
}

function soja_process_node(&$vars) {
}
// */

/**
 * Override or insert variables into the comment templates.
 */
/* -- Delete this line if you want to use these functions
function soja_preprocess_comment(&$vars) {
}

function soja_process_comment(&$vars) {
}
// */

/**
 * Override or insert variables into the block templates.
 */
/* -- Delete this line if you want to use these functions
function soja_preprocess_block(&$vars) {
}

function soja_process_block(&$vars) {
}
// */

/**
 * Add the Style Schemes if enabled.
 * NOTE: You MUST make changes in your subthemes theme-settings.php file
 * also to enable Style Schemes.
 */
/* -- Delete this line if you want to enable style schemes.
// DONT TOUCH THIS STUFF...
function get_at_styles() {
  $scheme = theme_get_setting('style_schemes');
  if (!$scheme) {
    $scheme = 'style-default.css';
  }
  if (isset($_COOKIE["atstyles"])) {
    $scheme = $_COOKIE["atstyles"];
  }
  return $scheme;
}
if (theme_get_setting('style_enable_schemes') == 'on') {
  $style = get_at_styles();
  if ($style != 'none') {
    drupal_add_css(path_to_theme() . '/css/schemes/' . $style, array(
      'group' => CSS_THEME,
      'preprocess' => TRUE,
      )
    );
  }
}
// */

/**
* Returns HTML for a slideshow formatter.
*
* @param $variables
*   An associative array containing:
*   - items: An array of images fields.
*   - image_style: An optional image style.
*
* @ingroup themeable
*/
function soja_field_slideshow($variables) {

	static $field_slideshow_id = -1;
	$field_slideshow_id++;

	// Change order if needed
	if (isset($variables['order'])) {
		if ($variables['order'] == 'reverse') $variables['items'] = array_reverse($variables['items']);
		elseif ($variables['order'] == 'random') shuffle($variables['items']);
	}

	// Generate slides
	$field_slideshow_zebra = 'odd';
	$slides_max_width = 0;
	$slides_max_height = 0;
	$slides_output = '';
	foreach ($variables['items'] as $num => $item) {
		$classes = array('field-slideshow-slide');
		$field_slideshow_zebra = ($field_slideshow_zebra == 'odd') ? 'even' : 'odd';
		$classes[] = $field_slideshow_zebra;
		if ($num == 0) $classes[] = 'first';
		elseif ($num == count($variables['items']) - 1) $classes[] = 'last';
		$slides_output .= '<div class="' . implode(' ', $classes) . '"' . ($num != 0 ? ' style="display:none;"' : '') . '>';

		// Generate the image html
		$image['path'] = $item['uri'];
		$image['alt'] = isset($item['alt']) ? $item['alt'] : '';
		if (isset($item['title']) && drupal_strlen($item['title']) > 0) $image['title'] = $item['title'];
		if (isset($variables['image_style'])) {
			$image['style_name'] = $variables['image_style'];
			$image_output = theme('image_style', $image);
		}
		else {
			$image_output = theme('image', $image);
		}

		// Get image sizes and add them the img tag, so height is correctly calctulated by Cycle
		if (isset($variables['image_style'])) {
			$image_path = image_style_path($variables['image_style'], $image['path']);
			// if thumbnail is not generated, do it, so we can get the dimensions
			if (!file_exists($image_path)) {
				image_style_create_derivative(image_style_load($variables['image_style']), $image['path'], $image_path);
			}
		}
		else $image_path = $image['path'];
		$image_dims = getimagesize($image_path);
		$image_output = drupal_substr($image_output, 0, -2) . $image_dims[3] . ' />';
		if ($image_dims[0] > $slides_max_width) $slides_max_width = $image_dims[0];
		if ($image_dims[1] > $slides_max_height) $slides_max_height = $image_dims[1];

		// Generate the caption
		if (isset($item['caption']) && $item['caption'] != '') {
			$caption_output = '<div class="field-slideshow-caption"><span class="field-slideshow-caption-text">' . $item['caption'] . '</span></div>';
		}
		else $caption_output = '';

		// Add links if needed
		$links = array('path' => 'image_output');
		if (isset($item['caption']) && $item['caption'] != '') $links['caption_path'] = 'caption_output';
		// Loop thru required links (because image and caption can have different links)
		foreach ($links as $link => $out) {
			if (!empty($item[$link])) {
				$path = $item[$link]['path'];
				$options = $item[$link]['options'];
				// When displaying an image inside a link, the html option must be TRUE.
				$options['html'] = TRUE;
				// Generate differnet rel attribute for image and caption, so colorbox doesn't double the image list
				if (isset($options['attributes']['rel'])) $options['attributes']['rel'] .= $out;
				$$out = l($$out, $path, $options);
			}
		}

		$slides_output .= $image_output . $caption_output;
		$slides_output .= '</div>'; // .fied-slideshow-slide div closed
	}

	if (count($variables['items']) == 1) {
		// don't add controls if there's only one image
		$variables['controls'] = 0;
		$variables['pager'] = '';
	}

	// Add controls if needed
	if (!empty($variables['controls'])) {
	//	$controls_output = '<div id="field-slideshow-' . $field_slideshow_id . '-controls" class="field-slideshow-controls"><a href="#" class="prev">' . t('Prev') . '</a> <a href="#" class="next">' . t('Next') . '</a></div>';
	}

	// Add thumbnails pager/carousel if needed
	if (isset($variables['pager']) && $variables['pager'] != '') {
		$pager_output = '';
		if ($variables['pager'] == 'image' || $variables['pager'] == 'carousel') {

			if ($variables['pager'] == 'carousel') {
				$image_style = $variables['carousel_image_style'];
				// Adds carousel wrapper and controls
				$pager_output .= '<div id="field-slideshow-' . $field_slideshow_id . '-carousel-wrapper" class="field-slideshow-carousel-wrapper"><a href="#" class="carousel-prev">«</a><div id="field-slideshow-' . $field_slideshow_id . '-carousel" class="field-slideshow-carousel">';
			}
			else $image_style = $variables['pager_image_style'];

			$pager_output .= '<ul id="field-slideshow-' . $field_slideshow_id . '-pager" class="field-slideshow-pager slides-' . count($variables['items']) . '">';
			foreach ($variables['items'] as $num => $item) {
				$image['path'] = $item['uri'];
				$image['alt'] = isset($item['alt']) ? $item['alt'] : '';
				if (isset($item['title']) && drupal_strlen($item['title']) > 0) $image['title'] = $item['title'];
				if ($image_style) {
					$image['style_name'] = $image_style;
					$image_output = theme('image_style', $image);
					// Get image sizes and add them the img tag
					$image_path = image_style_path($image_style, $image['path']);
					// if thumbnail is not generated, do it, so we can get the dimensions
					if (!file_exists($image_path)) {
						image_style_create_derivative(image_style_load($image_style), $image['path'], $image_path);
					}
				}
				else {
					$image_output = theme('image', $image);
					$image_path = $image['path'];
				}
				$image_dims = getimagesize($image_path);
				// adds width and height tags
				$image_output = drupal_substr($image_output, 0, -2) . $image_dims[3] . ' />';

				$pager_output .= '<li><a href="#">' . $image_output . '</a></li>';
			}
			$pager_output .= '</ul>';

			if ($variables['pager'] == 'carousel') {
				// close carousel wrapper and controls
				$pager_output .= '</div><a href="#" class="carousel-next">»</a></div>';
			}

		}
		else {
			// wrapper for number pager
			$pager_output = '<div id="field-slideshow-' . $field_slideshow_id . '-pager" class="field-slideshow-pager slides-' . count($variables['items']) . '"></div>';
		}
	}

	// Generate global markup
	$output = '<div id="field-slideshow-' . $field_slideshow_id . '-wrapper" class="field-slideshow-wrapper">';

	if (isset($controls_output) && $variables['controls_position'] == 'before') $output .= $controls_output;
	if (isset($pager_output) && $variables['pager_position'] == 'before') $output .= $pager_output;

	$classes = array('field-slideshow', 'field-slideshow-' . $field_slideshow_id, 'effect-' . $variables['fx'], 'timeout-' . $variables['timeout']);
	if (isset($variables['pager']) && $variables['pager'] != '') $classes[] = 'with-pager';
	if (isset($variables['controls'])) $classes[] = 'with-controls';
	$output .= '<div class="' . implode(' ', $classes) . '" style="width:' . $slides_max_width . 'px; height:' . $slides_max_height . 'px">';
	$output .= $slides_output; // adds the slides
	$output .= '</div>'; // .field-slideshow

	if (isset($controls_output) && $variables['controls_position'] == 'after') $output .= $controls_output;
	if (isset($pager_output) && $variables['pager_position'] == 'after') $output .= $pager_output;

	$output .= '</div>'; // .field-slideshow-wrapper div closed

	// Add the Cycle plugin and the Js code
	drupal_add_js(drupal_get_path('module', 'field_slideshow') . '/js/jquery.cycle.all.min.js');
	if (isset($variables['pager']) && $variables['pager'] == 'carousel') {
		drupal_add_js(drupal_get_path('module', 'field_slideshow') . '/js/jcarousellite_1.0.1.min.js');
	}
	drupal_add_js(drupal_get_path('module', 'field_slideshow') . '/js/field_slideshow.js');

	// Add js variables
	unset($variables['items']);
	drupal_add_js(array('field_slideshow' => array('field-slideshow-' . $field_slideshow_id => $variables)), 'setting');

	// Add css
	drupal_add_css(drupal_get_path('module', 'field_slideshow') . '/field_slideshow.css');

	return $output;
}