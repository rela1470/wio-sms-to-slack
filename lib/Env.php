<?php
namespace App;

use Aws\SecretsManager\SecretsManagerClient;
use Dotenv\Dotenv;

class Env {

    private static bool $_loaded = false;

    /**
     * get
     * ローカルは .env、Lambda は環境変数と Secrets Manager (SECRETS_MANAGER_NAME) から読む
     * @param $key
     * @return mixed|null
     */
    public static function get($key): mixed
    {
        if (! self::$_loaded) {
            self::loadDotenv();
            self::loadSecretsManager();
            self::$_loaded = true;
        }
        if (array_key_exists($key, $_ENV)) return $_ENV[$key];

        $value = getenv($key);
        return $value === false ? null : $value;
    }

    /**
     * loadDotenv
     * Lambda には .env を同梱しないので、無くてもエラーにしない
     * @return void
     */
    private static function loadDotenv(): void
    {
        Dotenv::createImmutable(__DIR__ . "/../")->safeLoad();
    }

    /**
     * loadSecretsManager
     * JSON の Secret を読み、$_ENV に無いキーだけ足す
     * @return void
     */
    private static function loadSecretsManager(): void
    {
        $name = ($_ENV['SECRETS_MANAGER_NAME'] ?? getenv('SECRETS_MANAGER_NAME')) ?: null;
        if (! $name) return;

        try {
            $client = new SecretsManagerClient([
                'region'  => (getenv('AWS_REGION') ?: 'ap-northeast-1'),
                'version' => 'latest',
                'http'    => ['connect_timeout' => 3, 'timeout' => 5],
            ]);
            $result = $client->getSecretValue(['SecretId' => $name]);
            $secrets = json_decode($result['SecretString'] ?? '', true);
            if (! is_array($secrets)) {
                error_log("[Env] SecretsManager '{$name}' is not valid JSON object");
                return;
            }
            foreach ($secrets as $k => $v) {
                if (! array_key_exists($k, $_ENV)) $_ENV[$k] = is_scalar($v) ? (string)$v : json_encode($v);
            }
        } catch (\Throwable $e) {
            error_log('[Env] SecretsManager load failed: ' . $e->getMessage());
        }
    }
}
