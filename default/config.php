<?php
echo "<?php\n";
?>
use humhub\modules\space\widgets\Menu;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\user\models\User;
use humhub\widgets\TopMenu;
use humhub\modules\content\widgets\WallEntryControls;

return [
    'id' => '<?=$generator->moduleID?>',
    'class' => '<?=$generator->moduleClass?>',
    'namespace' => '<?=$generator->moduleNamespace()?>',
    'events' => [
        //['class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => ['humhub\modules\mail\Events', 'onTopMenuInit']],
        array('class' => Menu::className(), 'event' => Menu::EVENT_INIT, 'callback' => array('<?=$generator->moduleNamespace()?>\Events', 'onSpaceMenuInit')),
        array('class' => WallEntryControls::className(), 'event' => WallEntryControls::EVENT_INIT, 'callback' => array('<?=$generator->moduleNamespace()?>\Events', 'onWallEntryControlsInit')),
    ],
];
?>