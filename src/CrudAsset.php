<?php

namespace dzil\yii2_crud;

use yii\web\AssetBundle;

/**
 * @author John Martin <john.itvn@gmail.com>
 * @author Dzil <ahmadfadlydziljalal@gmail.com>
 * @since 1.0
 */
class CrudAsset extends AssetBundle
{
    public $sourcePath = '@dzilcrud/generators/assets';

    /*public $publishOptions = [
        'forceCopy' => true,
    ];*/

    public $css = [
        'ajaxcrud.css'
    ];

    public $depends = [
        'kartik\grid\GridViewAsset',
        'yii\bootstrap5\BootstrapIconAsset'
    ];

    public function init(): void
    {
        // In dev mode use non-minified javascripts
        $this->js = YII_DEBUG ? ['ModalRemote.js', 'ajaxcrud.js',] : ['ModalRemote.min.js', 'ajaxcrud.min.js',];
        parent::init();
    }
}