<?php
/** @var $this \yii\web\View */
/** @var $languages \mgcode\i18n\common\models\Language[] */
?>

<div id="lang-nav" class="dropdown">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= strtoupper(Yii::$app->language); ?> <span class="caret"></span></a>
    <ul class="dropdown-menu">
        <?php foreach($languages as $language): ?>
            <li><a href="<?= $language->getUrl(); ?>"><?= strtoupper($language->iso_code); ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>