<?php
/** @var array $menuItems */
foreach($menuItems as $item) :
    ?>
    <li class="nav-item"><a href="<?=$item["url"] ?>" class="nav-link"><i class="nav-icon fa fa-circle"></i><p><?=$item["name"] ?></p></a></li>
<?php
endforeach;
?>
