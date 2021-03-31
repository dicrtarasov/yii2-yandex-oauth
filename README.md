# Яндекс.OAuth клиент для Yii2

API: https://yandex.ru/dev/oauth/doc/dg/concepts/about.html

Способ получить отладочный токен вручную, без обработки callback:
https://yandex.ru/dev/oauth/doc/dg/tasks/get-oauth-token.html

## Настройка

```php
$config = [
    'components' => [
        'yandex-oauth' => [
            'class' => dicr\yandex\oauth\OAuthClient::class,
            'clientId' => 'ИД приложения',
        ]
    ]
];
```

## Использование

```php
/** @var dicr\yandex\oauth\OAuthClient $oauth */
$oauth = Yii::$app->get('yandex-oauth'); 

/** @var dicr\yandex\oauth\CodeRequest $req запрос кода авторизации */
$req = $oauth->codeRequest();

/** @var string $oauthUrl адрес для переадресации клиента */
$oauthUrl = $req->oauthUrl();

// переадресация клиента на Яндекс.OAuth
Yii::$app->end(0, Yii::$app->response->redirect($oauthUrl));
```
