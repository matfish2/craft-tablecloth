<?php


namespace matfish\Tablecloth\models\Column;


use craft\records\User;

class AuthorColumn extends ListColumn
{
//    public function getDbColumn(string $context = self::CONTEXT_SELECT): string
//    {
//        $addAlias = $context === self::CONTEXT_SELECT;
//
//        $handle = $context === self::CONTEXT_PREFILTER ? "authorId" : "CONCAT(`users`.`firstName`, ' ' ,`users`.`lastName`)";
//
//        if ($addAlias) {
//            $handle .= ' as `authorId__text`';
//        }
//
//        return $handle;
//    }

    protected function getOptions(): array
    {
        $res = [];

        $users = User::find()->all();

        foreach ($users as $user) {
            $res[] = [
                'value'=>$user->id,
                'label'=>$user->firstName ? $user->firstName . ' ' . $user->lastName :  $user->username
            ];
        }

        return $res;
    }
}