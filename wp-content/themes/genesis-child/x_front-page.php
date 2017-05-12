<?php
	namespace 123Blog;

	$templates	= array( 'front-page.twig' );

	$data		= array(
			'page'	=> array(
				'link'	=> Content::instance()->getCurrentPageLink(),
				'title'	=> get_the_title(),
				'sections'	=> Content::instance->getSections()
			)
	);

	require CORE_INC . 'common.php';