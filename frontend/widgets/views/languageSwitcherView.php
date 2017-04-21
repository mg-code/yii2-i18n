<?php
/** @var $this \yii\web\View */
/** @var $languages \mgcode\i18n\common\models\Language[] */
?>
<div id="lang-nav" class="dropdown">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= strtoupper(Yii::$app->language); ?> <span class="caret"></span></a>
    <ul class="dropdown-menu">
        <?php foreach($languages as $language): ?>
            <li><a href="<?= $language->getUrl(); ?>" data-language="<?= $language->iso_code; ?>"><?= strtoupper($language->iso_code); ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php
if(Yii::$app->languageManager->useUuidParameter()) {
    \mgcode\i18n\frontend\assets\LanguageSwitcherAsset::register($this)->initPlugin($this, '#lang-nav');
}