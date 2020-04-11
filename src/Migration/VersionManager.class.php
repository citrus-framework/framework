<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Migration;

use Citrus\CitrusException;
use Citrus\Command\Console;
use Citrus\Database\DSN;
use Citrus\Variable\Structs;
use PDO;

/**
 * マイグレーションバージョン管理
 */
class VersionManager
{
    use Console;
    use Structs;

    /** @var PDO DBハンドラ */
    private $handler;

    /** @var DSN DB接続情報 */
    private $dsn;



    /**
     * constructor.
     *
     * @param DSN $dsn
     */
    public function __construct(DSN $dsn)
    {
        $this->dsn = $dsn;
        $this->handler = new PDO($this->dsn->toStringWithAuth());
        // マイグレーションのセットアップ
        $this->setupMigration();
    }



    /**
     * マイグレーションのセットアップ
     *
     * @return void
     */
    public function setupMigration(): void
    {
        // マイグレーション管理テーブルの生成
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS {SCHEMA}cf_migrations (
    version_code CHARACTER VARYING(32) NOT NULL,
    migrated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (version_code)
);
SQL;
        self::executeQuery($query);
    }



    /**
     * マイグレーションの正方向実行
     *
     * @param Item $item
     * @return void
     * @throws CitrusException
     */
    public function up(Item $item): void
    {
        // バージョン
        $version = $item->version();
        // クラス名
        $class_name = get_class($item);

        // ログ：バージョン操作開始
        $this->format('%s up. executing.', $class_name);

        // バージョンアップできるか
        if (false === $this->isUp($version))
        {
            // ログ：バージョン操作対象外
            $this->format('%s up. is already.', $class_name);
            return;
        }

        // 実行開始タイム
        $microsecond = microtime(true);

        // 正方向クエリ
        $query = $item->up();
        $result = $this->executeQuery($query);

        // 実行終了タイム
        $execute_microsecond = (microtime(true) - $microsecond);

        // バージョン情報の登録
        if (true === $result)
        {
            $this->registVersion($version);
        }

        // ログ：実行結果
        $method = (true === $result ? 'success' : 'failure');
        $this->$method(sprintf('%s up. %s. %f μs.', $class_name, $method, $execute_microsecond));
    }



    /**
     * マイグレーションの逆方向実行
     *
     * @param Item $item
     * @return void
     */
    public function down(Item $item): void
    {
        // バージョン
        $version = $item->version();
        // クラス名
        $class_name = get_class($item);

        // ログ：バージョン操作開始
        $this->format('%s down. executing.', $class_name);

        // バージョンダウンできるか
        if (false === $this->isDown($version))
        {
            // ログ：バージョン操作対象外
            $this->format('%s down. is already.', $class_name);
            return;
        }

        // 実行開始タイム
        $microsecond = microtime(true);

        // クエリ実行
        $query = $item->down();
        $result = $this->executeQuery($query);

        // 実行終了タイム
        $execute_microsecond = (microtime(true) - $microsecond);

        // バージョン情報の削除
        if (true === $result)
        {
            $this->removeVersion($version);
        }

        // ログ：実行結果
        $method = (true === $result ? 'success' : 'failure');
        $this->$method(sprintf('%s down. %s. %f μs.', $class_name, $method, $execute_microsecond));
    }



    /**
     * スキーマ指定の置換
     *
     * @param string $query 置換対象文字列
     * @return string 置換済み文字列
     */
    public function replaceSchema(string $query): string
    {
        // スキーマに文字列があれば、ドットでつなぐ
        $schema = $this->dsn->schema;
        if (false === is_null($schema) && 0 < strlen($schema))
        {
            $schema .= '.';
        }
        return str_replace('{SCHEMA}', $schema, $query);
    }



    /**
     * 指定のバージョンの正方向実行が可能か
     *
     * @param string $version
     * @return bool 正方向実行可能(未UP状態)
     */
    private function isUp(string $version): bool
    {
        return (false === $this->existVersion($version));
    }



    /**
     * 指定のバージョンの逆方向実行が可能か
     *
     * @param string $version
     * @return bool 逆方向実行可能(UP済み状態)
     */
    private function isDown(string $version): bool
    {
        return (true === $this->existVersion($version));
    }



    /**
     * クエリの実行
     *
     * @param string $query 実行したいクエリー
     * @return bool true:成功,false:失敗
     */
    private function executeQuery(string $query): bool
    {
        // スキーマ置換
        $query = $this->replaceSchema($query);
        // クエリ実行
        $result = $this->handler->exec($query);

        return (false === $result ? false : true);
    }



    /**
     * プリペアクエリの実行
     *
     * @param string $query      実行したいクエリー
     * @param array  $parameters パラメータ
     * @return \PDOStatement|null
     */
    private function prepareQuery(string $query, array $parameters): ?\PDOStatement
    {
        // スキーマ置換
        $query = $this->replaceSchema($query);
        // プリペア実行
        $statement = $this->handler->prepare($query);
        $result = $statement->execute($parameters);
        return (true === $result ? $statement : null);
    }



    /**
     * 指定のバージョンの実行ログが存在するか
     *
     * @param string $version チェックしたいバージョン
     * @return bool true:存在する,false:存在しない
     */
    private function existVersion(string $version): bool
    {
        $statement = self::prepareQuery('SELECT * FROM {SCHEMA}cf_migrations WHERE version_code = :version_code;', [
            ':version_code' => $version,
        ]);
        if (true === is_null($statement))
        {
            return false;
        }
        // 件数が0を超える場合、対象バージョンが存在する
        return (0 < count($statement->fetchAll(PDO::FETCH_ASSOC)) ? true : false);
    }



    /**
     * バージョン情報の登録
     *
     * @param string $version
     * @return void
     * @throws CitrusException
     */
    private function registVersion(string $version): void
    {
        $now = null;
        try
        {
            $now = (new \DateTime())->format('Y-m-d H:i:s T');
        }
        catch (\Exception $e)
        {
            throw CitrusException::convert($e);
        }
        self::prepareQuery('INSERT INTO {SCHEMA}cf_migrations (version_code, migrated_at) VALUES (:version_code, :migrated_at);', [
            ':version_code' => $version,
            ':migrated_at' => $now,
        ]);
    }



    /**
     * バージョン情報の削除
     *
     * @param string $version
     * @return void
     */
    private function removeVersion(string $version): void
    {
        self::prepareQuery('DELETE FROM {SCHEMA}cf_migrations WHERE version_code = :version_code;', [
            ':version_code' => $version,
        ]);
    }
}
