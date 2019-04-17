<?php

class PHPUnit_Framework_TestCase {

    private $assertOk=0;
    private $assertError=0;

    public function assertSame($o1,$o2) {
        if ($o1==$o2) {
            $this->assertOk++;
        } else {
            $this->assertError++;
        }
    }

    public function assertTrue($boolean) {
        if ($boolean) {
            $this->assertOk++;
        } else {
            $this->assertError++;
        }
    }

    public function assertFalse($boolean) {
        $this->assertTrue(!$boolean);
    }

    public function assertNotNull($object) {
        if (null!==$object) {
            $this->assertOk++;
        } else {
            $this->assertError++;
        }
    }

    public function assertNull($object) {
        if (null===$object) {
            $this->assertOk++;
        } else {
            $this->assertError++;
        }
    }


    public function assertGetUnitTestResult() {
        $ret = new stdClass();
        $ret->testResult = $this->assertError==0;
        $ret->assertError = $this->assertError;
        $ret->assertOk = $this->assertOk;
        return $ret;
    }


}

