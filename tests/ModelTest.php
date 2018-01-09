<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Application;
use Prim\Model;

use Tests\Mocks\Container;

class ModelTest extends TestCase
{
    public function testUpdate()
    {
        $container = new Container();
        $app = new Application($container, $container->getController('Tests\Mocks\Controller'));
        $model = new Model($container);

        $model->update('test', ['test' => '0'], '', []);

        $this->assertEquals('UPDATE test SET test=? ', $model->db->sql);

        return $model;
    }

    public function testUpdateMultiple()
    {
        $container = new Container();
        $app = new Application($container, $container->getController('Tests\Mocks\Controller'));
        $model = new Model($container);

        $model->update('test', ['test' => '0', 'name' => 'wot'], '', []);

        $this->assertEquals('UPDATE test SET test=?,name=? ', $model->db->sql);

        return $model;
    }

    public function testUpdateWhere()
    {
        $container = new Container();
        $app = new Application($container, $container->getController('Tests\Mocks\Controller'));
        $model = new Model($container);

        $model->update('test', ['test' => '0'], 'id = ?', ['1']);

        $this->assertEquals('UPDATE test SET test=? WHERE id = ?', $model->db->sql);

        return $model;
    }

    public function testInsert()
    {
        $container = new Container();
        $model = new Model($container);

        $model->insert('test', ['test' => '0']);

        $this->assertEquals('INSERT INTO test (test) VALUES(?)', $model->db->sql);

        return $model;
    }
}