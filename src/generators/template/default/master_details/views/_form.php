<?php

use yii\base\InvalidConfigException;
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
use kartik\base\BootstrapInterface;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $modelsDetail <?= ltrim($generator->modelsClassDetail, '\\') ?> */
/* @var $modelsDetailDetail <?= ltrim($generator->modelsClassDetailDetail, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'id' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>Form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => BootstrapInterface::SIZE_SMALL]
    ]); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
  if (in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
     continue;
  }
  if (in_array($attribute, $safeAttributes)) {
     echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n";
  }
} ?>

   <?= "<?php " ?>DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper',
        'widgetBody' => '.container-items',
        'widgetItem' => '.item',
        'limit' => 100,
        'min' => 1,
        'insertButton' => '.add-item',
        'deleteButton' => '.remove-item',
        'model' => $modelsDetail[0],
        'formId' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>Form',
        'formFields' => [<?php foreach ($generator->getDetailColumnNames() as $columnName) {
          echo " '" . $columnName . "', ";
       } ?>],
    ]); ?>

    <hr class="text-muted"/>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th scope="col" style="width: 2px">#</th>
            <?php foreach ($generator->getDetailColumnNames() as $columnName) {
                if ($columnName === 'id') continue;
                if ($columnName === Inflector::underscore(StringHelper::basename($generator->modelClass)) . '_id') continue;
                ?><th scope="col"><?= Inflector::humanize($columnName) ?></th>
            <?php } ?><th scope="col" style="width: 2px"></th>
        </tr>
        </thead>

        <tbody class="container-items">
            <?= "<?php " ?>foreach ($modelsDetail as $i => $modelDetail): ?>
            <tr class="item">
                <td class="align-middle" style="width: 2px;">
                    <?= "<?php " ?>if (!$modelDetail->isNewRecord) {
                        echo Html::activeHiddenInput($modelDetail, "[{$i}]id");
                    } ?>
                    <i class="bi bi-arrow-right"></i>
                </td>

                <?php foreach ($generator->getDetailColumnNames() as $columnName) {
                    if ($columnName === 'id') continue;
                    if ($columnName === Inflector::underscore(StringHelper::basename($generator->modelClass)) . '_id') continue;
                    ?><td><?php try {
                            echo "<?= " . $generator->generateDetailsActiveField($columnName, type: 1) . " ?>";
                        } catch (InvalidConfigException $e) {
                            echo $e->getMessage();
                        } ?></td>
                <?php } ?>

                <td class="text-center" style="width: 2px;">
                    <button type="button" class="remove-item btn btn-outline-danger btn-xs">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            <?= "<?php " ?>endforeach; ?>
        </tbody>

        <tfoot>
        <tr>
            <td></td>
            <td class="text-end" colspan="<?= count($generator->getDetailColumnNames()) - 2 ?>">
                <?= "<?php echo " ?>Html::button('<i class="bi bi-plus-circle"></i> Add' , [
                    'class' => 'add-item btn btn-success',
                ]); ?>
            </td>
            <td></td>
        </tr>
        </tfoot>
    </table>

    <?= "<?php DynamicFormWidget::end(); ?> \n" ?>

	<div class="d-flex justify-content-between">
		<?= "<?= " ?>Html::a(<?= $generator->generateString('Tutup') ?>, ['index'], ['class' => 'btn btn-secondary']) ?>
		<?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Simpan') ?>, ['class' =>'btn btn-success'])?>
	</div>
    <?= "<?php ActiveForm::end();  ?> " ?>

</div>
