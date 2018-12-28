<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<title><?php wp_title(''); ?><?php if(wp_title('', false)) { echo ' :'; } ?> <?php bloginfo('name'); ?></title>

		<link href="//www.google-analytics.com" rel="dns-prefetch">
        <link href="<?php echo get_template_directory_uri(); ?>/img/icons/favicon.ico" rel="shortcut icon">
        <link href="<?php echo get_template_directory_uri(); ?>/img/icons/touch.png" rel="apple-touch-icon-precomposed">

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="<?php bloginfo('description'); ?>">

		<?php wp_head(); ?>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link href="https://fonts.googleapis.com/css?family=Charmonman:700" rel="stylesheet">
		<!-- <link href="https://fonts.googleapis.com/css?family=Alegreya:9\600|Archivo+Black|Noto+Sans+SC" rel="stylesheet"> -->
		<script>
        // conditionizr.com
        // configure environment tests
        conditionizr.config({
            assets: '<?php echo get_template_directory_uri(); ?>',
            tests: {}
        });
        </script>

	</head>
	<body <?php body_class(); ?>>

		<!-- wrapper -->
		<div class="wrapper">

			<!-- header -->
			<header class="header clear" role="banner">

				<div class="topnav" id="myTopnav">
					<nav class="desktopNav" role="navigation">
						<a class="logo" href="<?php echo home_url(); ?>">
							<!-- svg logo - toddmotto.com/mastering-svg-use-for-a-retina-web-fallbacks-with-png-script -->
							<img src="<?php echo get_template_directory_uri(); ?>/img/earthsigndesign-logo.png" alt="Logo" class="logo-img">
							<h2 class="homeLogo2">earth sign design</h2>
						</a>

						<?php html5blank_nav(); ?>
					</nav>
					<div class="mobileNav" id="mobileNav">
						<a class="logo" href="<?php echo home_url(); ?>">
							<!-- svg logo - toddmotto.com/mastering-svg-use-for-a-retina-web-fallbacks-with-png-script -->
							<img src="<?php echo get_template_directory_uri(); ?>/img/earthsigndesign-logo.png" alt="Logo" class="logo-img">
							<h2 class="homeLogo2">earth sign design</h2>
						</a>
						<a href="javascript:void(0);" class="icon" onclick="toggleNav()"><i class="fa fa-bars"></i></a>
					</div>
					<nav class="toggleNav" id="toggleNav">
						<?php html5blank_nav(); ?>
					</nav>
				</div>

			</header>
			<!-- /header -->
