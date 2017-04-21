<?php

namespace mgcode\i18n\common\models;

use yii\helpers\Url;

/**
 * This is the model class for table "language".
 */
class Language extends AbstractLanguage
{
    /**
     * @param array $extraParameters
     * @return string
     */
    public function getUrl($extraParameters = [])
    {
        $route = array_merge(
            ['/'.\Yii::$app->controller->route],
            \Yii::$app->request->get(),
            ['lang' => $this->iso_code],
            $extraParameters
        );
        return Url::to($route);
    }
}
