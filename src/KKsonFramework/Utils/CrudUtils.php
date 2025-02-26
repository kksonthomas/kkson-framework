<?php

namespace KKsonFramework\Utils;


class CrudUtils
{
    public static function checkParams($array, $paramNames) {
        foreach ($paramNames as $name) {
            if(!isset($array[$name])) {
                throw new \Exception("$name parameter is missing");
            }
        }
    }

    public static function processDataTableAjaxOrderBy($data, $defaultOrderBy = "id", $defaultOrderDir = "asc")
    {
        if(isset($data['order'])) {
            $name = $data["columns"][$data["order"][0]["column"]]["data"];
            $dir = $data["order"][0]["dir"];

            $data["orderBy"] = $name;
            $data["orderDir"] = $dir;
        } else {
            $data["orderBy"] = $defaultOrderBy;
            $data["orderDir"] = $defaultOrderDir;
        }

        return $data;
    }
}
