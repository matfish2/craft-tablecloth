<?php


namespace matfish\Tablecloth\services\dataset;


class UnsafeSqlRegex
{
    public static array $regex = [
        '/join /i',
        '/select /i',
        '/insert /i',
        '/delete /i ',
        '/update /i ',
        '/create /i',
        '/from /i',
        '/truncate /i',
        '/drop /i',
        '/table /i'
    ];
}