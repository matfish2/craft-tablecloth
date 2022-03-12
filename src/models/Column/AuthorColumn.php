<?php


namespace matfish\Tablecloth\models\Column;


use craft\elements\User;

class AuthorColumn extends ListColumn
{
    protected string $label = 'data';

    protected function getOptions(): array
    {
        $res = [];

        $users = User::find()->all();

        foreach ($users as $user) {
            $res[] = [
                'value' => $user->id,
                'data' =>
                    [
                        'fullName' => $user->firstName ? $user->firstName . ' ' . $user->lastName : $user->username,
                        'username' => $user->username,
                        'photoUrl' => $user->photo ? $user->photo->url : null
                    ]
            ];
        }

        return $res;
    }
}