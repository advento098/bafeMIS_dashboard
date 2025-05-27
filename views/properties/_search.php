<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PropertiesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="properties-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'timestamp') ?>

    <?= $form->field($model, 'doc_no') ?>

    <?= $form->field($model, 'property_type') ?>

    <?= $form->field($model, 'property_no') ?>

    <?php // echo $form->field($model, 'particular') ?>

    <?php // echo $form->field($model, 'date_acquired') ?>

    <?php // echo $form->field($model, 'unit_value') ?>

    <?php // echo $form->field($model, 'possessor') ?>

    <?php // echo $form->field($model, 'mr_date') ?>

    <?php // echo $form->field($model, 'current_holder') ?>

    <?php // echo $form->field($model, 'office') ?>

    <?php // echo $form->field($model, 'operability') ?>

    <?php // echo $form->field($model, 'remarks') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
