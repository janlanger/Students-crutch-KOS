<?php //netteCache[01]000222a:2:{s:4:"time";s:21:"0.13764000 1284484971";s:9:"callbacks";a:1:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:66:"D:\Dokumenty\HTML\_bp\document_root/../app/templates/@layout.phtml";i:2;i:1284201041;}}}?><?php
// file …/templates/@layout.phtml
//

$_l = NLatteMacros::initRuntime($template, NULL, '84cfa46f76'); unset($_extends);

if (isset($presenter, $control) && $presenter->isAjax()) { NLatteMacros::renderSnippets($control, $_l, get_defined_vars()); }

if (NSnippetHelper::$outputAllowed) {
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<meta name="description" content="Nette Framework web application skeleton" /><?php if (isset($robots)): ?>
	<meta name="robots" content="<?php echo NTemplateHelpers::escapeHtml($robots) ?>" />
<?php endif ?>

	<title>Nette Application Skeleton</title>

	<link rel="stylesheet" media="screen,projection,tv" href="<?php echo NTemplateHelpers::escapeHtml($basePath) ?>/css/screen.css" type="text/css" />
	<link rel="stylesheet" media="print" href="<?php echo NTemplateHelpers::escapeHtml($basePath) ?>/css/print.css" type="text/css" />
	<link rel="shortcut icon" href="<?php echo NTemplateHelpers::escapeHtml($basePath) ?>/favicon.ico" type="image/x-icon" />

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
</head>

<body>
	<?php foreach ($iterator = $_l->its[] = new NSmartCachingIterator($flashes) as $flash): ?><div class="flash <?php echo NTemplateHelpers::escapeHtml($flash->type) ?>"><?php echo NTemplateHelpers::escapeHtml($flash->message) ?></div><?php endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>


<?php NLatteMacros::callBlock($_l, 'content', $template->getParams()) ?>
</body>
</html>
<?php
}
