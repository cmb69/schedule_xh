<?php

namespace Schedule;

if (!defined("CMSIMPLE_XH_VERSION")) {
  header("HTTP/1.1 403 Forbidden");
  exit;
}

/**
 * @var View $this
 * @var bool $showTotals
 * @var ?string $currentUser
 * @var string $url
 * @var array<string> $options
 * @var array<string,int> $counts
 * @var array<string,array<string>> $users
 * @var string $itype
 * @var string $iname
 * @var string $sname
 * @var int $columns
 */
?>
<!-- Schedule_XH planner -->
<?php if ($currentUser):?>
<form class="schedule" action="<?=$url?>" method="POST">
<?php else:?>
<div class="schedule">
<?php endif?>
  <table class="schedule">
    <thead>
      <tr>
        <th></th>
<?php foreach ($options as $option):?>
        <th><?=$option?></th>
<?php endforeach?>
      </tr>
    </thead>
    <tbody>
<?php foreach ($users as $user => $votes):?>
      <tr>
        <td class="schedule_user"><?=$user?></td>
<?php   foreach ($votes as $option => $class):?>
        <td class="<?=$class?>">
<?php     if ($user === $currentUser):?>
<?php       if ($class === "schedule_green"):?>
          <input type="<?=$itype?>" name="<?=$iname?>[]" value="<?=$option?>" checked>
<?php       else:?>
          <input type="<?=$itype?>" name="<?=$iname?>[]" value="<?=$option?>">
<?php       endif?>
<?php     elseif ($class === "schedule_green"):?>
            ✓
<?php     endif?>
        </td>
<?php   endforeach?>
      </tr>
<?php endforeach?>

<?php if ($showTotals):?>
      <tr class="schedule_total">
        <td class="schedule_user"><?=$this->text("total")?></td>
<?php   foreach ($counts as $count):?>
        <td><?=$count?></td>
<?php   endforeach?>
      </tr>
<?php endif?>

<?php if ($currentUser):?>
      <tr class="schedule_buttons">
        <td colspan="<?=$columns?>">
          <input type="submit" class="submit" name="<?=$sname?>" value="<?=$this->text("label_save")?>">
        </td>
      </tr>
<?php endif?>
    </tbody>
  </table>
<?php if ($currentUser):?>
</form>
<?php else:?>
</div>
<?php endif?>
