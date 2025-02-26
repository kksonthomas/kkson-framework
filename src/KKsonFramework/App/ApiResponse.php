<?php
namespace KKsonFramework\App;

class ApiResponse extends \stdClass
{
    /**
     * @param \Exception $ex
     * @return ApiResponse
     */
    public static function createFromException($ex) {
        $response = new ApiResponse();
        $response->status = false;
        $response->error = $ex->getMessage();
        return $response;
    }
    public function __construct()
    {
        $this->status = true;
    }

    public function setStatus($b) {
        $this->status = !!$b;
    }

    public function toJSON() {
        return json_encode($this);
    }

    public function __toString()
    {
        return $this->toJSON();
    }
}