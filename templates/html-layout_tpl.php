<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $config['lang']; ?>">
<head>
<?php echo $WebPage->getMeta(); ?>
<?php echo $WebPage->getLinks(); ?>
<?php echo $WebPage->getStyles(); ?>
<?php echo $WebPage->getScripts( 'head' ); ?>
<title><?php echo $WebPage->title; ?></title>
</head>
<body>
<?php include 'header_tpl.php'; ?>
<?php if ( $WebPage->getViewFilepath() !== false ) { include $WebPage->getViewFilepath(); } else { echo $WebPage->html; } ?>
<?php APPLICATION_ENV === 'development' ? include 'debug_tpl.php' : null; ?>
<?php echo $WebPage->getScripts( 'body' ); ?>
</body>
</html>