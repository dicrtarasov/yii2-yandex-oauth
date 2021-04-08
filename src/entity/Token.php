<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 09.04.21 04:19:36
 */

declare(strict_types = 1);
namespace dicr\yandex\oauth\entity;

use dicr\json\JsonEntity;
use InvalidArgumentException;

/**
 * Токен доступа.
 *
 * @property int $createTime timestamp создания токена
 * @property-read ?int $expireTime timestamp время создания
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

    /** @var int время создания */
    private $_createTime;

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $this->_createTime = time();

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'createTime'
        ]);
    }

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

    /**
     * Время создания.
     *
     * @return int
     */
    public function getCreateTime(): int
    {
        return $this->_createTime;
    }

    /**
     * Установить время создания.
     *
     * @param int $time
     */
    public function setCreateTime(int $time): void
    {
        if ($time <= 0) {
            throw new InvalidArgumentException('time: ' . $time);
        }

        $this->_createTime = $time;
    }

    /**
     * Время окончания.
     *
     * @return ?int
     */
    public function getExpireTime(): ?int
    {
        return empty($this->expiresIn) ? null : $this->_createTime + $this->expiresIn;
    }
}
