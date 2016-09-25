<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode("Page Not Found(#404)"); ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($error)) ?>
    </div>
</div>
