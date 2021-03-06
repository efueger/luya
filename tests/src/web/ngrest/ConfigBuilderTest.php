<?php

namespace tests\src\web\ngrest;

use Yii;

class ConfigBuilderTest extends \tests\BaseWebTest
{
    
    private function getConfig()
    {
        $config = new \admin\ngrest\ConfigBuilder();
        
        $config->list->field('create_var_1', 'testlabel in list')->text();
        $config->list->field('list_var_1', 'testlabel')->textarea();
        $config->list->field('list_var_2', 'testlabel')->textarea();
        
        $config->create->field('create_var_1', 'testlabel')->text();
        $config->create->extraField('create_extra_var_2', 'extratestlabel')->text();
        
        $config->update->copyFrom('list', ['textvar2']);
        
        return $config->getConfig();
    }
    
    public function testNgRestConfigBuilder()
    {
        $array = $this->getConfig();
        
        $this->assertArrayHasKey('update', $array);
        $this->assertArrayNotHasKey('delete', $array);
        $this->assertArrayHasKey('list', $array);
        $this->assertArrayHasKey('create', $array);
        
        $list = $array['list'];
        $create = $array['create'];
        
        $this->assertArrayHasKey('list_var_1', $list);
        $this->assertArrayHasKey('create_var_1', $create);
        
        $testvar = $list['list_var_1'];
        
        $this->assertArrayHasKey('name', $testvar);
        $this->assertArrayHasKey('gridCols', $testvar);
        $this->assertArrayHasKey('alias', $testvar);
        $this->assertArrayHasKey('plugins', $testvar);
        $this->assertArrayHasKey('i18n', $testvar);
        $this->assertArrayHasKey('extraField', $testvar);
        
        $plugins = $testvar['plugins'];
        
        $this->assertArrayHasKey(0, $plugins);
        $this->assertArrayHasKey("class", $plugins[0]);
        $this->assertArrayHasKey("args", $plugins[0]);
        // check if args
        $this->assertEquals('\admin\ngrest\plugins\Textarea', $plugins[0]['class']);
        // text extraField = 1
        $this->assertEquals(1, $create['create_extra_var_2']['extraField']);
    }
    
    public function testNgRestConfigAW()
    {
        $config = new \admin\ngrest\ConfigBuilder();
        $config->aw->register(new \admin\aws\ChangePassword(), 'Change Password');
        $cfg = $config->getConfig();
        
        $this->assertArrayHasKey('aw', $cfg);
        $aw = $cfg['aw'];
        
        $this->assertArrayHasKey('a4935e3b2248d9c6667a02faf4b0966e35333a92', $aw);
        $obj = $aw['a4935e3b2248d9c6667a02faf4b0966e35333a92'];
        
        $this->assertArrayHasKey('object', $obj);
        $this->assertArrayHasKey('activeWindowHash', $obj);
        $this->assertArrayHasKey('class', $obj);
        $this->assertArrayHasKey('alias', $obj);
        
        $this->assertEquals('a4935e3b2248d9c6667a02faf4b0966e35333a92', $obj['activeWindowHash']);
        
        $ngRestConfig = new \admin\ngrest\Config(['apiEndpoint' => 'api-admin-test', 'primaryKey' => 'id']);
        $ngRestConfig->setConfig($cfg);
        
        
    }
    
    public function testNgRestConfigPlugins()
    {
        $configData = $this->getConfig();
        $ngRest = new \admin\ngrest\Config(['apiEndpoint' => 'api-admin-test', 'primaryKey' => 'id']);
        $ngRest->setConfig($configData);
        $plugins = $ngRest->getPlugins();

        $this->assertEquals(4, count($plugins));
        $this->assertEquals(1, count($plugins['create_var_1']));
        
        $plugins = $ngRest->plugins;
        
        $this->assertEquals(4, count($plugins));
        $this->assertEquals(1, count($plugins['create_var_1']));
    }
    
    public function testNgRestConfigExtraFields()
    {
        $configData = $this->getConfig();
        $ngRest = new \admin\ngrest\Config(['apiEndpoint' => 'api-admin-test', 'primaryKey' => 'id']);
        $ngRest->setConfig($configData);
        
        $fields = $ngRest->getExtraFields();
        $this->assertEquals(1, count($fields));
        $this->assertEquals("create_extra_var_2", $fields[0]);
        
        $fields = $ngRest->extraFields;
        $this->assertEquals(1, count($fields));
        $this->assertEquals("create_extra_var_2", $fields[0]);
    }
    
    public function testNgRestConfigAppendFieldOption()
    {
        $configData = $this->getConfig();
        $ngRest = new \admin\ngrest\Config(['apiEndpoint' => 'api-admin-test', 'primaryKey' => 'id']);
        $ngRest->setConfig($configData);
    
        $ngRest->appendFieldOption('list_var_1', 'i18n', true);
        $field = $ngRest->getField('list', 'list_var_1');
        $this->assertEquals(true, $field['i18n']);
        $field = $ngRest->getField('list', 'list_var_2');
        $this->assertEquals(false, $field['i18n']);
    }
    
    public function testNgRestConfig()
    {
        $configData = $this->getConfig();
        
        $ngRestConfig = Yii::createObject(['class' => '\admin\ngrest\Config', 'apiEndpoint' => 'api-admin-test']);
        //$ngRestConfig = new \admin\ngrest\Config('api-admin-test', 'id');
        $ngRestConfig->setConfig($configData);
        
        $this->assertEquals(true, $ngRestConfig->hasPointer('list'));
        $this->assertEquals(true, $ngRestConfig->hasPointer('create'));
        $this->assertEquals(true, $ngRestConfig->hasPointer('update'));
        $this->assertEquals(false, $ngRestConfig->hasPointer('delete'));
        $this->assertEquals(false, $ngRestConfig->hasPointer('aw'));
        
        $this->assertEquals(true, $ngRestConfig->hasField('list', 'create_var_1'));
        $this->assertEquals(false, $ngRestConfig->hasField('list', 'id'));
        
        $this->assertEquals(false, $ngRestConfig->isDeletable());
        
        $ngRestConfig->addField('list', 'foo', [
            'name' => 'foo',
            'alias' => 'ID',
            'plugins' => [
                [
                    'class' => '\admin\ngrest\plugins\Text',
                    'args' => [],
                ]
            ]
        ]);
        $this->assertEquals(true, $ngRestConfig->hasField('list', 'foo'));
    }
}   