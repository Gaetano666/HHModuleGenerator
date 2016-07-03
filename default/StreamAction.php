<?php
echo "<?php\n";
?>
namespace <?=$generator->moduleNamespace()?>\components;

use humhub\modules\content\components\actions\ContentContainerStream;
use humhub\modules\<?=$generator->moduleID?>\models\<?=$generator->modelClass?>;

class StreamAction extends ContentContainerStream
{

    public function setupFilters()
    {
        //see documentation here: https://www.humhub.org/docs/guide-dev-module-stream.html
        $this->activeQuery->andWhere(['content.object_model' => <?=$generator->modelClass?>::className()]);
        
        //insert here the filters for your module, see e.g. Polls filter sample
        
//        if (in_array('polls_notAnswered', $this->filters) || in_array('polls_mine', $this->filters)) {
//
//            $this->activeQuery->leftJoin('poll', 'content.object_id=poll.id AND content.object_model=:pollClass', [':pollClass' => Poll::className()]);
//
//            if (in_array('polls_notAnswered', $this->filters)) {
//                $this->activeQuery->leftJoin('poll_answer_user', 'poll.id=poll_answer_user.poll_id AND poll_answer_user.created_by=:userId', [':userId' => Yii::$app->user->id]);
//                $this->activeQuery->andWhere(['is', 'poll_answer_user.id', new \yii\db\Expression('NULL')]);
//            }
//        }
    }

}


