<?php
echo "<?php\n";
?>
namespace <?=$generator->moduleNamespace()?>\widgets;



/**
 * WallEntryWidget is used to display a model inside the stream.
 *
 * This Widget will used by the Model in Method getWallOut().
 *
 
 * @since 0.5
 * @author <?=$generator->author?>
 */
class WallEntry extends \humhub\modules\content\widgets\WallEntry
{

    public $editRoute = '/<?=$generator->moduleID?>/<?=$generator->moduleID?>/edit';//to do: check the values here, not sure about moduleID
    
    public function run()
    {
        //Hint: insert here a check in case you do not wish to edit a specific post, setting then $this->editRoute = '';  

        return $this->render('entry', array('my<?=$generator->moduleID?>s' => $this->contentObject,
                    'user' => $this->contentObject->content->user,
                    'contentContainer' => $this->contentObject->content->container));
    }

}