<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 01.04.21 04:38:14
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth;

use dicr\helper\Log;
use dicr\json\JsonEntity;
use dicr\validate\ValidateException;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * Запрос обмена кода авторизации на токен доступа.
 * А также для обновления токена по токену обновления.
 *
 * @link https://yandex.ru/dev/oauth/doc/dg/reference/auto-code-client.html
 * @link https://yandex.ru/dev/oauth/doc/dg/reference/refresh-client.html
 */
class TokenRequest extends JsonEntity
{
    /** @var string тип запроса - код авторизации */
    public const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';

    /** @var string тип запроса - токен обновления */
    public const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    /** @var string[] */
    public const GRANT_TYPE = [
        self::GRANT_TYPE_AUTHORIZATION_CODE,
        self::GRANT_TYPE_REFRESH_TOKEN
    ];

    /**
     * @var string Способ запроса OAuth-токена.
     * Если вы используете код подтверждения, укажите значение «authorization_code»
     * Для обновления токена - refresh_token.
     */
    public $grantType = self::GRANT_TYPE_AUTHORIZATION_CODE;

    /**
     * @var ?string Код подтверждения, полученный от Яндекс.OAuth.
     * Требуется при grantType = authorization_code
     * Время жизни предоставленного кода — 10 минут. По истечении этого времени код нужно запросить заново.
     */
    public $code;

    /**
     * @var ?string токен обновления.
     * Требуется при grantType = refresh_token
     */
    public $refreshToken;

    /** @var ?string Идентификатор приложения. Доступен в свойствах приложения */
    public $clientId;

    /** @var ?string Пароль приложения. Доступен в свойствах приложения */
    public $clientSecret;

    /**
     * @var ?string Уникальный идентификатор устройства, для которого запрашивается токен.
     * Чтобы обеспечить уникальность, достаточно один раз сгенерировать UUID и использовать его при каждом запросе
     * нового токена с данного устройства.
     * Идентификатор должен быть не короче 6 символов и не длиннее 50. Допускается использовать только печатаемые
     * ASCII-символы (с кодами от 32 до 126).
     */
    public $deviceId;

    /**
     * @var ?string Имя устройства, которое следует показывать пользователям.
     * Не длиннее 100 символов.
     */
    public $deviceName;

    /** @var OAuthClient */
    private $client;

    /**
     * CodeRequest constructor.
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
            ['grantType', 'required'],
            ['grantType', 'in', 'range' => self::GRANT_TYPE],

            ['code', 'trim'],
            ['code', 'default'],
            ['code', 'required', 'when' => fn() => $this->grantType === self::GRANT_TYPE_AUTHORIZATION_CODE],

            ['refreshToken', 'trim'],
            ['refreshToken', 'default'],
            ['refreshToken', 'required', 'when' => fn() => $this->grantType === self::GRANT_TYPE_REFRESH_TOKEN],

            ['clientId', 'trim'],
            ['clientId', 'default', 'value' => $this->client->clientId],

            ['clientSecret', 'trim'],
            ['clientSecret', 'default', 'value' => $this->client->clientSecret],

            ['deviceId', 'trim'],
            ['deviceId', 'default', 'value' => $this->client->deviceId],
            ['deviceId', 'string', 'min' => 6, 'max' => 50],

            ['deviceName', 'trim'],
            ['deviceName', 'default', 'value' => $this->client->deviceName],
            ['deviceName', 'string', 'max' => 100]
        ];
    }

    /**
     * Отправляет запрос и возвращает токен.
     *
     * @return Token
     * @throws Exception
     */
    public function send(): Token
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        $req = $this->client->httpClient
            ->post('/token', $this->json)
            ->setFormat(Client::FORMAT_URLENCODED);

        Log::debug('Запрос: ' . $req->toString());

        $res = $req->send();
        $res->format = Client::FORMAT_JSON;
        Log::debug('Ответ: ' . $res->toString());

        if (! $res->isOk) {
            throw new Exception('HTTP-error: ' . $res->statusCode);
        }

        if (empty($res->data['access_token'])) {
            throw new Exception($res->data['error_description'] ?? 'Не получен токен доступа');
        }

        return new Token([
            'json' => $res->data
        ]);
    }
}
