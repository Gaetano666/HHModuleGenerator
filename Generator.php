<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\modules\generator;

use yii\gii\CodeFile;
use yii\helpers\Html;
use Yii;
use yii\helpers\StringHelper;
use humhub\libs\ZipFile;
use yii\base\ErrorException;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\db\TableSchema;

use yii\helpers\Inflector;
use yii\base\NotSupportedException;


/**
 * This generator will generate the skeleton code needed by a module.
 * code based on module here: https://github.com/tejrajs/generator (many thanks to the author)
 * 
 * @property string $controllerNamespace The controller namespace of the module. This property is read-only.
 * @property boolean $modulePath The directory that contains the module class. This property is read-only.
 *
 * @author Gaetano Tasselli <gaetanotasselli@gmail.com>
 
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    const RELATIONS_NONE = 'none';
    const RELATIONS_ALL = 'all';
    const RELATIONS_ALL_INVERSE = 'all-inverse';
    
    public $moduleClass;
    public $moduleID;
    public $moduleDsc;
    public $author;
    public $outputPath = "@app/modules";
    public $modelClass;
    public $tableName;
    public $tableSchema;
    
    protected $tableNames;
    protected $classNames;
    public $db = 'db';
    public $ns = '';
    public $baseClass = 'yii\db\ActiveRecord';
    public $generateRelations = self::RELATIONS_ALL;
    public $generateLabelsFromComments = false;
    public $useTablePrefix = false;
    public $useSchemaName = true;
    public $generateQuery = false;
    
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'HumHub Module Generator (Yii2 based)';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator helps you to generate the skeleton code needed by a HumHub module.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['moduleID', 'moduleClass'], 'filter', 'filter' => 'trim'],
            [['moduleID', 'moduleClass'], 'required'],
            [['moduleDsc', 'author'], 'safe'],
            [['moduleID'], 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'],
            [['moduleClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['moduleClass'], 'validateModuleClass'],
            [['modelClass'],'required'],
            [['modelClass'], 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'],
            [['modelClass'], 'validateModelClass', 'skipOnEmpty' => false],
            //to do: reenter this once the module is able to generate the Model automatically
            [['tableName'],'required'],
            [['tableName'], 'validateTableName'],
            [['tableName'], 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'],
            [['tableName'], 'filter', 'filter' => 'trim'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'moduleID' => 'Module ID',
            'moduleClass' => 'Module Class',
            'moduleDsc' => 'Module Description',
            'author' => 'Author',
            'tableName' => 'Model table name', //to do: reenter this once the module is able to generate the Model automatically
            'modelClass' => 'Model class'
        ];
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return [
            'moduleID' => 'This refers to the ID of the module, e.g., <code>admin</code>.',
            'moduleClass' => 'This is the fully qualified class name of the module, e.g., <code>humbhub\modules\admin\Module</code>.',
            'moduleDsc' => 'Module Description.',
            'author' => 'Module Author, e.g., Jone Done &lt;jonedone@gmail.com&gt;.',
            'tableName' => 'This is the name of the DB table that the new ActiveRecord class is associated with, e.g. <code>post</code>.
                The table name may consist of the DB schema part if needed, e.g. <code>public.post</code>.
                The table name may end with asterisk to match multiple table names, e.g. <code>tbl_*</code>
                will match tables who name starts with <code>tbl_</code>. In this case, multiple ActiveRecord classes
                will be generated, one for each matching table name; and the class names will be generated from
                the matching characters. For example, table <code>tbl_post</code> will generate <code>Post</code>
                class.',
            'modelClass' => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
                the namespace part as it is specified in "Namespace". You do not need to specify the class name
                if "Table Name" ends with asterisk, in which case multiple ActiveRecord classes will be generated.'
        ];
    }

    /**
     * @inheritdoc
     */
    public function successMessage()
    {
        if (Yii::$app->hasModule($this->moduleID)) {
            $link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($this->moduleID), ['target' => '_blank']);

            return "The module has been generated successfully. You may $link.";
        }

        $output = <<<EOD
<p>The module has been generated successfully.</p>
<p>To access the module, you need to add this to your application configuration:</p>
EOD;
        $code = <<<EOD
