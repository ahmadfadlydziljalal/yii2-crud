<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */
/** @var $model yii\db\ActiveRecord */

$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form kartik\form\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]
    ]); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {

    if (in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
        continue;
    }

    if (in_array($attribute, $safeAttributes)) {
        echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
    }
} ?>  
	<?='<?php if (!Yii::$app->request->isAjax){ ?>'."\n"?>
	  	<div class="d-flex flex-row flex-wrap">
	        <?= "<?= " ?>Html::a(<?= $generator->generateString('Index') ?>, ['index'] ,['class' => 'btn btn-outline-secondary']) ?>
            <div class="ms-auto">
	            <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Update') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
	    </div>
	<?="<?php } ?>\n"?>

    <?= "<?php " ?>ActiveForm::end(); ?>
    
</div>
