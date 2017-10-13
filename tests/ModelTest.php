<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Model;

use Tests\Mocks\mockPDO as PDO;

class ModelTest extends TestCase
{
    public function testUpdate()
    {
        $pdo = new PDO();
        $model = new Model($pdo);

        $model->update('test', ['test' => '0'], '', []);

        $this->assertEquals('UPDATE test SET test=? ', $model->db->sql);

        return $model;
    }

    public function testUpdateWhere()
    {
        $pdo = new PDO();
        $model = new Model($pdo);

        $model->update('test', ['test' => '0'], 'id = ?', ['1']);

        $this->assertEquals('UPDATE test SET test=? WHERE id = ?', $model->db->sql);

        return $model;
    }

    public function testInsert()
    {
        $pdo = new PDO();
        $model = new Model($pdo);

        $model->insert('test', ['test' => '0']);

        $this->assertEquals('INSERT INTO test (test) VALUES(?)', $model->db->sql);

        return $model;
    }
}