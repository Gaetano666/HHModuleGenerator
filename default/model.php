<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?= $generator->moduleNamespace() ?>\models;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentActiveRecord;

/**
 * This is the model class for table "<?= $generator->tableName ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends ContentActiveRecord implements \humhub\modules\search\interfaces\Searchable
{
    
    public $autoAddToWall = true;
    public $wallEntryClass = '<?= $generator->moduleNamespace()?>\widgets\WallEntry';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        //to do: modify the content of the return at your pleasure; this is the "Label" that will appear into each entry into the wall and into the activity panel
        return "<?=ucwords($generator->moduleID)?>";
    }
    
    /**
     * @inheritdoc
     */
    public function getContentDescription()
    {
        //to do: modify the content of the return at your pleasure; this is the "Label" that will appear into each entry into the activity panel, e.g. $this->description;
        return "my sample text"; 
    }
    
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
    */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
    
    /**
     * @inheritdoc
    */
    public function getSearchAttributes()
    {
        //to do: you need to remove the unnecessary fields
        return array(
        <?php foreach ($labels as $name => $label): ?>          
          '<?=$name?>' => $this-><?=$name?>,
        <?php endforeach; ?>
        );
    }    
}
