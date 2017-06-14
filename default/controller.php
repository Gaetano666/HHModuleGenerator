<?php
/**
 * This is the template for generating a controller class within a module.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\module\Generator */

echo "<?php\n";
?>

namespace <?=$generator->moduleNamespace()?>\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\components\ContentActiveRecord;
use yii\web;
use yii\web\View;
use <?=$generator->moduleNamespace()?>\models\<?=$generator->modelClass?>;

/**
 * Description of <?=$generator->moduleClass?> Controller
 *
 * @author <?=$generator->author?>
 */

class <?=ucwords($generator->moduleID)?>Controller extends ContentContainerController
{
    public $wallEntryClass = '<?=$generator->moduleNamespace()?>\widgets\WallEntry';
    
    /**
    * Let's specify the actions to be taken
    * see: https://www.humhub.org/docs/guide-dev-module-stream.html
    */
    public function actions()
    {
        return array(
            'stream' => array(
                'class' => \<?=$generator->moduleNamespace()?>\components\StreamAction::className(),
                'mode' => \<?=$generator->moduleNamespace()?>\components\StreamAction::MODE_NORMAL,
                'contentContainer' => $this->contentContainer
            ),
        );
    }

    /**
     * Shows the model tab
     */
    public function actionShow()
    {
        return $this->render('show', array(
                    'contentContainer' => $this->contentContainer                    
        ));

    }
    
     /**
     * Edit the model
     */
    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');

        $edited = false;
        $model = <?=$generator->modelClass?>::findOne(['id' => $id]);
        
        if (!$model->content->canWrite()) {
            throw new HttpException(403, 'Access denied!'); //to do: translate
        }
        if ($model->load(Yii::$app->request->post())) {
            // Reload record to get populated updated_at field
            if($model->validate() && $model->save()) {
                $model = <?=$generator->modelClass?>::findOne(['id' => $id]);
                return $this->renderAjaxContent($model->getWallOut(['justEdited' => true]));
            } else {
                Yii::$app->response->statusCode = 400;
            }
            
        }

        return $this->renderAjax('edit', array('post' => $model, 'edited' => $edited));
    }
    
    /**
     * Create the model 
     */
    public function actionCreate()
    {
        //create a new model
        $mypostmodel = new <?=$generator->modelClass?>();
        
        //set the parameters of the Model e.g.
        //$mypostmodel->DBcolumnName=Yii::$app->request->post('FormFieldName');
        //the below fields are automatically created, but you may need to customize them
        //note: you may need to remove the ID field
        <?php foreach ($labels as $name => $label): ?>
            <?= '$mypostmodel->'.$name.'=Yii::$app->request->post(\''.$name.'\')'?>;
        <?php endforeach; ?>
        
        return \<?=$generator->moduleNamespace()?>\widgets\WallCreateForm::create($mypostmodel, $this->contentContainer);
    }
    
    /**
     * Deletes an existing <?=$generator->modelClass?> model.
     * If deletion is successful, the browser will be redirected to the 'show' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['show']);
    }

    /**
     * Finds the <?=$generator->modelClass?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return <?=$generator->modelClass?> the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = <?=$generator->modelClass?>::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
