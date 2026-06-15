<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package HelloElementor
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

$viewport_content = apply_filters('hello_elementor_viewport_content', 'width=device-width, initial-scale=1');
$enable_skip_link = apply_filters('hello_elementor_enable_skip_link', true);
$skip_link_url = apply_filters('hello_elementor_skip_link_url', '#content');
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="<?php echo esc_attr($viewport_content); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php if (!isset($_COOKIE['UNVEILED_KEY'])) { /*
		if (is_home() || is_front_page()) { ?>
			<style>
				body {
					opacity: 0;
				}

				.absolute.inset-0.z-0 img {
					max-width: 100vw;
					max-height: 100vh;
				}

				.relative.min-h-screen.w-full.overflow-hidden.bg-white {
					min-height: 0;
				}

				.relative.min-h-screen.w-full.overflow-hidden.bg-white {
					z-index: 9999999999999999999;
				}

				body h1.text-blue-600 {
					color: #1762B8 !important;
					font-weight: 600;
				}

				img[src="https://accordhub.in/launch/pulse-unveil--craftedqdev.replit.app_files/image_1764931467294-CuPp7HYt.png"] {
					display: none;
				}
			</style>
			<script>
				document.addEventListener("DOMContentLoaded", () => {
					jQuery('body').css('opacity', '1');
					function getCookie(name) {
						return document.cookie
							.split("; ")
							.find(row => row.startsWith(name + "="))
							?.split("=")[1];
					}

					if (getCookie("UNVEILED_KEY") === "true") {
						document.getElementById("root")?.remove();
					}
				});
			</script>
		<?php }
	*/ } ?>
	<?php

	if (!isset($_COOKIE['UNVEILED_KEY'])) { /*
		if (is_home() || is_front_page()) {
			?>
	<link href="https://accordhub.in/launch/pulse-unveil--craftedqdev.replit.app_files/css2.css?<?php echo time(); ?>"
		rel="stylesheet">
	<script type="module" crossorigin=""
		src="https://accordhub.in/launch/pulse-unveil--craftedqdev.replit.app_files/index-Calfo9Cp.js?<?php echo time(); ?>"></script>
	<link rel="stylesheet" crossorigin=""
		href="https://accordhub.in/launch/pulse-unveil--craftedqdev.replit.app_files/index-CIyPdZOv.css?<?php echo time(); ?>">
			<?php
		}
	*/ }
	?>

	<?php wp_head(); ?>

	<?php if (isset($_GET['check_screen'])) { ?>
		<script>
			jQuery(document).ready(function($){
				var h = $(window).height();
				var w = $(window).width();
				alert(`Width: ${w}px | Height: ${h}px`);
			});
		</script>
	<?php } ?>


	<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css" /> -->
	<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/fancybox.css" />
	<!-- <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js"></script> -->
	<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/fancybox.umd.js"></script>

	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-81NT1XZC30"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'G-81NT1XZC30');
	</script>
</head>

<body <?php body_class(); ?>>
	<?php

	if (!isset($_COOKIE['UNVEILED_KEY'])) { /* 
		if (is_home() || is_front_page()) {
			?>
			<div id="root"></div>
			<?php
		}
	*/ }
	?>
	<?php wp_body_open(); ?>

	<?php if ($enable_skip_link) { ?>
		<a class="skip-link screen-reader-text"
			href="<?php echo esc_url($skip_link_url); ?>"><?php echo esc_html__('Skip to content', 'hello-elementor'); ?></a>
	<?php } ?>

	<?php
	if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('header')) {
		if (hello_elementor_display_header_footer()) {
			if (did_action('elementor/loaded') && hello_header_footer_experiment_active()) {
				get_template_part('template-parts/dynamic-header');
			} else {
				get_template_part('template-parts/header');
			}
		}
	}
