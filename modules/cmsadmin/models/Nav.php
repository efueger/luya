<?php

namespace cmsadmin\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * @todo what happens when resort items if an items is deleted?
 *
 * @author nadar
 */
class Nav extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'cms_nav';
    }

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'eventBeforeInsert']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'reindex']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'reindex']);
        $this->on(self::EVENT_AFTER_DELETE, [$this, 'reindex']);
    }

    public function rules()
    {
        return [
            [['cat_id', 'parent_nav_id'], 'required'],
            [['is_hidden', 'sort_index', 'is_deleted'], 'safe'],
        ];
    }

    public function getNavItems()
    {
        return $this->hasMany(\cmsadmin\models\NavItem::className(), ['nav_id' => 'id']);
    }

    /**
     * find the latest sort index cms_nav item for the current cat_id and parent_nav_id and set internal index count plus one.
     */
    public function eventBeforeInsert()
    {
        $item = self::find()->where(['cat_id' => $this->cat_id, 'parent_nav_id' => $this->parent_nav_id])->orderBy('sort_index DESC')->limit(1)->asArray()->one();
        if (!$item) {
            $this->sort_index = 1;
        } else {
            $this->sort_index = $item['sort_index'] + 1;
        }
    }

    public function reindex()
    {
        $i = 1;
        foreach(self::find()->where(['cat_id' => $this->cat_id, 'parent_nav_id' => $this->parent_nav_id])->orderBy('sort_index ASC')->asArray()->all() as $model) {
            Yii::$app->db->createCommand()->update(self::tableName(), ['sort_index' => $i], 'id=:id', ['id' => $model['id']])->execute();
            $i++;
        }
    }
    
    public static function moveToBefore($moveNavId, $toBeforeNavId)
    {
        $move = self::findOne($moveNavId);
        $to = self::findOne($toBeforeNavId);
        
        $to->moveUpstairs();
        
        $move->cat_id = $to->cat_id;
        $move->parent_nav_id = $to->parent_nav_id;
        $move->sort_index = $to->sort_index;
        $move->update();
        
        return true;
    }
    
    public static function moveToAfter($moveNavId, $toAfterNavId)
    {
        $move = self::findOne($moveNavId);
        $to = self::findOne($toAfterNavId);
    
        $to->moveDownstairs();
    
        $move->cat_id = $to->cat_id;
        $move->parent_nav_id = $to->parent_nav_id;
        $move->sort_index = $to->sort_index;
        $move->update();
    
        return true;
    }
    
    public static function moveToChild($moveNavId, $droppedOnItemId)
    {
        $move = self::findOne($moveNavId);
        $on = self::findOne($droppedOnItemId);
    
        $move->cat_id = $on->cat_id;
        $move->parent_nav_id = $on->id;
        $move->update();
    
        return true;
    }
    
    public function moveUpstairs()
    {
        $startIndex = (int) $this->sort_index;
        foreach(self::find()->where("sort_index >= :index", ['index' => $startIndex])->andWhere(['cat_id' => $this->cat_id, 'parent_nav_id' => $this->parent_nav_id])->orderBy('sort_index ASC')->asArray()->all() as $item) {
            $startIndex++;
            $up = Yii::$app->db->createCommand()->update(self::tableName(), ['sort_index' => $startIndex], 'id=:id', ['id' => $item['id']])->execute();
        }
        
    }
    
    public function moveDownstairs()
    {
        $startIndex = (int) $this->sort_index;
        foreach(self::find()->where("sort_index >= :index", ['index' => $startIndex])->andWhere(['cat_id' => $this->cat_id, 'parent_nav_id' => $this->parent_nav_id])->orderBy('sort_index ASC')->asArray()->all() as $item) {
            $startIndex--;
            $up = Yii::$app->db->createCommand()->update(self::tableName(), ['sort_index' => $startIndex], 'id=:id', ['id' => $item['id']])->execute();
        }
    
    }
    
    public function createPage($parentNavId, $catId, $langId, $title, $rewrite, $layoutId)
    {
        $_errors = [];

        $nav = $this;
        $navItem = new \cmsadmin\models\NavItem();
        $navItemPage = new \cmsadmin\models\NavItemPage();

        $nav->attributes = ['parent_nav_id' => $parentNavId, 'cat_id' => $catId, 'is_hidden' => 1];
        $navItem->attributes = ['lang_id' => $langId, 'title' => $title, 'rewrite' => $rewrite, 'nav_item_type' => 1];
        $navItemPage->attributes = ['layout_id' => $layoutId];

        if (!$nav->validate()) {
            $_errors = ArrayHelper::merge($nav->getErrors(), $_errors);
        }
        if (!$navItem->validate()) {
            $_errors = ArrayHelper::merge($navItem->getErrors(), $_errors);
        }
        if (!$navItemPage->validate()) {
            $_errors = ArrayHelper::merge($navItemPage->getErrors(), $_errors);
        }

        if (!empty($_errors)) {
            return $_errors;
        }

        $navItemPage->save();
        $nav->save();

        $navItem->nav_item_type_id = $navItemPage->id;
        $navItem->nav_id = $nav->id;
        $navItemId = $navItem->save();

        return $navItemId;
    }

    public function createPageItem($navId, $langId, $title, $rewrite, $layoutId)
    {
        $_errors = [];

        $navItem = new \cmsadmin\models\NavItem();
        $navItemPage = new \cmsadmin\models\NavItemPage();

        $navItem->attributes = ['nav_id' => $navId, 'lang_id' => $langId, 'title' => $title, 'rewrite' => $rewrite, 'nav_item_type' => 1];
        $navItemPage->attributes = ['layout_id' => $layoutId];

        if (!$navItem->validate()) {
            $_errors = ArrayHelper::merge($navItem->getErrors(), $_errors);
        }
        if (!$navItemPage->validate()) {
            $_errors = ArrayHelper::merge($navItemPage->getErrors(), $_errors);
        }

        if (!empty($_errors)) {
            return $_errors;
        }

        $navItemPage->save();

        $navItem->nav_item_type_id = $navItemPage->id;
        $navItemId = $navItem->save();

        return $navItemId;
    }

    public function createModule($parentNavId, $catId, $langId, $title, $rewrite, $moduleName)
    {
        $_errors = [];

        $nav = $this;
        $navItem = new \cmsadmin\models\NavItem();
        $navItemModule = new \cmsadmin\models\NavItemModule();

        $nav->attributes = ['parent_nav_id' => $parentNavId, 'cat_id' => $catId, 'is_hidden' => 1];
        $navItem->attributes = ['lang_id' => $langId, 'title' => $title, 'rewrite' => $rewrite, 'nav_item_type' => 2];
        $navItemModule->attributes = ['module_name' => $moduleName];

        if (!$nav->validate()) {
            $_errors = ArrayHelper::merge($nav->getErrors(), $_errors);
        }
        if (!$navItem->validate()) {
            $_errors = ArrayHelper::merge($navItem->getErrors(), $_errors);
        }
        if (!$navItemModule->validate()) {
            $_errors = ArrayHelper::merge($navItemModule->getErrors(), $_errors);
        }

        if (!empty($_errors)) {
            return $_errors;
        }

        $navItemModule->save();
        $nav->save();

        $navItem->nav_item_type_id = $navItemModule->id;
        $navItem->nav_id = $nav->id;
        $navItemId = $navItem->save();

        return $navItemId;
    }

    public function createModuleItem($navId, $langId, $title, $rewrite, $moduleName)
    {
        $_errors = [];

        $navItem = new \cmsadmin\models\NavItem();
        $navItemModule = new \cmsadmin\models\NavItemModule();

        $navItem->attributes = ['nav_id' => $navId, 'lang_id' => $langId, 'title' => $title, 'rewrite' => $rewrite, 'nav_item_type' => 2];
        $navItemModule->attributes = ['module_name' => $moduleName];

        if (!$navItem->validate()) {
            $_errors = ArrayHelper::merge($navItem->getErrors(), $_errors);
        }
        if (!$navItemModule->validate()) {
            $_errors = ArrayHelper::merge($navItemModule->getErrors(), $_errors);
        }

        if (!empty($_errors)) {
            return $_errors;
        }

        $navItemModule->save();

        $navItem->nav_item_type_id = $navItemModule->id;
        $navItemId = $navItem->save();

        return $navItemId;
    }

    public static function getItemsData($navId, $displayHidden = false)
    {
        return \yii::$app->db->createCommand('SELECT t1.id, t1.parent_nav_id, t2.id as nav_item_id, t2.title, t2.rewrite, t3.rewrite AS cat_rewrite, t4.name AS lang_name, t4.id AS lang_id, t4.short_code AS lang_short_code FROM cms_nav as t1 LEFT JOIN (cms_nav_item as t2 LEFT JOIN (admin_lang as t4) ON (t2.lang_id=t4.id), cms_cat as t3) ON (t1.id=t2.nav_id AND t1.cat_id=t3.id) WHERE t1.parent_nav_id=:id AND t1.is_hidden=:hidden AND t1.is_deleted=0 ORDER by sort_index ASC')->bindValues([
            ':id' => $navId, ':hidden' => (int) $displayHidden
        ])->queryAll();
    }
}
