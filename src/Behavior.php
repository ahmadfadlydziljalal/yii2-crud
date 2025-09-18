<?php

namespace dzil\crud;

use Yii;
use yii\base\Behavior as BaseBehavior;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;

/**
 * This class provides implementation details for CRUD behaviour associated with a specific data model.
 * It includes methods for generating scaffolding files such as controllers, search models, and views,
 * as well as for handling search functionality, generating rules, labels, and conditions.
 *
 * @property $owner Generator
 */
class Behavior extends BaseBehavior
{
    /**
     * Get an indefinite article ("a" / "an") for a given word.
     */
    public function getIndefiniteArticle(string $word): string
    {
        if ($word === '') return 'a';

        // Take the first letter
        $firstLetter = strtolower($word[0]);

        // Vocal → "an", this is dumb by me :wink
        if (in_array($firstLetter, ['a', 'e', 'i', 'o', 'u'])) {
            return 'an';
        }

        // Default → "a"
        return 'a';
    }

    /**
     * Return the name of the service class. When you're using clean code, creating a service class is a good idea.
     * @return string
     */
    public function getServiceClassName(): string
    {
        return StringHelper::basename($this->owner->modelClass) . 'Service';
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     * @throws InvalidConfigException
     */
    public function generateSearchRules(): array
    {
        if (($table = $this->owner->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->owner->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
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
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * Generates the attribute labels for the search model.
     * @return array the generated attribute labels (name => label)
     * @throws InvalidConfigException
     */
    public function generateSearchLabels(): array
    {
        /* @var $model Model */
        $model = new $this->owner->modelClass();
        $attributeLabels = $model->attributeLabels();
        $labels = [];
        foreach ($this->owner->getColumnNames() as $name) {
            if (isset($attributeLabels[$name])) {
                $labels[$name] = $attributeLabels[$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels[$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                        $label = substr($label, 0, -3) . ' ID';
                    }
                    $labels[$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * @return array
     * @throws InvalidConfigException
     */
    public function generateSearchConditions(): array
    {
        $columns = [];
        if (($table = $this->owner->getTableSchema()) === false) {
            $class = $this->owner->modelClass;
            /* @var $model Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "'{$column}' => \$this->{$column},";
                    break;
                default:
                    $likeConditions[] = "->andFilterWhere(['like', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * @return string
     */
    public function generateUrlParams(): string
    {
        /* @var $class ActiveRecord */
        $class = $this->owner->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            } else {
                return "'id' => \$model->{$pks[0]}";
            }
        } else {
            $params = [];
            foreach ($pks as $pk) {
                if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                    $params[] = "'$pk' => (string)\$model->$pk";
                } else {
                    $params[] = "'$pk' => \$model->$pk";
                }
            }

            return implode(', ', $params);
        }
    }

    /**
     * Generates parameter tags for phpdoc
     * @return array parameter tags for phpdoc
     * @throws InvalidConfigException
     */
    public function generateActionParamComments(): array
    {
        /* @var $class ActiveRecord */
        $class = $this->owner->modelClass;
        $pks = $class::primaryKey();
        if (($table = $this->owner->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (str_ends_with(strtolower($pk), 'id') ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        } else {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
            }
            return $params;
        }
    }

    /**
     * Generates code for the active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField(string $attribute): string
    {
        $tableSchema = $this->owner->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    /**
     * Checks if a model class is valid
     */
    public function validateModelClass(): void
    {
        /* @var $class ActiveRecord */
        $class = $this->owner->modelClass;
        $pk = $class::primaryKey();
        if (empty($pk)) {
            $this->owner->addError('modelClass', "The table associated with " . $class::class . " must have primary key(s).");
        }
    }

    /**
     * @return string the controller view path
     */
    public function getViewPath(): string
    {
        if (empty($this->owner->viewPath)) {
            return Yii::getAlias('@app/views/' . $this->owner->getControllerID());
        } else {
            return Yii::getAlias($this->owner->viewPath);
        }
    }

    /**
     * @return string the attribute that is used as the name of the model
     */
    public function getNameAttribute(): string
    {
        foreach ($this->owner->getColumnNames() as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /* @var $class ActiveRecord */
        $class = $this->owner->modelClass;
        $pk = $class::primaryKey();

        return $pk[0];
    }

    /**
     * Returns the column names for the current model class.
     * @return array list of column names
     * @throws InvalidConfigException
     */
    public function getColumnNames(): array
    {
        /* @var $class ActiveRecord */
        $class = $this->owner->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            $model = new $class();
            return $model->attributes();
        }
    }

    /**
     * Returns table schema for the current model class or false if it is not an active record
     * @param null $modelClass
     * @return boolean|TableSchema
     * @throws InvalidConfigException
     */
    public function getTableSchema($modelClass = null): bool|TableSchema
    {
        /* @var $class ActiveRecord */
        if (isset($modelClass)) {
            $class = $this->owner->modelsClassDetail;
        } else {
            $class = $this->owner->modelClass;
        }

        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     * Generates code for the active field
     * @param string $attribute
     * @return string
     * @throws InvalidConfigException
     */
    public function generateActiveField(string $attribute): string
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "\$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns[$attribute];

        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } elseif ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        } elseif ($column->type === 'date') {
            return "\$form->field(\$model, '$attribute')->widget(kartik\datecontrol\DateControl::class,[ 'type'=>kartik\datecontrol\DateControl::FORMAT_DATE, ])";
        } elseif ($column->type === 'datetime') {
            return "\$form->field(\$model, '$attribute')->widget(kartik\datecontrol\DateControl::class,[ 'type'=>kartik\datecontrol\DateControl::FORMAT_DATETIME, ])";
        } else {

            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }

            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
                }
                return "\$form->field(\$model, '$attribute')->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)) . ")";
            } elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\$form->field(\$model, '$attribute')->$input()";
            } else {
                return "\$form->field(\$model, '$attribute')->$input(['maxlength' => true])";
            }
        }
    }

    /**
     * Generates code for the active field but with the autofocus attribute
     *
     * @param string $attribute
     * @return string
     * @throws InvalidConfigException
     */
    public function generateActiveFieldAutoFocus(string $attribute): string
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "\$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns[$attribute];

        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } elseif ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea([
                'rows' => 6,
                'autofocus'=> 'autofocus'
            ])";
        } elseif ($column->type === 'date') {
            return "\$form->field(\$model, '$attribute')->widget(\kartik\datecontrol\DateControl::class,[ 
                'type'=>kartik\datecontrol\DateControl::FORMAT_DATE,
                'options' => [
                    'autofocus'=> 'autofocus'
                ] 
            ])";
        } elseif ($column->type === 'datetime') {
            return "\$form->field(\$model, '$attribute')->widget(\kartik\datecontrol\DateControl::class,[ 
                'type'=>kartik\datecontrol\DateControl::FORMAT_DATETIME,
                'options' => [
                    'autofocus'=> 'autofocus'
                ]  
            ])";
        } else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }

            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
                }
                return "\$form->field(\$model, '$attribute')->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)) . ", ['autofocus' => 'autofocus'])";
            } elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\$form->field(\$model, '$attribute')->$input()";
            } else {
                return "\$form->field(\$model, '$attribute')->$input([
                        'maxlength' => true,
                        'autofocus'=> 'autofocus'
                    ])";
            }
        }
    }

    /**
     * Generates column format
     * @param ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat(ColumnSchema $column): string
    {

        if ($column->type === 'date')
            return 'date';

        if ($column->type === 'datetime')
            return 'datetime';

        if ($column->phpType === 'boolean')
            return 'boolean';

        if ($column->type === 'text')
            return 'ntext';

        if (stripos($column->name, 'time') !== false && $column->phpType === 'integer')
            return 'datetime';

        if (stripos($column->name, 'email') !== false)
            return 'email';

        if (stripos($column->name, 'url') !== false)
            return 'url';

        return 'text';

    }

    /**
     * @return array searchable attributes
     * @throws InvalidConfigException
     */
    public function getSearchAttributes(): array
    {
        return $this->getColumnNames();
    }

    /**
     * Generates action parameters
     * @return string
     */
    public function generateActionParams(): string
    {
        /* @var $class ActiveRecord */
        $class = $this->owner->modelClass;
        $pks = $class::primaryKey();
        return count($pks) === 1 ? '$id' : '$' . implode(', $', $pks);
    }

    /**
     * Retrieves the column names for the detailed model class.
     *
     * If the model class is a subclass of yii\db\ActiveRecord, it fetches
     * column names from the table schema. Otherwise, it retrieves
     * attribute names using the model instance.
     *
     * @return array The list of column or attribute names for the model.
     * @throws InvalidConfigException
     */
    public function getDetailColumnNames(): array
    {
        /* @var $class ActiveRecord */
        $class = $this->owner->modelsClassDetail;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            $model = new $class();
            return $model->attributes();
        }
    }

    /**
     * Retrieves the list of column names from the detailed detail models class.
     *
     * If the class is a subclass of ActiveRecord, it fetches the column names from the database schema.
     * Otherwise, it retrieves the attribute names directly from the model instance.
     *
     * @return array List of column or attribute names.
     * @throws InvalidConfigException
     */
    public function getDetailDetailColumnNames(): array
    {
        /* @var $class ActiveRecord */
        $class = $this->owner->modelsClassDetailDetail;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            $model = new $class();
            return $model->attributes();
        }
    }

    /**
     * Retrieves the controller ID based on the controller class name.
     * @return string the ID of the controller derived in camel-case format
     */
    public function getControllerID(): string
    {
        $pos = strrpos($this->owner->controllerClass, '\\');
        $class = substr(substr($this->owner->controllerClass, $pos + 1), 0, -10);
        return Inflector::camel2id($class);
    }

    /**
     * Generates the code for the active field with a detailed structure based on the given parameters.
     *
     * @param mixed $attribute The attribute name or identifier used for generating the active field.
     * @param int $type The type of field structure to generate. Defaults to 1.
     * @param int $level The hierarchical level of the model being processed. Defaults to 1.
     * @return string The generated code for the active field.
     * @throws InvalidConfigException If the table schema or the attribute is not recognised.
     */
    public function generateDetailsActiveField(mixed $attribute, int $type = 1, int $level = 1): string
    {
        $options = $type === 1 ?
            "['options' => ['class' => ''], 'enableLabel' => false,]" :
            "['options' =>['class' => 'mb-3 row']]";

        $indexLevelValue = $level <= 1 ?
            "\"[\$i]$attribute\"" :
            "\"[\$i][\$j]$attribute\"";

        $modelLevelValue = $level <= 1 ?
            "\$modelDetail" :
            "\$modelDetailDetail";

        $tableSchema = $this->getTableSchema($this->owner->modelsClassDetail);

        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field($modelLevelValue, $indexLevelValue, $options)->passwordInput()";
            } else {
                return "\$form->field($modelLevelValue, $indexLevelValue, $options)";
            }
        }
        $column = $tableSchema->columns[$attribute];

        if ($column->phpType === 'boolean') {
            return "\$form->field($modelLevelValue, $indexLevelValue, $options)->checkbox()";
        } elseif ($column->type === 'text') {
            return "\$form->field($modelLevelValue, $indexLevelValue, $options)->textarea(['rows' => 6])";
        } elseif ($column->type === 'date') {
            return "\$form->field($modelLevelValue, $indexLevelValue, $options)->widget(\kartik\datecontrol\DateControl::class,[ 'type'=>\kartik\datecontrol\DateControl::FORMAT_DATE,])";
        } elseif ($column->type === 'datetime') {
            return "\$form->field($modelLevelValue, $indexLevelValue, $options)->widget(\kartik\datecontrol\DateControl::class,[ 'type'=>\kartik\datecontrol\DateControl::FORMAT_DATETIME,])";
        } else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }

            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
                }
                return "\$form->field($modelLevelValue, $indexLevelValue, $options)->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)) . ")";
            } elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\$form->field($modelLevelValue, $indexLevelValue, $options)->$input()";
            } else {
                return "\$form->field($modelLevelValue, $indexLevelValue, $options)->$input(['maxlength' => true, 'type' => '$column->type'])";
            }
        }
    }


}