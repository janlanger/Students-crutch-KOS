<?php



/**
 * Test class for UsersModel.
 * Generated by PHPUnit on 2011-01-02 at 21:04:17.
 */
class UsersModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var UsersModel
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new UsersModel;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testAuthenticate().
     */
    public function testAuthenticate()
    {

        try{
            $this->object->authenticate(array());
            $this->fail();
        } catch (\Nette\Security\AuthenticationException $e) {
            $this->assertEquals(UsersModel::IDENTITY_NOT_FOUND,$e->getCode());
        }

        try{
            $this->object->authenticate(array(0=>'test',1=>''));
            $this->fail();
        } catch (\Nette\Security\AuthenticationException $e) {
            $this->assertEquals(UsersModel::INVALID_CREDENTIAL,$e->getCode());
        }
    }
}
?>