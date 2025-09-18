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

echo "<?php\n";
?>

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form kartik\form\ActiveForm */

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\base\BootstrapInterface;
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'id' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>Form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => BootstrapInterface::SIZE_SMALL]
    ]); ?>

    <div class="row">
        <div class="col-12 col-lg-8">

        <?php foreach ($generator->getColumnNames() as $key => $attribute) {
            if (in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
                continue;
            }
            if (in_array($attribute, $safeAttributes)) {
                if ($key == 1) {
                    echo "    <?= " . $generator->generateActiveFieldAutoFocus($attribute) . "?>\n";
                } else {
                    echo "           <?= " . $generator->generateActiveField($attribute) . " ?>\n";
                }
            }
        } ?>

            <div class="d-flex mt-3 justify-content-between">
                <?= "<?= " ?>Html::a(<?= $generator->generateString(' Close') ?>, ['index'], [
                    'class' => 'btn btn-secondary',
                    'type' => 'button'
                ]) ?>
                <?= "<?= " ?>Html::submitButton(<?= $generator->generateString(' Simpan') ?>, ['class' =>'btn btn-success' ]) ?>
            </div>

        </div>
    </div>
    <?= "<?php " ?>ActiveForm::end(); ?>
</div>