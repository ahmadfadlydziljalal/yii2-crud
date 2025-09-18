<?php


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
$detailDetail = Inflector::titleize(StringHelper::basename($generator->modelsClassDetailDetail));
echo "<?php\n";
?>
use yii\helpers\Html;
use kartik\form\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $i int|string */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $modelsDetail <?= ltrim($generator->modelsClassDetail, '\\') ?> */
/* @var $modelsDetailDetail <?= ltrim($generator->modelsClassDetailDetail, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<?= "<?php " ?>
DynamicFormWidget::begin([
'widgetContainer' => "dynamicform_inner_{$i}", // <-- unik per parent
'widgetBody' => ".container-rooms-{$i}", // <-- unik per parent
'widgetItem' => '.room-item',
'limit' => 99,
'min' => 1,
'insertButton' => '.add-room',
'deleteButton' => '.remove-room',
'model' => $modelsDetailDetail[0],
'formId' => 'dynamic-form',
'formFields' => [<?php foreach ($generator->getDetailDetailColumnNames() as $columnName) {
   echo " '" . $columnName . "', ";
} ?>],
]); ?>

<?php $detailDetail = ucwords(Inflector::titleize(Inflector::pluralize(StringHelper::basename($generator->modelsClassDetailDetail)))) ?>
<table class="table table-bordered">
    <thead>
     <tr>
        <th scope="col">#</th>
        <?php foreach ($generator->getDetailDetailColumnNames() as $columnName) {
            if ($columnName === 'id') continue;
            if ($columnName === Inflector::underscore(StringHelper::basename($generator->modelsClassDetail)) . '_id') continue;
            ?>
            <th scope="col"><?= Inflector::humanize($columnName) ?></th>
        <?php } ?>
        <th scope="col" class="text-center" style="width: 2px"><?= "<?php echo " ?>Html::button('<span
                    class="bi bi-plus-circle"></span>' , [ 'class' => 'add-room btn btn-link text-success', ]); ?>
        </th>
    </tr>
    </thead>
    <tbody class="container-rooms-<?= "<?= \$i ?>" ?>">
    <?= "<?php " ?>foreach ($modelsDetailDetail as $j => $modelDetailDetail): ?>
    <tr class="room-item">
        <td class="align-middle" style="width: 2px;">

            <?= "<?php " ?>if (!$modelDetailDetail->isNewRecord) {
            echo Html::activeHiddenInput($modelDetailDetail, "[$i][$j]id");
            } ?>

            <i class="bi bi-dash"></i>
        </td>

        <?php foreach ($generator->getDetailDetailColumnNames() as $columnName) {
            if ($columnName === 'id') continue;
            if ($columnName === Inflector::underscore(StringHelper::basename($generator->modelsClassDetail)) . '_id') continue;
            ?>
            <td>
                <?php echo "<?= " . $generator->generateDetailsActiveField($columnName, type: 1, level: 2) . " ?>\n"; ?>
            </td>
        <?php } ?>

        <td class="text-center" style="width: 90px;">
            <button type="button" class="remove-room btn btn-link text-danger">
                <i class="bi bi-trash"> </i>
            </button>
        </td>
    </tr>
    <?= "<?php " ?>endforeach; ?>
    </tbody>

</table>

<?= "<?php " ?> DynamicFormWidget::end(); ?>