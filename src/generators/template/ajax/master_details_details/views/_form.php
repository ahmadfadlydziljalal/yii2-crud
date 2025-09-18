<?php


use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\crud\generators\Generator */

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
        'id' => 'dynamic-form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]
    ]); ?>

   <?php foreach ($generator->getColumnNames() as $attribute) {
      if (in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) continue;
      if (in_array($attribute, $safeAttributes)) echo "<?= " . $generator->generateActiveField($attribute) . "; ?>\n\t";
   } ?>

       <?= "<?php " ?>DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_wrapper',
            'widgetBody' => '.container-items', // required: css class selector
            'widgetItem' => '.item', // required: css class
            'limit' => 100, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $modelsDetail[0],
            'formId' => 'dynamic-form',
            'formFields' => [<?php foreach ($generator->getDetailColumnNames() as $columnName) {
              echo " '" . $columnName . "', ";
           } ?>],
        ]); ?>



        <table class="table table-bordered">

                <thead class="thead-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col"><?= $detail = Inflector::titleize(StringHelper::basename($generator->modelsClassDetail)) ?></th>
                    <th scope="col" style="width: 2px"></th>
                </tr>
                </thead>

                <tbody class="container-items">
                <?= "<?php " ?>foreach ($modelsDetail as $i => $modelDetail): ?>
                    <tr class="item">
                        <td style="width: 2px;">

                            <?= "<?php " ?>if (!$modelDetail->isNewRecord) {
                            echo Html::activeHiddenInput($modelDetail, "[{$i}]id");
                            } ?>

                            <i class="bi bi-arrow-right"></i>
                        </td>

                        <td class="p-4">
                            <?php foreach ($generator->getDetailColumnNames() as $columnName) {
                                if ($columnName === 'id') continue;
                                if ($columnName === Inflector::underscore(StringHelper::basename($generator->modelClass)) . '_id') continue; ?>
                                     <?php
                                        try {
                                                echo "<?= " . $generator->generateDetailsActiveField($columnName, 2) . " ?>\n";
                                        } catch (InvalidConfigException $e) {
                                                echo $e->getMessage();
                                        }
                                    ?>
                            <?php } ?>

                            <?= "<?php echo " ?>$this->render('_form-detail-detail', [
                                'form' => $form,
                                'i' => $i,
                                'modelsDetailDetail' => $modelsDetailDetail[$i],
                            ]) ?>
                        </td>

                        <td>
                            <button type="button" class="remove-item btn btn-outline-danger">
                                <span class="bi bi-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?= "<?php " ?>endforeach; ?>
                </tbody>

                <tfoot>
                <tr>
                    <td></td>
                    <td>
                        <?= "<?php echo " ?>Html::button('<i class="bi bi-plus-circle"></i> Add <?= lcfirst($detail) ?>', [
                        'class' => 'add-item btn btn-success float-right',
                        ]); ?>
                    </td>
                    <td></td>
                </tr>
                </tfoot>
            </table>


       <?= "<?php DynamicFormWidget::end(); ?> \n" ?>
   <?= "<?php ActiveForm::end();  ?> " ?>

</div>