<?php

class odataRestController extends ezcMvcController
{

    public function doFoo()
    {
        $res = new ezcMvcResult();
        $res->variables['message'] = "This is FOO!";
        return $res;
    }

    public function doFooBar()
    {
        $res = new ezcMvcResult();
        $res->variables['message'] = "This is FOOBAR!";
        return $res;
    }
}