<?php

namespace account\base;

class Controller extends \luya\base\Controller
{
    public function getRules()
    {
        return [
            [
                'allow' => true,
                'actions' => [], // apply to all actions by default
                'roles' => ['@'],
            ],
        ];
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'user' => $this->module->getUserIdentity(),
                'rules' => $this->getRules(),
            ],
        ];
    }
}
