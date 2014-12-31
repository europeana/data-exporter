<?php

	$menu_items = array(
		'search' => array(
			'href' => '/search/',
			'page' => 'search/',
			'title' => 'search'
		),
		'tag' => array(
			'href' => '/my-europeana/tag-list/',
			'page' => 'my-europeana/tag-list/',
			'title' => 'tag list'
		),
		'queue' => array(
			'href' => '/queue/',
			'page' => 'queue/',
			'title' => 'queue'
		)
	);

	$Nav = new App\Nav( $menu_items );
?>
<div id="header">
	<a class="logo" href="/" title="<?php echo $config['site-name']; ?>"></a>
	<h1><?php echo $Page->heading; ?></h1>
	<?php echo $Nav->getNavAsUl( 'nav', $Page->page ); ?>
</div>
