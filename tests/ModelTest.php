<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Model;
use \PDO;

class ModelTest extends TestCase
{
    public function testUpdate()
    {
        $pdo = $this->createMock(PDO::class);

        $model = new Model($pdo);

        $model->update('test', ['test' => '0'], '', []);

        $this->assertEquals('UPDATE test SET test=? ', $model->db->sql);

        return $model;
    }

    public function testUpdateMultiple()
    {
        $pdo = $this->createMock(PDO::class);

        $model = new Model($pdo);

        $model->update('test', ['test' => '0', 'name' => 'wot'], '', []);

        $this->assertEquals('UPDATE test SET test=?,name=? ', $model->db->sql);

        return $model;
    }

    public function testUpdateWhere()
    {
        $pdo = $this->createMock(PDO::class);

        $model = new Model($pdo);

        $model->update('test', ['test' => '0'], 'id = ?', ['1']);

        $this->assertEquals('UPDATE test SET test=? WHERE id = ?', $model->db->sql);

        return $model;
    }

    public function testInsert()
    {
        $pdo = $this->createMock(PDO::class);

        $model = new Model($pdo);

        $model->insert('test', ['test' => '0']);

        $this->assertEquals('INSERT INTO test (test) VALUES(?)', $model->db->sql);

        return $model;
    }
}
