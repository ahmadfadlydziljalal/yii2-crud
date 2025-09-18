<?php

namespace dzil\crud;

use yii\base\Widget;
use yii\helpers\Html;

class BulkButtonWidget extends Widget
{
    public ?string $buttons;

    public function run()
    {
        return Html::tag('div', '&nbsp;' . $this->buttons, ['class' => 'float-left']);
    }
}