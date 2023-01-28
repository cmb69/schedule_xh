<?php

use Schedule\View;

if (!defined("CMSIMPLE_XH_VERSION")) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

/**
 * @var View $this
 * @var string $version
 * @var array<array{string,string}> $checks
 */
?>

<h1>Schedule <?=$this->esc($version)?></h1>
<h2><?=$this->text("syscheck_title")?></h2>
<?php foreach ($checks as [$text, $class]):?>
<p class="<?=$this->esc($class)?>"><?=$this->esc($text)?></p>
<?php endforeach?>
