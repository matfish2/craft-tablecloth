<?php

namespace matfish\Tablecloth\models\Column;

use craft\elements\User;

class UsersColumn extends RelationsColumn
{
    protected function getOptions(): array
    {
        $field = $this->getField();

        $sql = "SELECT [[targetId]] [[userId]] from {{%relations}} [[relations]] 
JOIN {{%users}} [[users]] ON [[users.id]]=[[relations.targetId]] 
WHERE [[fieldId]]=$field->id
GROUP BY 1";

        $rows = array_map(static function ($row) {
            return $row['userId'];
        }, \Craft::$app->db->createCommand($sql)->queryAll());

        $rows = implode(',', $rows);

        $users = User::find()->where("[[users.id]] in ($rows)")->all();

        $res = [];

        foreach ($users as $user) {
            $res[] = [
                'value' => $user->id,
                'data' => [
                    'firstName'=>$user->firstName,
                    'lastName'=>$user->lastName,
                    'username'=>$user->username,
                    'fullName' => $user->fullName,
                ]
            ];
        }

        return $res;
    }
}