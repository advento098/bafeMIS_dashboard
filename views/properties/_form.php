<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Properties $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="properties-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'timestamp')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'property_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'property_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'particular')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'date_acquired')->textInput() ?>

    <?= $form->field($model, 'unit_value')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'possessor')->textInput() ?>

    <?= $form->field($model, 'mr_date')->textInput() ?>

    <?= $form->field($model, 'current_holder')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'office')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'operability')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
