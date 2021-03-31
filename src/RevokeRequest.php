<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 01.04.21 03:01:23
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth;

use dicr\helper\Log;
use dicr\json\JsonEntity;
use dicr\validate\ValidateException;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * Запрос на отзыв токена.
 */
class RevokeRequest extends JsonEntity
{
    /** @var string OAuth-токен, который нужно отозвать. */
    public $accessToken;

    /** @var ?string Идентификатор приложения. Доступен в свойствах приложения */
    public $clientId;

    /** @var ?string Пароль приложения. Доступен в свойствах приложения */
    public $clientSecret;

    /** @var OAuthClient */
    private $client;

    /**
     * RevokeRequest constructor.
     *
     * @param OAuthClient $client
     * @param array $config
     */
    public function __construct(OAuthClient $client, $config = [])
    {
        $this->client = $client;

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            ['accessToken', 'trim'],
            ['accessToken', 'required'],

            ['clientId', 'trim'],
            ['clientId', 'default', 'value' => $this->client->clientId],

            ['clientSecret', 'trim'],
            ['clientSecret', 'default', 'value' => $this->client->clientSecret],
        ];
    }

    /**
     * Отправка запроса.
     *
     * @throws Exception
     */
    public function send(): void
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        $req = $this->client->httpClient
            ->post('/revoke_token', $this->json)
            ->setFormat(Client::FORMAT_URLENCODED);

        Log::debug('Запрос: ' . $req->toString());
        $res = $req->send();
        Log::debug('Ответ: ' . $res->toString());

        $res->format = Client::FORMAT_JSON;
        if (empty($res->data['status']) || $res->data['status'] !== 'ok') {
            throw new Exception($res->data['error_description'] ?? $res->data['error'] ?? 'Ошибка отзыва токена');
        }
    }
}
