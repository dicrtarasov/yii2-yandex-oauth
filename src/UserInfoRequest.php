<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 08.04.21 02:54:57
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth;

use dicr\helper\Log;
use dicr\json\JsonEntity;
use dicr\validate\ValidateException;
use dicr\yandex\oauth\entity\UserInfo;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * Запрос информации о пользователе.
 *
 * @link https://yandex.ru/dev/passport/doc/dg/reference/request.html
 */
class UserInfoRequest extends JsonEntity
{
    /** @var string OAuth-токен, который разрешает доступ к данным учетной записи пользователя через API Яндекс ID. */
    public $oauthToken;

    /** @var ?bool Признак запроса OpenID-идентификаторов, которые пользователь мог получить от Яндекса. */
    public $withOpenidIdentity;

    /** @var OAuthClient */
    private $client;

    /**
     * AuthorizeRequest constructor.
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
            ['oauthToken', 'trim'],
            ['oauthToken', 'required'],

            ['withOpenidIdentity', 'default'],
            ['withOpenidIdentity', 'boolean'],
            ['withOpenidIdentity', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true]
        ];
    }

    /**
     * Отправка запроса.
     *
     * @return UserInfo информация о пользователе
     * @throws Exception
     */
    public function send(): UserInfo
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        $url = 'https://login.yandex.ru/info';
        if ($this->withOpenidIdentity !== null) {
            $url .= '?with_openid_identity=' . $this->withOpenidIdentity;
        }

        $req = $this->client->httpClient->get($url, null, [
            'Authorization' => 'OAuth ' . $this->oauthToken
        ]);

        Log::debug('Запрос: ' . $req->toString());
        $res = $req->send();
        Log::debug('Ответ: ' . $req->send());
        $res->format = Client::FORMAT_JSON;

        return new UserInfo([
            'json' => $res->data
        ]);
    }
}
