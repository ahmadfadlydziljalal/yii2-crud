<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */

$urlParams = $generator->generateUrlParams();
$labelID = empty($generator->labelID) ? $generator->getNameAttribute() : $generator->labelID;

echo "<?php\n";
?>

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $index int */

use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use yii\widgets\DetailView;
?>

<?php $modelsDetail = StringHelper::basename($generator->modelsClassDetail); ?>
<?php $modelsDetailDetail = StringHelper::basename($generator->modelsClassDetailDetail); ?>
<?php $details = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetail))));  ?>
<?php $detailsDetails = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetailDetail))));  ?>
<div class="d-flex flex-column gap-4">

    <strong><?= '<?= ($index + 1)' ?> . '. ' . StringHelper::basename(get_class($model)) ?></strong>

    <?= "<?php try { 
        echo "?>DetailView::widget([
            'model' => $model,
            'attributes' => [<?php
                array_map(function($el) use($generator){
                    if ($el == 'id' || $el == Inflector::underscore(StringHelper::basename($generator->modelClass)).'_id' || $el == 'created_at' || $el == 'updated_at' || $el == 'created_by' || $el == 'updated_by') {
                        echo "\n                         // '" . $el . "',";
                    }else{
                        echo "\n                        '" . $el . "',";
                    }},$generator->getDetailColumnNames());
                echo "\n";
                 ?>
            ],
        ]);

        echo GridView::widget([
            'dataProvider' => new ActiveDataProvider([
                 'query' => $model->get<?= ucfirst($detailsDetails) . "()" ?>,
                 'sort' => false,
                 'pagination' => false
            ]),
            'layout' => '{items}',
            'columns' =>[
                 [
                    'class' => 'yii\grid\SerialColumn',
                ],<?php array_map(function($el) use ($generator){
            if ($el == 'id'|| $el == Inflector::underscore(StringHelper::basename($generator->modelsClassDetail)).'_id' || $el == 'created_at' || $el == 'updated_at' || $el == 'created_by' || $el == 'updated_by') {
                echo "\n                     // [\n";
                echo "                          // 'class'=>'\yii\grid\DataColumn',\n";
                echo "                          // 'attribute'=>'" . $el . "',\n";
                echo "                     // ],";
            }else{
                echo "\n                     [\n";
                echo "                          'class'=>'\yii\grid\DataColumn',\n";
                echo "                          'attribute'=>'" . $el . "',\n";
                echo "                     ],";
            }},$generator->getDetailDetailColumnNames());
            echo "\n";
            ?>
           ]
       ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    } catch (Throwable $e) {
        echo $e->getMessage();
    }
    ?>



</div>