<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test;

use Citrus\Authentication;
use Citrus\Authentication\Database;
use Citrus\Authentication\Item;
use Citrus\Configure\ConfigureException;
use Citrus\Database\Connection\Connection;
use Citrus\Database\DSN;
use PHPUnit\Framework\TestCase;

/**
 * 認証処理のテスト
 */
class AuthenticationTest extends TestCase
{
    use TestFile;

    /** @var string 出力ディレクトリ */
    private $output_dir;

    /** @var string SQLITEファイル */
    private $sqlite_file;

    /** @var array 設定配列 */
    private $configures;

    /** @var Connection */
    private $connection;



    /**
     * {@inheritDoc}
     * @throws ConfigureException
     */
    public function setUp(): void
    {
        parent::setUp();

        // 出力ディレクトリ
        $this->output_dir = __DIR__ . '/Sample/Integration/temp';
        $this->sqlite_file = $this->output_dir . '/test.sqlite';

        // 設定配列
        $database = [
            'type'      => 'sqlite',
            'hostname'  => $this->sqlite_file,
        ];
        $this->configures = [
            'default' => [
                'authentication' => [
                    'type' => 'database',
                    'database' => $database,
                ],
                'database' => $database,
            ],
        ];

        // ディレクトリ生成
        mkdir($this->output_dir);
        chmod($this->output_dir, 0755);
        chown($this->output_dir, posix_getpwuid(posix_geteuid())['name']);
        chgrp($this->output_dir, posix_getgrgid(posix_getegid())['name']);

        // データ生成
        $pdo = new \PDO(sprintf('sqlite:%s', $this->sqlite_file));
        $pdo->query('CREATE TABLE users (user_id INT, password TEXT, token TEXT, keep_at TEXT, status INT, created_at TEXT, updated_at TEXT, rowid INT, rev INT);');
        $pdo->query('INSERT INTO users VALUES (1, "'. password_hash('hogehoge', PASSWORD_DEFAULT) .'", "", "", 0, "2019-01-01", "2019-01-01", 1, 1);');

        $dsn = DSN::getInstance()->loadConfigures($this->configures);
        $this->connection = new Connection($dsn);
    }



    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // ディレクトリがあったら削除
        $this->forceRemove($this->output_dir);
    }



    /**
     * @test
     * @throws ConfigureException
     */
    public function loadConfigures_設定を読み込んで適用できる()
    {
        // 生成
        /** @var Authentication $authentication */
        $authentication = Authentication::sharedInstance()->loadConfigures($this->configures);

        // 検証
        $this->assertInstanceOf(Database::class, $authentication->protocol);
    }



    /**
     * @test
     */
    public function authorize_認証を通す()
    {
        /** @var Authentication $authentication */
        $authentication = Authentication::sharedInstance()->loadConfigures($this->configures);

        // 認証処理
        $authItem = new Item();
        $authItem->user_id = 1;
        $authItem->password = 'hogehoge';
        $is_auth = $authentication->authorize($authItem);
        $this->assertTrue($is_auth);
    }
}
