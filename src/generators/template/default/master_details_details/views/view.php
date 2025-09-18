<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;


/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */

$urlParams = $generator->generateUrlParams();
$labelID = empty($generator->labelID) ? $generator->getNameAttribute() : $generator->labelID;

echo "<?php\n";
?>
use yii\widgets\DetailView;
use yii\helpers\Html;
use mdm\admin\components\Helper;
use yii\data\ActiveDataProvider;
use yii\widgets\ListView;
use yii\bootstrap5\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $labelID ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $modelsDetail = StringHelper::basename($generator->modelsClassDetail); ?>
<?php $modelsDetailDetail = StringHelper::basename($generator->modelsClassDetailDetail); ?>
<div class="<?= $model =  Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view  d-flex flex-column gap-3">

    <div class="d-flex justify-content-between flex-wrap gap-4 gap-md-2">
        <h1 class="my-0"><?= "<?= " ?>Html::encode($this->title) ?></h1>
        <div class="d-flex flex-row flex-wrap align-items-center" style="gap: .5rem">

            <?= "<?= " ?>Html::a(<?= $generator->generateString('Back') ?>, Yii::$app->request->referrer, ['class' => 'btn btn-outline-secondary']) ?>
            <?= "<?= " ?>Html::a(<?= $generator->generateString('Index') ?>, ['index'], ['class' => 'btn btn-outline-primary']) ?>
            <?= "<?= " ?>Html::a(<?= $generator->generateString('Create More') ?>, ['create'], ['class' => 'btn btn-success']) ?>
            <?= "<?php " ?>

                $items = [
                    [
                        'label' => 'Update',
                        'url' => ['update', 'id' => $model->id],
                        'linkOptions' => [
                            'data-pjax' => 0
                        ]
                    ],
                ];

                if(Helper::checkRoute('delete')) :
                    $items = array_merge($items,[
                        '<li><hr class="dropdown-divider"></li>',
                        [
                            'label' => <?= $generator->generateString('Delete') ?>,
                            'url' => ['delete', <?= $urlParams ?>],
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data' => [
                                    'confirm' => 'Are you sure you want to delete this item?',
                                    'method' => 'post',
                                ],
                            ]
                        ]
                    ]);
                endif;

                echo ButtonDropdown::widget([
                    'label' => '<i class="bi bi-three-dots-vertical"></i>',
                    'dropdown' => [
                        'items' => $items,
                        'options' => [
                            'class' => 'dropdown-menu dropdown-menu-end'
                        ]
                    ],
                    'buttonOptions' => [
                        'class' => 'btn btn-outline-secondary dropdown-toggle text-body-emphasis',
                        'type' => 'button',
                        'data-bs-toggle' => 'dropdown',
                        'aria-expanded' => false
                    ],
                    'encodeLabel' => false,
                ]);
            ?>
        </div>
    </div>

    <?php $timestamp = ['created_at', 'updated_at',] ?>
    <?php $blameable = ['created_by', 'updated_by',] ?>
    <?php $details = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetail))));  ?>
    <?php $detailsDetails = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetailDetail))));  ?>

    <?= "<?php try { 
            echo "?>DetailView::widget([
                'model' => $model,
                'options' => [
                    'class' => 'table table-bordered table-detail-view'
                ],
                'attributes' => [
                    <?php
                    if (($tableSchema = $generator->getTableSchema()) === false) {
                        foreach ($generator->getColumnNames() as $name) {

                            if ($name == 'id') {
                                continue;
                            }

                            echo "            '" . $name . "',\n";
                        }
                    } else {
                        foreach ($generator->getTableSchema()->columns as $column) {

                            if( $column->name == 'id'){
                                continue;
                            }
                            $format = $generator->generateColumnFormat($column);

                            if(in_array($column->name, $timestamp)){
                                echo "           [
                                                                'attribute' => '" . $column->name . "',\n" .
                                    "                    'format' => 'datetime'," .
                                    "            \n           ],\n";
                                continue;
                            }

                            if(in_array($column->name, $blameable)){
                                echo "           [
                                                                'attribute' => '" . $column->name . "',\n" .
                                    "                    'value' => function(\$model){ return app\models\User::findOne(\$model->$column->name)->username; }" .
                                    "            \n           ],\n";
                                continue;
                            }

                            echo "  '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n                 ";
                        }
                    } ?>],
            ]);

            echo ListView::widget([
                'dataProvider' => new ActiveDataProvider([
                    'query' => $model->get<?= ucfirst($details) . "()\n" ?>
                ]),
                'itemView' => function ($model, $key, $index, $widget){
                    return $this->render('_view_detail', [
                        'model' => $model,
                        'index' => $index
                    ]);
                },
                'layout' => '{items}'
            ]);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    ?>

</div>