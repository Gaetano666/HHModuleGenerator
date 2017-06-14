<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\module\Generator */

?>
<div class="module-form">
<?php
    echo $form->field($generator, 'moduleClass');
    echo $form->field($generator, 'moduleID');
    echo $form->field($generator, 'author');
    echo $form->field($generator, 'tableName'); //to do: reenter this once the module is able to generate the Model automatically
    echo $form->field($generator, 'modelClass');
    echo $form->field($generator, 'moduleDsc')->textarea();
?>
</div>
