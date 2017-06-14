<?php
echo "<?php\n";
?>

echo \<?=$generator->moduleNamespace()?>\widgets\WallCreateForm::widget([
    'contentContainer' => $contentContainer,    
    'submitButtonText' => 'Insert new...',
]);
<?php
echo "?>";
?>



<?php
echo "<?php\n";
?>

$canCreate= $contentContainer->permissionManager->can(new \<?=$generator->moduleNamespace()?>\permissions\CreatePost());


echo \humhub\modules\content\widgets\Stream::widget(array(
    'contentContainer' => $contentContainer,
    'streamAction' => '/<?=$generator->moduleID?>/<?=$generator->moduleID?>/stream',
    'messageStreamEmpty' => ($canCreate) ?
            '<b>There are no post yet!</b><br>Be the first and create one...' : '<b>There are no post yet!</b>',
    'messageStreamEmptyCss' => ($canCreate) ? 'placeholder-empty-stream' : '',

));
<?php
echo "?>";
?>
