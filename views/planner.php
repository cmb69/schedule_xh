<?php
/**
 * @var bool $showTotals
 * @var array<string,string> $ptx
 * @var ?string $currentUser
 * @var string $url
 * @var array<string> $options
 * @var array<string,int> $counts
 * @var array<string,array<string>> $users
 * @var array<string,array<string,string>> $cells
 * @var string $submit
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
        <td class="<?=$class?>"><?=$cells[$user][$option]?></td>
<?php   endforeach?>
      </tr>
<?php endforeach?>

<?php if ($showTotals):?>
      <tr class="schedule_total">
        <td class="schedule_user"><?=$ptx['total']?></td>
<?php   foreach ($counts as $count):?>
        <td><?=$count?></td>
<?php   endforeach?>
      </tr>
<?php endif?>

<?php if ($currentUser):?>
      <tr class="schedule_buttons">
        <td colspan="<?=$columns?>"><?=$submit?></td>
      </tr>
<?php endif?>
    </tbody>
  </table>
<?php if ($currentUser):?>
</form>
<?php else:?>
</div>
<?php endif?>
