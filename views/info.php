<?php

use Schedule\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

/**
 * @var View $this
 * @var string $version
 * @var list<array{key:string,arg:string,class:string}> $checks
 */
?>

<h1>Schedule <?=$this->esc($version)?></h1>
<h2><?=$this->text("syscheck_title")?></h2>
<?php foreach ($checks as $check):?>
<p class="<?=$this->esc($check['class'])?>"><?=$this->text($check['key'], $check['arg'])?></p>
<?php endforeach?>
