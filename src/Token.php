<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 01.04.21 01:18:00
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth;

use dicr\json\JsonEntity;

/**
 * Токен доступа.
 */
class Token extends JsonEntity
{
    /** @var string Тип выданного токена. Всегда принимает значение «bearer». */
    public $tokenType;

    /** @var string OAuth-токен с запрошенными правами или с правами, указанными при регистрации приложения. */
    public $accessToken;

    /** @var int Время жизни токена в секундах. */
    public $expiresIn;

    /** @var string Токен, который можно использовать для продления срока жизни соответствующего OAuth-токена. */
    public $refreshToken;

    /**
     * @var string[]|null
     * Права, запрошенные разработчиком или указанные при регистрации приложения. Поле scope является дополнительным
     * и возвращается, если OAuth предоставил токен с меньшим набором прав, чем было запрошено.
     */
    public $scope;

    /**
     * @inheritDoc
     */
    public function attributesToJson(): array
    {
        return [
            'scope' => static fn($val) => empty($val) ? null : implode(' ', (array)$val)
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributesFromJson(): array
    {
        return [
            'scope' => static fn($val) => empty($val) ? null : explode(' ', (string)$val)
        ];
    }
}
