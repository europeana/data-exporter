<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $config['lang']; ?>">
<head>
<?php include 'meta_tpl.php'; ?>
<?php include 'links_tpl.php'; ?>
<?php echo $Page->script_head; ?>
<title><?php echo $Page->title; ?></title>
</head>
<body>
<?php include 'header_tpl.php'; ?>
<?php include $Page->page . '_view.php'; ?>
<?php if ( APPLICATION_ENV === 'development' ) { include 'debug_tpl.php'; } ?>
<?php echo $Page->script_body; ?>
</body>
</html>