<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $config['lang']; ?>">
<head>
<?php echo $Page->getMeta(); ?>
<?php echo $Page->getLinks(); ?>
<?php echo $Page->getStyles(); ?>
<?php echo $Page->getScripts( 'head' ); ?>
<title><?php echo $Page->title; ?></title>
</head>
<body>
<?php include 'header_tpl.php'; ?>
<?php if ( $Page->getViewFilepath() !== false ) { include $Page->getViewFilepath(); } else { echo $Page->html; } ?>
<?php APPLICATION_ENV === 'development' ? include 'debug_tpl.php' : null; ?>
<?php echo $Page->getScripts( 'body' ); ?>
</body>
</html>