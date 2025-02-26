<?php
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Field;

/** @var Field[] $fields */
/** @var array $list */
/** @var KKsonCRUD $crud */
/** @var string $layoutName*/

/** @var string $title */
/** @var string $msg */

$this->layout($layoutName, [
    "crud" => $crud
]);
?>

    <div class="row">

        <div class="col-md-6">

            <div class="card card-primary">
                <div class="card-header with-border">
                    <h3 class="card-title"><?=$title?></h3>
                </div>
                    <div class="card-body">
                        <?=$msg ?>
                    </div>

                    <div class="card-footer">

                    </div>
            </div>
        </div>
    </div>
