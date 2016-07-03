<?php
echo "<?php\n";
?>

use yii\helpers\Html;
<?php
echo "?>";
?>


<?php echo "<?php";?> echo Html::beginForm(); <?php echo "?>";?>
 


<!--//this will be the content of the stream
//e.g.:
//echo "description: ".[WallEntry object]->description."<br/>";
//of course, you will need to customize the data view to arrange the layout you need
//below fields are auto generated based on th tables available into the system, but data are really rough -->
<?php
echo "<?php\n";
?>
<?php foreach ($labels as $name => $label): ?>          
          echo "<?=ucwords($name)?>: ".<?='$my'.$generator->moduleID.'s->'.$name?>."<br/>";
<?php endforeach; ?>

<?php
echo "?>";
?>          
          
<?php
echo "<?php";
?> echo Html::endForm(); <?php
echo "?>";
?>


