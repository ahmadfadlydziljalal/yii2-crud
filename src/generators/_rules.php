<?php

use dzil\crud\generators\Generator;
use yii\db\BaseActiveRecord;
use yii\web\Controller;

return [
    [['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
    [['modelClass', 'controllerClass', 'baseControllerClass'], 'required'],
    [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
    [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
    [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::class]],
    [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::class]],
    [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
    [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
    [['controllerClass', 'searchModelClass'], 'validateNewClass'],

    /** @see \dzil\crud\generators\Generator::validateModelClass()*/

    [['modelClass'], 'validateModelClass'],
    [['enableI18N'], 'boolean'],
    [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],

    [['modelsClassDetail'], 'required', 'when' => function ($model) {
        return in_array($model->template, [Generator::AJAX_MASTER_DETAIL_TEMPLATE, Generator::MASTER_DETAIL_TEMPLATE]);
    }, 'whenClient' => "function (attribute, value) {
        var template = $('#generator-template').val();
        return template === 'master-details' || template === 'ajax-master-details';
    }", 'message' => 'Required if using 1st level master-details template'],

    [['modelsClassDetailDetail'], 'required', 'when' => function ($model) {
        return in_array($model->template, [Generator::AJAX_MASTER_DETAIL_DETAIL_TEMPLATE, Generator::MASTER_DETAIL_DETAIL_TEMPLATE]);
    }, 'whenClient' => "function (attribute, value) {
        var template = $('#generator-template').val();
        return template === 'master-details-details' || template === 'ajax-master-details-details';
    }", 'message' => 'Required if using 2nd level master-details template'],

    [['viewPath', 'labelID'], 'safe'],
];