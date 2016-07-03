<?php
echo "<?php\n";
?>
namespace <?=$generator->moduleNamespace()?>;

/**
 * Description of <?=$generator->moduleClass?>Assets
 *
 * @author <?=$generator->author?>
 */
 
class Assets extends yii\web\AssetBundle
{

    public $css	= [''];
    public $js	= [''];

    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . '/assets';
        parent::init();
    }

}
