<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (empty($arResult)) {
    return;
}

$userGroups = \CUser::GetUserGroup($GLOBALS['USER']->getId());

echo '<div class="top-nav">';
foreach ($arResult as $item) {
    if (!array_uintersect($item['PARAMS']['deniedGroups'], $userGroups, 'strcasecmp')) {
        $class = 'btn inline-block menu-item';
        if ($item['SELECTED']) {
            $class .= ' selected';
        }
        $attr = '';
        if (strpos($item['LINK'], 'http') === 0) {
            $attr .= ' target="blank"';
        }
        ?><a href="<?=$item['LINK']?>"<?=$attr?> class="<?=$class?>"><?=$item['TEXT']?></a>
        <?php
    }
}
echo '</div>';
