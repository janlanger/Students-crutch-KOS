<?php //netteCache[01]000230a:2:{s:4:"time";s:21:"0.81804400 1284485040";s:9:"callbacks";a:1:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:74:"D:\Dokumenty\HTML\_bp\document_root/../app/templates/Default/analyze.phtml";i:2;i:1284485002;}}}?><?php
// file …/templates/Default/analyze.phtml
//

$_l = NLatteMacros::initRuntime($template, NULL, 'c15bb363e0'); unset($_extends);


//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lb3df152d154_content')) { function _lb3df152d154_content($_l, $_args) { extract($_args)
;
}}

//
// end of blocks
//

if ($_l->extends) { ob_start(); }
elseif (isset($presenter, $control) && $presenter->isAjax()) { NLatteMacros::renderSnippets($control, $_l, get_defined_vars()); }

if (NSnippetHelper::$outputAllowed) {
?>

<?php if (!$_l->extends) { call_user_func(reset($_l->blocks['content']), $_l, get_defined_vars()); }  
}

if ($_l->extends) { ob_end_clean(); NLatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); }
