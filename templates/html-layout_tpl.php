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
<?php $Page->getViewFilepath() !== false ? include $Page->getViewFilepath() : null; ?>
<?php APPLICATION_ENV === 'development' ? include 'debug_tpl.php' : null; ?>
<?php echo $Page->getScripts( 'body' ); ?>
</body>
</html>