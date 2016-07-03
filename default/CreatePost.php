<?php
echo "<?php\n";
?>


namespace <?=$generator->moduleNamespace()?>\permissions;

use humhub\modules\space\models\Space;

/**
 * CreatePost Permission
 * Those allow the user - if s/he has rights - to access the module
 */
class CreatePost extends \humhub\libs\BasePermission
{

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
    ];
    
    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_USER
    ];

    /**
     * @inheritdoc
     */
    protected $title = "Create <?=$generator->moduleID?>";

    /**
     * @inheritdoc
     */
    protected $description = "Allows the user to create <?=$generator->moduleID?>";

    /**
     * @inheritdoc
     */
    protected $moduleId = '<?=$generator->moduleID?>';

}
