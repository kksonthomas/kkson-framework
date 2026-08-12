<?php
use KKsonFramework\CRUD\KKsonCRUD;

/** @var KKsonCRUD $crud */
/** @var string $layoutName*/

$crud->addBodyEndHTML(<<< HTML
<script>

</script>
HTML
);

$this->layout($layoutName, [
    "crud" => $crud
]);

?>
<div class="alert alert-danger" style="margin-top: 40px; border-radius: 12px; border-width: 2px; box-shadow: 0 2px 18px rgba(220,53,69,0.07); padding: 32px 24px; text-align: center;">
    <div style="font-size: 3.5rem; margin-bottom: 20px;">
        <i class="fa fa-ban" aria-hidden="true" style="color:rgb(255, 255, 255);"></i>
    </div>
    <h2 style="font-weight: bold; letter-spacing: 2px; background-color: #dc3545; color: #fff; margin-bottom: 14px;">權限不足</h2>
    <p style="font-size: 1.2rem; background-color: #dc3545; color: #fff;">你的用戶權限不足，未能訪問此頁面。</p>
    <a href="javascript:window.history.back()" class="btn btn-default" style="color:rgb(0, 0, 0);">
        <i class="fa fa-arrow-left" style="margin-right:4px;color:rgb(0, 0, 0);"></i> 返回上一頁
    </a>
</div>




