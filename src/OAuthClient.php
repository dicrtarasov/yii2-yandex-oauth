<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 07.04.21 23:21:00
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;

use function is_array;

use const CURLOPT_ENCODING;

/**
 * Яндекс.OAuth клиент.
 *
 * @link https://yandex.ru/dev/oauth/doc/dg/concepts/about.html
 */
class OAuthClient extends Component
{
    /** @var string базовый URL */
    public const URL_BASE = 'https://oauth.yandex.ru';

    /** @var string идентификатор зарегистрированного приложения */
    public $clientId;

    /** @var string Пароль приложения. Доступен в свойствах приложения */
    public $clientSecret;

    /**
     * @var ?string Идентификатор устройства, для которого запрашивается токен.
     * Если идентификатор был задан в параметре device_id при запросе кода подтверждения, при запросе токена
     * параметры device_id и device_name игнорируются.
     */
    public $deviceId;

    /**
     * @var ?string Имя устройства, для которого запрашивается токен.
     * Если при запросе кода подтверждения был передан параметр device_id, при запросе токена параметры
     * device_id и device_name игнорируются.
     */
    public $deviceName;

    /** @var Client HTTP-клиент */
    public $httpClient;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->clientId)) {
            throw new InvalidConfigException('clientId');
        }

        if (empty($this->clientSecret)) {
            throw new InvalidConfigException('clientSecret');
        }

        $this->deviceId = trim((string)$this->deviceId) ?: null;
        $this->deviceName = trim((string)$this->deviceName) ?: null;

        if (empty($this->httpClient) || is_array($this->httpClient)) {
            $this->httpClient = array_merge([
                'class' => Client::class,
                'transport' => CurlTransport::class,
                'baseUrl' => self::URL_BASE,
                'requestConfig' => [
                    'options' => [
                        CURLOPT_ENCODING => ''
                    ]
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_JSON
                ]
            ], $this->httpClient ?: []);
        }

        $this->httpClient = Instance::ensure($this->httpClient, Client::class);
    }

    /**
     * Создает запрос на получения кода авторизации.
     *
     * @param array $config
     * @return CodeRequest
     */
    public function codeRequest(array $config = []): CodeRequest
    {
        return new CodeRequest($this, $config);
    }

    /**
     * Создает запрос на получение токена.
     *
     * @param array $config
     * @return TokenRequest
     */
    public function tokenRequest(array $config = []): TokenRequest
    {
        return new TokenRequest($this, $config);
    }

    /**
     * Запрос отзыва токена.
     *
     * @param array $config
     * @return RevokeRequest
     */
    public function revokeRequest(array $config = []): RevokeRequest
    {
        return new RevokeRequest($this, $config);
    }

    /**
     * Запрос информации о пользователе.
     *
     * @param array $config
     * @return UserInfoRequest
     */
    public function userInfoRequest(array $config = []): UserInfoRequest
    {
        return new UserInfoRequest($this, $config);
    }
}
