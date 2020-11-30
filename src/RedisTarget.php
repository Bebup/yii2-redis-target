<?php

namespace bebup\log;

use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\log\Target;
use yii\redis\Connection;

/**
 * Description of RedisTarget
 *
 * @author gpayo
 */
class RedisTarget extends Target {
    /**
     * @var Connection|array|string the Redis connection object
     * or a configuration array for creating the object,
     * or the application component ID of the Redis connection.
     */
    public $redis = 'redis';

    /**
     * @var string key of the Redis list to store log messages. Default to "log"
     */
    public $key = 'log';

    /**
     * The time to expire the key
     *
     * @var int
     */
    public $expire = 15552000; // 6 months

    /**
     * Initializes the RedisTarget component.
     * This method will initialize the [[redis]] property to make sure it refers to a valid Redis connection.
     *
     * @throws InvalidConfigException if [[redis]] is invalid.
     */
    public function init() {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::className());
    }

    /**
     * Stores log messages to Redis.
     */
    public function export() {
        foreach ($this->messages as $message) {
            $text = $this->formatMessage($message);
            $key = sprintf('%s:%s', $this->key, date('Y-m-d'));
            $this->redis->executeCommand('ZADD', [$key, time(), $text]);
            $this->redis->executeCommand('EXPIRE', [$key, $this->expire]);
        }
    }
}
