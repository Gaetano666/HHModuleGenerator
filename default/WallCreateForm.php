<?php
echo "<?php\n";
?>
namespace <?=$generator->moduleNamespace()?>\widgets;


class WallCreateForm extends \humhub\modules\content\widgets\WallCreateContentForm
{

    /**
     * @inheritdoc
     */
    public $submitUrl = '/<?=$generator->moduleID?>/<?=$generator->moduleID?>/create'; //to do: check the values here, not sure about moduleID
    

    /**
     * @inheritdoc
     */
    public function renderForm()
    {
        return $this->render('form', array());
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->contentContainer instanceof \humhub\modules\space\models\Space) {
            
            if (!$this->contentContainer->permissionManager->can(new \<?=$generator->moduleNamespace()?>\permissions\CreatePost())) {
                return;
            }
        }

        return parent::run();
    }

}

?>