<?php

namespace mgcode\language\common\models\queries;

use mgcode\helpers\ActiveQueryAliasTrait;

/**
 * This is the ActiveQuery class for [[\mgcode\language\common\models\Language]].
 * @see \mgcode\language\common\models\Language
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

    /**
     * @inheritdoc
     * @return \mgcode\language\common\models\Language[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \mgcode\language\common\models\Language|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
