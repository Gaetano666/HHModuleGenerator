<?php
echo "<?php\n";
?>

namespace <?= $generator->moduleNamespace() ?>;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;


/**
 * Description of <?=$generator->moduleClass?> Events
 *
 * @author <?=$generator->author?>
 */

class Events extends \yii\base\Object
{

    /**
     * On build of a Space Navigation, check if this module is enabled.
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onSpaceMenuInit($event)
    {
        $space = $event->sender->space;

        // Is Module enabled on this workspace?
        if ($space->isModuleEnabled('<?=$generator->moduleID?>')) {
            $event->sender->addItem(array(
                'label' => '<?=$generator->moduleID?>',
                'group' => 'modules',
                'url' => $space->createUrl('/<?=$generator->moduleID?>/<?=$generator->moduleID?>/show'),
                'icon' => '<i class="fa fa-question-circle"></i>',
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == '<?=$generator->moduleID?>'),
            ));
        }
    }
    
    public static function onWallEntryControlsInit($event)
    {
        $object = $event->sender->object;
        
        if(!$object instanceof <?=$generator->modelClass?>) {           
            return;
        }
        

    }
}