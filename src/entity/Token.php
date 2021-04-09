<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 09.04.21 07:58:58
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth\entity;

use dicr\json\JsonEntity;

/**
 * Токен доступа.
 *
 * @property-read ?int $expireTime timestamp окончания
 */
class Token extends JsonEntity
{
    /** @var int timestamp времени создания */
    public $created;

    /** @var string Тип выданного токена. Всегда принимает значение «bearer». */
    public $token_type;

    /** @var string OAuth-токен с запрошенными правами или с правами, указанными при регистрации приложения. */
    public $access_token;

    /** @var int Время жизни токена в секундах. */
    public $expires_in;

    /** @var string Токен, который можно использовать для продления срока жизни соответствующего OAuth-токена. */
    public $refresh_token;

    /**
     * @var string разделенные пробелом права доступа
     * Права, запрошенные разработчиком или указанные при регистрации приложения. Поле scope является дополнительным
     * и возвращается, если OAuth предоставил токен с меньшим набором прав, чем было запрошено.
     */
    public $scope;

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $this->created = time();

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function attributeFields(): array
    {
        // для соответствия стандартному токену не конвертируем в camelCase
        return [];
    }

    /**
     * Время окончания.
     *
     * @return ?int
     */
    public function getExpireTime(): ?int
    {
        return empty($this->expires_in) ? null : $this->created + $this->expires_in;
    }
}
