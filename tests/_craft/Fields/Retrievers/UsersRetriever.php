<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class UsersRetriever extends FieldValueRetriever
{
    public function transform($value)
    {
        $res = [];

        foreach ($value->all() as $user) {
            $res[] = [
                'data' => [
                    'fullName' => $user->fullName,
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'username' => $user->username
                ],
                'value' => (string)$user->id
            ];
        }

        return $res;
    }
}