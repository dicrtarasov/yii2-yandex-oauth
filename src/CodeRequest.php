<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 01.04.21 02:23:45
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth;

use dicr\json\JsonEntity;
use dicr\validate\StringsValidator;
use dicr\validate\ValidateException;

use function http_build_query;
use function implode;

/**
 * Запрос кода авторизации приложения.
 *
 * @link https://yandex.ru/dev/oauth/doc/dg/reference/auto-code-client.html
 */
class CodeRequest extends JsonEntity
{
    /** @var string запрос кода */
    public const RESPONSE_TYPE_CODE = 'code';

    /** @var string[] типы запросов */
    public const RESPONSE_TYPE = [
        self::RESPONSE_TYPE_CODE
    ];

    /**
     * @var string Требуемый ответ.
     * При запросе кода подтверждения следует указать значение «code».
     */
    public $responseType = self::RESPONSE_TYPE_CODE;

    /**
     * @var string идентификатор зарегистрированного приложения
     * Доступен в свойствах приложения.
     */
    public $clientId;

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

    /**
     * @var ?string URL, на который нужно перенаправить пользователя после того, как он разрешил или отказал
     * приложению в доступе.
     * По умолчанию используется первый Callback URI, указанный в настройках приложения (Платформы → Веб-сервисы →
     * Callback URI). В значении параметра допустимо указывать только те адреса, которые перечислены в настройках
     * приложения. Если совпадение неточное, параметр игнорируется.
     */
    public $redirectUri;

    /**
     * @var ?string Явное указание аккаунта, для которого запрашивается токен. В значении параметра можно передавать
     * логин аккаунта на Яндексе, а также адрес Яндекс.Почты или Яндекс.Почты для домена.
     */
    public $loginHint;

    /**
     * @var string[]|null Список необходимых приложению в данный момент прав доступа, разделенных пробелом.
     * Права должны запрашиваться из перечня, определенного при регистрации приложения. Узнать допустимые права можно
     *     по ссылке https://oauth.yandex.ru/client/<client_id>/info
     */
    public $scope;

    /**
     * @var string[]|null Список разделенных пробелом опциональных прав доступа, без которых приложение может обойтись.
     */
    public $optionalScope;

    /**
     * @var ?bool у пользователя обязательно нужно запросить разрешение на доступ к аккаунту (даже если пользователь
     * уже разрешил доступ данному приложению).
     * Получив этот параметр, Яндекс.OAuth предложит пользователю разрешить доступ приложению и
     * выбрать нужный аккаунт Яндекса.
     */
    public $forceConfirm;

    /**
     * @var ?string Строка состояния, которую Яндекс.OAuth возвращает без изменения. Максимальная допустимая
     * длина строки — 1024 символа.
     */
    public $state;

    /** @var OauthClient */
    private $client;

    /**
     * CodeRequest constructor.
     *
     * @param OauthClient $client
     * @param array $config
     */
    public function __construct(OauthClient $client, $config = [])
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
            ['responseType', 'required'],
            ['responseType', 'in', 'range' => self::RESPONSE_TYPE],

            ['clientId', 'trim'],
            ['clientId', 'default', 'value' => $this->client->clientId],
            ['clientId', 'required'],

            ['deviceId', 'trim'],
            ['deviceId', 'default', 'value' => $this->client->deviceId],
            ['deviceId', 'string', 'min' => 6, 'max' => 50],

            ['deviceName', 'trim'],
            ['deviceName', 'default', 'value' => $this->client->deviceName],
            ['deviceName', 'string', 'max' => 100],

            ['redirectUri', 'default'],
            ['redirectUri', 'url'],

            ['loginHint', 'trim'],
            ['loginHint', 'default'],

            [['scope', 'optionalScope'], 'default'],
            [['scope', 'optionalScope'], StringsValidator::class],

            ['forceConfirm', 'default'],
            ['forceConfirm', 'boolean'],
            ['forceConfirm', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['state', 'default'],
            ['state', 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributesToJson(): array
    {
        return [
            'scope' => static fn($val) => empty($val) ? null : implode(' ', (array)$val),
            'optionalScope' => static fn($val) => empty($val) ? null : implode(' ', (array)$val),
        ];
    }

    /**
     * Возвращает URL для переадресации клиента.
     *
     * @return string
     * @throws ValidateException
     */
    public function oauthUrl(): string
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        return OauthClient::URL_BASE . '?' . http_build_query($this->json);
    }
}
