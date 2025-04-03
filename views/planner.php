<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.1 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var bool $show_totals
 * @var ?string $voting
 * @var string $url
 * @var array<string> $options
 * @var list<int> $totals
 * @var array<string,list<array{class:string,content:string}>> $users
 * @var string $button
 * @var int $columns
 * @var string $csrf_token
 */
?>
<!-- schedule planner -->
<?if ($voting):?>
<form class="schedule" action="<?=$this->esc($url)?>" method="POST">
<?else:?>
<div class="schedule">
<?endif?>
  <table class="schedule">
    <thead>
      <tr>
        <th></th>
<?foreach ($options as $option):?>
        <th><?=$this->esc($option)?></th>
<?endforeach?>
      </tr>
    </thead>
    <tbody>
<?foreach ($users as $user => $cells):?>
      <tr>
        <td class="schedule_user"><?=$this->esc($user)?></td>
<?  foreach ($cells as $cell):?>
        <td class="<?=$this->esc($cell['class'])?>"><?=$this->raw($cell['content'])?></td>
<?  endforeach?>
      </tr>
<?endforeach?>
<?if ($show_totals):?>
      <tr class="schedule_total">
        <td class="schedule_user"><?=$this->text("total")?></td>
<?  foreach ($totals as $total):?>
        <td><?=$total?></td>
<?  endforeach?>
      </tr>
<?endif?>
<?if ($voting):?>
      <tr class="schedule_buttons">
        <td colspan="<?=$columns?>">
          <button name="<?=$this->esc($button)?>" value="vote"><?=$this->text("label_save")?></button>
        </td>
      </tr>
<?endif?>
    </tbody>
  </table>
<?if ($voting):?>
  <input type="hidden" name="schedule_token" value="<?=$this->esc($csrf_token)?>">
</form>
<?else:?>
</div>
<?endif?>
