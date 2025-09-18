<?php

use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */


/* @var $model ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>
use yii\helpers\Html;
use kartik\form\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $modelsDetail <?= ltrim($generator->modelsClassDetail, '\\') ?> */
/* @var $modelsDetailDetail <?= ltrim($generator->modelsClassDetailDetail, '\\') ?> */
/* @var $form kartik\form\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'id' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>Form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <div class="d-flex flex-column mt-0" style="gap: 1rem">
        <div class="form-master">
            <div class="row">
                <div class="col-12 col-lg-7">
                    <?php foreach ($generator->getColumnNames() as $key => $attribute) {

                        if (in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
                            continue;
                        }

                        if (in_array($attribute, $safeAttributes)) {
                            if ($key == 1) {
                                echo "<?= " . $generator->generateActiveFieldAutoFocus($attribute) . " ?>\n";
                            } else {
                                echo "                    <?= " . $generator->generateActiveField($attribute) . "; ?>\n";
                            }
                        }
                    } ?>
                </div>
            </div>
        </div>

        <div class="form-detail">

            <?= "<?php " ?>DynamicFormWidget::begin([
                'widgetContainer' => 'dynamicform_wrapper',
                'widgetBody' => '.container-items', // required: CSS class selector
                'widgetItem' => '.item', // required: CSS class
                'limit' => 100, // the maximum times, an element can be cloned (default 999)
                'min' => 1, // 0 or 1 (default 1)
                'insertButton' => '.add-item', // CSS class
                'deleteButton' => '.remove-item', // CSS class
                'model' => $modelsDetail[0],
                'formId' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>Form',
                'formFields' => [<?php foreach ($generator->getDetailColumnNames() as $columnName) {
                    echo " '" . $columnName . "', ";
                } ?>],
            ]); ?>
            <?php $detail = ucwords(Inflector::titleize(Inflector::pluralize(StringHelper::basename($generator->modelsClassDetail)))) ?>

            <div class="container-items">
                <?= "<?php " ?>foreach ($modelsDetail as $i => $modelDetail): ?>
                <div class="card item mb-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <?= "<?php " ?>if (!$modelDetail->isNewRecord) { echo Html::activeHiddenInput($modelDetail, "[$i]id"); } ?>
                            <strong><i class="bi bi-arrow-right-short"></i> <?= $detail ?></strong>
                            <button type="button" class="remove-item btn btn-link text-danger">
                                <i class="bi bi-x-circle"> </i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
<?php foreach ($generator->getDetailColumnNames() as $columnName) { ?>
    <?php if ($columnName === 'id') continue; ?>
    <?php if ($columnName === Inflector::underscore(StringHelper::basename($generator->modelClass)) . '_id') continue; ?>
    <?php  echo "<?= " . $generator->generateDetailsActiveField($columnName, 2) . " ?>\n"; ?>
<?php } ?>

                        <?= "<?= " ?>$this->render('_form-detail-detail', [
                            'form' => $form,
                            'i' => $i,
                            'modelsDetailDetail' => $modelsDetailDetail[$i],
                        ]) ?>
                    </div>
                </div>
                <?= "<?php " ?>endforeach; ?>
            </div>

            <div class="text-end">
                <?= "<?php echo " ?>Html::button('<span class="bi bi-plus-circle"></span> Tambah <?= ucwords($detail) ?>', [ 'class' => 'add-item btn btn-success', ]); ?>
            </div>

            <?= "<?php DynamicFormWidget::end(); ?> \n" ?>

            <div class="d-flex justify-content-between mt-3">
                <?= "<?= " ?>Html::a( <?= $generator->generateString(' Tutup') ?>, ['index'], ['class' => 'btn btn-secondary']) ?>
                <?= "<?= " ?>Html::submitButton(<?= $generator->generateString(' Simpan') ?>, ['class' =>'btn btn-primary' ]) ?>
            </div>
        </div>

        <?= "<?php ActiveForm::end();  ?> " ?>

    </div>