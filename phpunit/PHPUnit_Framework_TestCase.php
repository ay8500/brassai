<?php

class PHPUnit_Framework_TestCase {

    private $assertOk=0;
    private $assertError=0;
    private $errorText="";

    public function assertSame($o1,$o2) {
        if ($o1===$o2) {
            $this->assertOk++;
        } else {
            $this->assertError++;
            if (get_class($o1)===get_class($o2)) {
                $this->errorText .= ' Expected:' . $o1 . ' Actual:' . $o2;
            } else {
                $this->errorText .= ' Object not mach: Expected:' . get_class($o1) . ' Actual:' . get_class($o2);
            }
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
        $ret->errorText = $this->errorText;
        return $ret;
    }


}

