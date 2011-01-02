<?php

class OperationSQLTest extends PHPUnit_Framework_TestCase {

    /**
     * @var OperationSQL
     */
    protected $object;

    /**
     * @todo Implement testFind().
     */
    public function testCRU() {
        $object=OperationSQL::find(array());
        $this->assertInstanceOf("DibiRow",$object);
    }

    /**
     * @todo Implement testCreate().
     */
    public function testCreate() {
        $object = OperationSQL::create(array(
                    "met_id" => @reset(Operation::find())->met_id,
                    "rev_id" => @reset(Revision::find())->rev_id,
                    "sql" => 'SELECT xx FROM yy',
                    "assocKey" => 'x'
                ));
        $this->assertInstanceOf('OperationSQL', $object);
    }

    public function testValidate() {
        try {
            $object = OperationSQL::create(array(
                        "met_id" => @reset(Operation::find())->met_id,
                        "rev_id" => @reset(Revision::find())->rev_id,
                        "sql" => 'INSERT xx INTO yy',
                        "assocKey" => 'x'
                    ));
            $this->fail();
        } catch (InvalidArgumentException $e) {
            
        }
        try {
            $object = OperationSQL::create(array(
                        "met_id" => @reset(Operation::find())->met_id,
                        "rev_id" => @reset(Revision::find())->rev_id,
                        "sql" => 'DELETE xx INTO yy',
                        "assocKey" => 'x'
                    ));
            $this->fail();
        } catch (InvalidArgumentException $e) {

        }
        try {
            $object = OperationSQL::create(array(
                        "met_id" => @reset(Operation::find())->met_id,
                        "rev_id" => @reset(Revision::find())->rev_id,
                        "sql" => 'SELECT xx INTO :main:yy',
                        "assocKey" => 'x'
                    ));
            $this->fail();
        } catch (InvalidArgumentException $e) {

        }
    }


}

?>
