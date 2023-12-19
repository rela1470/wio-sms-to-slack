<?php
namespace App;
use Dotenv\Dotenv;

class Env {

    private static ?Dotenv $_instance = null;

    /**
     * initialize
     * @return void
     */
    public static function initialize(): void
    {
        self::$_instance = Dotenv::createMutable(__DIR__ . "/../");
        self::$_instance->load();
    }

    /**
     * get
     * @param $key
     * @return mixed|null
     */
    public static function get($key): mixed
    {
        return $_ENV[$key] ?? null;
    }
}
Env::initialize();
