<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;


/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>
use yii\bootstrap5\Tabs;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
?>
<?php $modelsDetail = StringHelper::basename($generator->modelsClassDetail); ?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

   <?php $timestamp = ['created_at', 'updated_at',] ?>
   <?php $blameable = ['created_by', 'updated_by',] ?>
   <?php $details = ucwords(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetail)))); ?>
   <?= "<?php try { echo " ?>Tabs::widget([
   'encodeLabels' => false,
   'options' => [
   'class' => 'nav nav-tabs'
   ],
   'tabContentOptions' => [
   'style' => [
   'padding-top' => '12px'
   ]
   ],
   'items' => [
   [
   'active' => true,
   'label' => ' Master',
   'content' =>
   DetailView::widget([
   'model' => $model,
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

         if ($column->name == 'id') {
            continue;
         }
         $format = $generator->generateColumnFormat($column);

         if (in_array($column->name, $timestamp)) {
            echo "           [
                                                    'attribute' => '" . $column->name . "',\n" .
               "                    'format' => 'datetime'," .
               "            \n           ],\n";
            continue;
         }

         if (in_array($column->name, $blameable)) {
            echo "           [
                                                    'attribute' => '" . $column->name . "',\n" .
               "                    'value' => function(\$model){ return \app\models\User::findOne(\$model->$column->name)->username ?? null; }" .
               "            \n           ],\n";
            continue;
         }

         echo "'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
      }
   } ?>
   ],
   ]) ,

   ],
   [
   'label' => 'Details',
   'content' =>
        \kartik\grid\GridView::widget([
            'dataProvider' => new \yii\data\ActiveDataProvider([
                'query' => $model->get<?= $details ?>(),
                'pagination' => false
            ])
        ]) ,
   ],
   ],
   ]);
   } catch (Exception $e) {
   echo $e->getMessage();
   }
   ?>
</div>