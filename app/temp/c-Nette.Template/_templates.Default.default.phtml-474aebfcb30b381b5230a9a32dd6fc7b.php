<?php //netteCache[01]000230a:2:{s:4:"time";s:21:"0.11182700 1284484971";s:9:"callbacks";a:1:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:74:"D:\Dokumenty\HTML\_bp\document_root/../app/templates/Default/default.phtml";i:2;i:1284484711;}}}?><?php
// file …/templates/Default/default.phtml
//

$_l = NLatteMacros::initRuntime($template, NULL, '7f9b7bb573'); unset($_extends);


//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lbd8a1af5620_content')) { function _lbd8a1af5620_content($_l, $_args) { extract($_args)
?>
<h1>Studentova berlička III - import KOSu</h1>

<a href="<?php echo NTemplateHelpers::escapeHtml($control->link("Default:analyze")) ?>">Analýza XML</a><?php
}}

//
// end of blocks
//

if ($_l->extends) { ob_start(); }
elseif (isset($presenter, $control) && $presenter->isAjax()) { NLatteMacros::renderSnippets($control, $_l, get_defined_vars()); }

if (NSnippetHelper::$outputAllowed) {
if (!$_l->extends) { call_user_func(reset($_l->blocks['content']), $_l, get_defined_vars()); }  
}

if ($_l->extends) { ob_end_clean(); NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); }
