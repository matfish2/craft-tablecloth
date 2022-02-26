<?php


namespace tableclothtests\_craft\Fields\Creators;


use craft\elements\User;

class UsersCreator extends FieldCreator
{
    public function getFieldData(): array
    {
        $this->generateUsers();

        return [
            $this->settingsKey=>[
                'sources'=>'*'
            ]
        ];
    }

    private function generateUsers() : void
    {
        $list = [
          [
              'username'=>'yuda',
              'firstName'=>'Yehuda',
              'lastName'=>'Fox',
              'email'=>'yudafox@gmail.com'
          ],
            [
              'username'=>'nachman',
              'firstName'=>'Nachman',
              'lastName'=>'Rosen',
              'email'=>'nachmanrosen@gmail.com'
          ],
            [
              'username'=>'cristiano',
              'firstName'=>'Cristiano',
              'lastName'=>'Seagull',
              'email'=>'crsiti@gmail.com'
          ],
            [
              'username'=>'rachel',
              'firstName'=>'Rachel',
              'lastName'=>'Morgan',
              'email'=>'rachelmorgan@hotmail.com'
          ],
            [
              'username'=>'matfish',
              'firstName'=>'Matanya',
              'lastName'=>'Fishaimer',
              'email'=>'matanyafishaimer@daneltech.com'
          ]
        ];

        foreach ($list as $user) {
            $u = new User($user);
            \Craft::$app->elements->saveElement($u);
        }
    }
}