<?php
    ......
    'modules' => [
        '{$this->moduleID}' => [
            'class' => '{$this->moduleClass}',
        ],
    ],
    ......
EOD;

        return $output . '<pre>' . highlight_string($code, true) . '</pre>';
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['assets.php','config.php','events.php','module_j.php','module.php', 'controller.php', 'view.php','index.html','StreamAction.php',
            'CreatePost.php', 'WallCreateForm.php', 'WallEntry.php', 'edit.php', 'entry.php', 'form.php', 'show.php', 'view.php'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        $modulePath = $this->getOutputPath().DIRECTORY_SEPARATOR.strtolower($this->moduleID); //$this->getModulePath();
        $files[] = new CodeFile(
        		$modulePath . '/assets/index.html',
        		$this->render("index.html")
        );
        $files[] = new CodeFile(
            $modulePath . '/Assets.php',
            $this->render("assets.php")
        );
        $files[] = new CodeFile(
        		$modulePath . '/config.php',
        		$this->render("config.php")
        );
        $files[] = new CodeFile(
        		$modulePath . '/Events.php',
        		$this->render("events.php")
        );
        $files[] = new CodeFile(
        		$modulePath . '/module.json',
        		$this->render("module_j.php")
        );
        $files[] = new CodeFile(
        		$modulePath . '/Module.php',
        		$this->render("module.php")
        );
        
        $files[] = new CodeFile(
            $modulePath . '/views/'.strtolower($this->moduleID).'/edit.php',
            $this->render("edit.php")
        );
        $files[] = new CodeFile(
            $modulePath . '/views/'.strtolower($this->moduleID).'/show.php',
            $this->render("show.php")
        );
        
        $files[] = new CodeFile(
                $modulePath . '/components/StreamAction.php',
                $this->render("StreamAction.php")
        );
	
        $files[] = new CodeFile(
                $modulePath . '/permissions/CreatePost.php',
                $this->render("CreatePost.php")
        );
        $files[] = new CodeFile(
                $modulePath . '/widgets/WallCreateForm.php',
                $this->render("WallCreateForm.php")
        );
        
        $files[] = new CodeFile(
                $modulePath . '/widgets/WallEntry.php',
                $this->render("WallEntry.php")
        );
        
        $files[] = new CodeFile(
                $modulePath . '/widgets/views/form.php',
                $this->render("form.php")
        );
        
        /*$files[] = $this->generateModel($modulePath);*/
        $relations = $this->generateRelations();
            $db = $this->getDbConnection();
            $ns = $this->moduleNamespace(); //useful?
            
            foreach ($this->getTableNames() as $tableName) {
            // model :
                $modelClassName = $this->generateClassName($tableName);
                $queryClassName = ($this->generateQuery) ? $this->generateQueryClassName($modelClassName) : false;
                $tableSchema = $db->getTableSchema($tableName);
                $params = [
                    'tableName' => $tableName,
                    'className' => $modelClassName,
                    'queryClassName' => $queryClassName,
                    'tableSchema' => $tableSchema,
                    'labels' => $this->generateLabels($tableSchema),
                    'rules' => $this->generateRules($tableSchema),
                    'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [],
                ];
                $files[] = new CodeFile(
                    //Yii::getAlias('@' . str_replace('\\', '/', $this->moduleNamespace())) . '/' . $modelClassName . '.php',
                    $modulePath . '/models/'.$modelClassName.'.php',
                    $this->render('model.php', $params)
                );

            
            }
        
        //those are needed to create the entry.php table data
        $tableSchema = $db->getTableSchema($tableName);    
        $parameters =[
            'labels' => $this->generateLabels($tableSchema),
        ];
        $files[] = new CodeFile(
                $modulePath . '/widgets/views/entry.php',
                $this->render("entry.php", $parameters)
        );    
        
        $files[] = new CodeFile(
            $modulePath . '/controllers/'.ucwords($this->moduleID).'Controller.php',
            $this->render("controller.php", $parameters)
        );
        return $files;
    }
    /**
     * @return boolean the directory that contains the module class
     */
    public function getOutputPath()
    {
    	return Yii::getAlias($this->outputPath);
    }
    /**
     * Validates [[moduleClass]] to make sure it is a fully qualified class name.
     */
    public function validateModuleClass()
    {
        if (strpos($this->moduleClass, '\\') === false || Yii::getAlias('@' . str_replace('\\', '/', $this->moduleClass), false) === false) {
            $this->addError('moduleClass', 'Module class must be properly namespaced.');
        }
        if (empty($this->moduleClass) || substr_compare($this->moduleClass, '\\', -1, 1) === 0) {
            $this->addError('moduleClass', 'Module class name must not be empty. Please enter a fully qualified class name. e.g. "app\\modules\\admin\\Module".');
        }
    }

    /**
     * @return boolean the directory that contains the module class
     */
    public function getModulePath()
    {
        return Yii::getAlias('@' . str_replace('\\', '/', substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\'))));
    }

    /**
     * @return string the controller namespace of the module.
     */
    public function getControllerNamespace()
    {
        return substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\')) . '\controllers';
    }
    
    public function getModel()
    {
        return $this->modelClass;
    }
    
    public function moduleNamespace(){
    	$className = $this->moduleClass;
    	$pos = strrpos($className, '\\');
    	return ltrim(substr($className, 0, $pos), '\\');
    }

    
    public function generateModel($modulePath) {
            //to do
            $files = [];
            $relations = $this->generateRelations();
            $db = $this->getDbConnection();
            $ns = $this->moduleNamespace(); //useful?
            
            foreach ($this->getTableNames() as $tableName) {
            // model :
                $modelClassName = $this->generateClassName($tableName);
                $queryClassName = ($this->generateQuery) ? $this->generateQueryClassName($modelClassName) : false;
                $tableSchema = $db->getTableSchema($tableName);
                $params = [
                    'tableName' => $tableName,
                    'className' => $modelClassName,
                    'queryClassName' => $queryClassName,
                    'tableSchema' => $tableSchema,
                    'labels' => $this->generateLabels($tableSchema),
                    'rules' => $this->generateRules($tableSchema),
                    'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [],
                ];
                $files[] = new CodeFile(
                    //Yii::getAlias('@' . str_replace('\\', '/', $this->moduleNamespace())) . '/' . $modelClassName . '.php',
                    $modulePath . '/model/Test.php',
                    $this->render('model.php', $params)
                );

            
            }

        
            
            
//            $files[] = new CodeFile(
//                $modulePath . '/model/Test.php',
//                $this->render("model.php")
//                );
            
            
            return $files;
    }
    
    /**
     * Validates the [[modelClass]] attribute.
     */
    public function validateModelClass()
    {
        if ($this->isReservedKeyword($this->modelClass)) {
            $this->addError('modelClass', 'Class name cannot be a reserved PHP keyword.');
        }
        if ((empty($this->tableName) || substr_compare($this->tableName, '*', -1, 1)) && $this->modelClass == '') {
            $this->addError('modelClass', 'Model Class cannot be blank if table name does not end with asterisk.');
        }
    }

    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        if (strpos($this->tableName, '*') !== false && substr_compare($this->tableName, '*', -1, 1)) {
            $this->addError('tableName', 'Asterisk is only allowed as the last character.');

            return;
        }
        $tables = $this->getTableNames();
        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist.");
        } else {
            foreach ($tables as $table) {
                $class = $this->generateClassName($table);
                if ($this->isReservedKeyword($class)) {
                    $this->addError('tableName', "Table '$table' will generate a class which is a reserved PHP keyword.");
                    break;
                }
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return [
                'tableName' => function () use ($db) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        } else {
            return [];
        }
    }
    
    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, false);
    }
    
    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    protected function getTableNames()
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }
        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }
        $tableNames = [];
        if (strpos($this->tableName, '*') !== false) {
            if (($pos = strrpos($this->tableName, '.')) !== false) {
                $schema = substr($this->tableName, 0, $pos);
                $pattern = '/^' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '$/';
            } else {
                $schema = '';
                $pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';
            }

            foreach ($db->schema->getTableNames($schema) as $table) {
                if (preg_match($pattern, $table)) {
                    $tableNames[] = $schema === '' ? $table : ($schema . '.' . $table);
                }
            }
        } elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
            $tableNames[] = $this->tableName;
            $this->classNames[$this->tableName] = $this->modelClass;
        }

        return $this->tableNames = $tableNames;
    }
    
    /**
     * Generates a class name from the specified table name.
     * @param string $tableName the table name (which may contain schema prefix)
     * @param boolean $useSchemaName should schema name be included in the class name, if present
     * @return string the generated class name
     */
    protected function generateClassName($tableName, $useSchemaName = null)
    {
        if (isset($this->classNames[$tableName])) {
            return $this->classNames[$tableName];
        }

        $schemaName = '';
        $fullTableName = $tableName;
        if (($pos = strrpos($tableName, '.')) !== false) {
            if (($useSchemaName === null && $this->useSchemaName) || $useSchemaName) {
                $schemaName = substr($tableName, 0, $pos) . '_';
            }
            $tableName = substr($tableName, $pos + 1);
        }

        $db = $this->getDbConnection();
        $patterns = [];
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        if (strpos($this->tableName, '*') !== false) {
            $pattern = $this->tableName;
            if (($pos = strrpos($pattern, '.')) !== false) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
        }
        $className = $tableName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $tableName, $matches)) {
                $className = $matches[1];
                break;
            }
        }

        return $this->classNames[$fullTableName] = Inflector::id2camel($schemaName.$className, '_');
    }
    
    /**
     * @return string[] all db schema names or an array with a single empty string
     * @throws NotSupportedException
     * @since 2.0.5
     */
    protected function getSchemaNames()
    {
        $db = $this->getDbConnection();
        $schema = $db->getSchema();
        if ($schema->hasMethod('getSchemaNames')) { // keep BC to Yii versions < 2.0.4
            try {
                $schemaNames = $schema->getSchemaNames();
            } catch (NotSupportedException $e) {
                // schema names are not supported by schema
            }
        }
        if (!isset($schemaNames)) {
            if (($pos = strpos($this->tableName, '.')) !== false) {
                $schemaNames = [substr($this->tableName, 0, $pos)];
            } else {
                $schemaNames = [''];
            }
        }
        return $schemaNames;
    }
    
    /**
     * @return array the generated relation declarations
     */
    protected function generateRelations()
    {
        if ($this->generateRelations === self::RELATIONS_NONE) {
            return [];
        }

        $db = $this->getDbConnection();

        $relations = [];
        foreach ($this->getSchemaNames() as $schemaName) {
            foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
                $className = $this->generateClassName($table->fullName);
                foreach ($table->foreignKeys as $refs) {
                    $refTable = $refs[0];
                    $refTableSchema = $db->getTableSchema($refTable);
                    if ($refTableSchema === null) {
                        // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                        continue;
                    }
                    unset($refs[0]);
                    $fks = array_keys($refs);
                    $refClassName = $this->generateClassName($refTable);

                    // Add relation for this table
                    $link = $this->generateRelationLink(array_flip($refs));
                    $relationName = $this->generateRelationName($relations, $table, $fks[0], false);
                    $relations[$table->fullName][$relationName] = [
                        "return \$this->hasOne($refClassName::className(), $link);",
                        $refClassName,
                        false,
                    ];

                    // Add relation for the referenced table
                    $hasMany = $this->isHasManyRelation($table, $fks);
                    $link = $this->generateRelationLink($refs);
                    $relationName = $this->generateRelationName($relations, $refTableSchema, $className, $hasMany);
                    $relations[$refTableSchema->fullName][$relationName] = [
                        "return \$this->" . ($hasMany ? 'hasMany' : 'hasOne') . "($className::className(), $link);",
                        $className,
                        $hasMany,
                    ];
                }

                if (($junctionFks = $this->checkJunctionTable($table)) === false) {
                    continue;
                }

                $relations = $this->generateManyManyRelations($table, $junctionFks, $relations);
            }
        }

        if ($this->generateRelations === self::RELATIONS_ALL_INVERSE) {
            return $this->addInverseRelations($relations);
        }

        return $relations;
    }
    
    /**
     * Generates the link parameter to be used in generating the relation declaration.
     * @param array $refs reference constraint
     * @return string the generated link parameter.
     */
    protected function generateRelationLink($refs)
    {
        $pairs = [];
        foreach ($refs as $a => $b) {
            $pairs[] = "'$a' => '$b'";
        }

        return '[' . implode(', ', $pairs) . ']';
    }
    
    /**
     * Checks if the given table is a junction table, that is it has at least one pair of unique foreign keys.
     * @param \yii\db\TableSchema the table being checked
     * @return array|boolean all unique foreign key pairs if the table is a junction table,
     * or false if the table is not a junction table.
     */
    protected function checkJunctionTable($table)
    {
        if (count($table->foreignKeys) < 2) {
            return false;
        }
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch (NotSupportedException $e) {
            // ignore
        }
        $result = [];
        // find all foreign key pairs that have all columns in an unique constraint
        $foreignKeys = array_values($table->foreignKeys);
        for ($i = 0; $i < count($foreignKeys); $i++) {
            $firstColumns = $foreignKeys[$i];
            unset($firstColumns[0]);

            for ($j = $i + 1; $j < count($foreignKeys); $j++) {
                $secondColumns = $foreignKeys[$j];
                unset($secondColumns[0]);

                $fks = array_merge(array_keys($firstColumns), array_keys($secondColumns));
                foreach ($uniqueKeys as $uniqueKey) {
                    if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                        // save the foreign key pair
                        $result[] = [$foreignKeys[$i], $foreignKeys[$j]];
                        break;
                    }
                }
            }
        }
        return empty($result) ? false : $result;
    }
    
    /**
     * Generate a relation name for the specified table and a base name.
     * @param array $relations the relations being generated currently.
     * @param \yii\db\TableSchema $table the table schema
     * @param string $key a base name that the relation name may be generated from
     * @param boolean $multiple whether this is a has-many relation
     * @return string the relation name
     */
    protected function generateRelationName($relations, $table, $key, $multiple)
    {
        if (!empty($key) && substr_compare($key, 'id', -2, 2, true) === 0 && strcasecmp($key, 'id')) {
            $key = rtrim(substr($key, 0, -2), '_');
        }
        if ($multiple) {
            $key = Inflector::pluralize($key);
        }
        $name = $rawName = Inflector::id2camel($key, '_');
        $i = 0;
        while (isset($table->columns[lcfirst($name)])) {
            $name = $rawName . ($i++);
        }
        while (isset($relations[$table->fullName][$name])) {
            $name = $rawName . ($i++);
        }

        return $name;
    }
    
    /**
     * Determines if relation is of has many type
     *
     * @param TableSchema $table
     * @param array $fks
     * @return boolean
     * @since 2.0.5
     */
    protected function isHasManyRelation($table, $fks)
    {
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch (NotSupportedException $e) {
            // ignore
        }
        foreach ($uniqueKeys as $uniqueKey) {
            if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Generates the attribute labels for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels($table)
    {
        $labels = [];
        foreach ($table->columns as $column) {
            if ($this->generateLabelsFromComments && !empty($column->comment)) {
                $labels[$column->name] = $column->comment;
            } elseif (!strcasecmp($column->name, 'id')) {
                $labels[$column->name] = 'ID';
            } else {
                $label = Inflector::camel2words($column->name);
                if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                    $label = substr($label, 0, -3) . ' ID';
                }
                $labels[$column->name] = $label;
            }
        }

        return $labels;
    }
    
    /**
     * Generates validation rules for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated validation rules
     */
    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case 'double': // Schema::TYPE_DOUBLE, which is available since Yii 2.0.3
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        $db = $this->getDbConnection();

        // Unique indexes rules
        try {
            $uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $labels = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
                        $lastLabel = array_pop($labels);
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList'], 'message' => 'The combination of " . implode(', ', $labels) . " and $lastLabel has already been taken.']";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }
    
    /**
     * Generates the table name by considering table prefix.
     * If [[useTablePrefix]] is false, the table name will be returned without change.
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated table name
     */
    public function generateTableName($tableName)
    {
        if (!$this->useTablePrefix) {
            return $tableName;
        }

        $db = $this->getDbConnection();
        if (preg_match("/^{$db->tablePrefix}(.*?)$/", $tableName, $matches)) {
            $tableName = '{{%' . $matches[1] . '}}';
        } elseif (preg_match("/^(.*?){$db->tablePrefix}$/", $tableName, $matches)) {
            $tableName = '{{' . $matches[1] . '%}}';
        }
        return $tableName;
    }
}
