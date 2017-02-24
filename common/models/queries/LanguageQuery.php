<?php

namespace mgcode\i18n\common\models\queries;

use mgcode\helpers\ActiveQueryAliasTrait;

/**
 * This is the ActiveQuery class for [[\mgcode\i18n\common\models\Language]].
 * @see \mgcode\i18n\common\models\Language
 */
class LanguageQuery extends \yii\db\ActiveQuery
{
    use ActiveQueryAliasTrait;

    /**
     * Sorts by defined order
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function sort()
    {
        $alias = $this->getTableAlias();
        $this->orderBy([
            $alias.'.sort' => SORT_ASC,
        ]);
        return $this;
    }

    public function active()
    {
        $this->andWhere(['is_active' => 1]);
        return $this;
    }

    /**
     * @inheritdoc
     * @return \mgcode\i18n\common\models\Language[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \mgcode\i18n\common\models\Language|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
