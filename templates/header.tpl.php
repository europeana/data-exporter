<?php
	$menu_items = array(
		//'search' => array(
		//	'href' => '/search/',
		//	'page' => 'search/',
		//	'title' => 'search'
		//),
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

	$Nav = new Penn\Html\Helpers\Nav( $menu_items );
?>
<div id="header">
	<a class="logo" href="/" title="<?php echo $Config->site_name; ?>"></a>
	<h1><?php echo $WebPage->heading; ?></h1>
	<?php echo $Nav->getNavAsUl( 'nav', $WebPage->page ); ?>
</div>
