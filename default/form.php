<?php
echo "<?php\n";
?>

use yii\helpers\Html;
//use yii\widgets\ActiveForm;
//use yii\helpers\ArrayHelper;


/* @var $this yii\web\View */
/* @var $model <?=$generator->moduleNamespace()?>\models\<?=$generator->modelClass?> */
/* @var $form yii\widgets\ActiveForm */
?>
<?php 

echo "<?php\n";
?>
    //"title" should be replaced with the required field name:
    //to do: check if it is possible to chose one specific table instead of title
    echo Html::textArea("title", "",
        array('id' => 'contentForm_title', 'class' => 'form-control autosize contentForm', 'rows' => '1', "placeholder" => "Insert new...")); ?>

<div class="mycookbook-form">
    
    <div class="form-group">
        <div class="checkbox">
            <label>
                
                <?php
                echo "<?php\n";
                    ?>
                    /* Modify textarea for mention input */ //this allows formatting for the given field 'contentForm_title'
                    echo \humhub\widgets\RichTextEditor::widget(array(
                        'id' => 'contentForm_title',
                    ));
                
                <?php
                echo "?>";
                    ?>
                
            </label>
        </div>
        
    </div>    

</div>


