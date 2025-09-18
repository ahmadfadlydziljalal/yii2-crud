<?php

/** @var $this yii\web\View */
/** @var $form yii\widgets\ActiveForm */
/** @var $generator \dzil\yii2_crud\generators\Generator */

echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'searchModelClass');
echo $form->field($generator, 'controllerClass');
echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
echo "<hr />";
echo $form->field($generator, 'modelsClassDetail');
echo $form->field($generator, 'modelsClassDetailDetail');
echo "<hr />";
echo $form->field($generator, 'baseControllerClass');
