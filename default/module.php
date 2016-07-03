<?php
echo "<?php\n";
?>

namespace <?= $generator->moduleNamespace() ?>;

use Yii;
use yii\helpers\Url;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;

/**
 * Description of <?=$generator->moduleClass?>Module
 *
 * @author <?=$generator->author?>
 */
 
class Module extends ContentContainerModule
{

    public $controllerNamespace = '<?= $generator->moduleNamespace() ?>\controllers';
    
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
/**


     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
    	return [
    		Space::className(),
                User::className(),
    	];
    }
    
    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof \humhub\modules\space\models\Space) {
            return [
                new permissions\CreatePost(),
            ];
        }

        return [];
    }
}
