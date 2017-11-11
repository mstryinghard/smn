<style media="screen">
  label.block {
    display: block;
  }
</style>
<div class="smu-settings">
  <?=form_open($action_url);?>
  <?=ee('CP/Alert')->get('smu-settings-form')?>
  <?php

    $this->table->set_template($cp_pad_table_template);
    $this->table->set_heading(
        array('data' => lang('preference'), 'style' => 'width:20%;'),
        lang('setting')
    );

    foreach ($settings as $key => $val)
    {
        $this->table->add_row(lang($key, $key), $val);
    }

    echo $this->table->generate();

  ?>

  <?php

    foreach ($platforms as $key => $value) {
      $this->table->clear();
      $this->table->set_heading(
          array('data' => lang($key), 'style' => 'width:20%;'),
          array('data' => '')
      );

      foreach ($value['accounts'] as $inner_key => $inner_value) {
        if(!empty($inner_value)) {
          $this->table->add_row(lang($inner_key, $inner_key), $inner_value);
        }
      }

      echo $this->table->generate();
    }


  ?>
  <p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
  <?php $this->table->clear()?>
  <?=form_close()?>
</div>
<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/link_truncator/views/index.php */
