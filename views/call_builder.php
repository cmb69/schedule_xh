<?php

use Plib\View;

/**
 * @var View $this
 * @var string $js
 * @var string $show_totals
 * @var string $read_only
 * @var string $multi
 */
?>

<script type="module" src="<?=$this->esc($js)?>"></script>
<h1>Schedule – <?=$this->text('menu_main')?></h1>
<form class="schedule_call_builder">
  <p>
    <label>
      <span><?=$this->text('label_name')?></span>
      <input name="name" required pattern="[a-z0-9\-]+">
    </label>
  </p>
  <p>
    <label>
      <span><?=$this->text('label_show_totals')?></span>
      <input name="show_totals" type="checkbox" <?=$this->esc($show_totals)?>>
    </label>
  </p>
  <p>
    <label>
      <span><?=$this->text('label_read_only')?></span>
      <input name="read_only" type="checkbox" <?=$this->esc($read_only)?>>
    </label>
  </p>
  <p>
    <label>
      <span><?=$this->text('label_multi')?></span>
      <input name="multi" type="checkbox" <?=$this->esc($multi)?>>
    </label>
  </p>
  <p>
    <label>
      <span><?=$this->text('label_options')?></span>
      <textarea name="options" required></textarea>
    </label>
  </p>
  <p class="schedule_buttons">
    <button type="button" name="parse"><?=$this->text('label_parse')?></button>
    <button type="button" name="build"><?=$this->text('label_build')?></button>
  </p>
  <p>
    <label>
      <span><?=$this->text('label_plugin_call')?></span>
      <textarea name="plugin_call"></textarea>
    </label>
  </p>
</form>
