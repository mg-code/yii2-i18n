<?php

namespace mgcode\i18n\common\models;

use yii\helpers\Url;

/**
 * This is the model class for table "language".
 */
class Language extends AbstractLanguage
{
    /**
     * @return string
     */
    public function getUrl()
    {
        $route = array_merge(
            ['/'.\Yii::$app->controller->route],
            \Yii::$app->request->get(),
            ['lang' => $this->iso_code]
        );
        return Url::to($route);
    }
}
