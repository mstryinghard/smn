<?php if($enabled): ?>
<style>
  .smu {
    margin: 15px 0;
    border: 1px solid #8195a0;
    border-radius: 3px;
    background-color: #fff;
  }

  .smu h5 {
    font-size: 14px;
    background-color: #8195a0;
    color: #fff;
    padding: 5px;
    margin: 0 0 15px 0;
  }

  .smu .form-group {
    padding: 0 15px;
    margin: 0 0 15px 0;
  }

  .smu .form-group:after {
    display: table;
    content: " ";
    clear: both;
  }

  .smu .form-group .help-text {
    float: left;
    margin-top: 5px;
    font-style: italic;
  }

  .smu .form-group .char-limit {
    float: right;
    margin-top: 5px;
    font-size: 11px;
  }

  .smu .form-group select {
    width: 200px;
  }

  .smu .hidden { display: none; }
</style>

<?php
  $repost = false;
  $update_url = '';
 
  if(!empty($smu) && isset(unserialize($smu->response)->updateKey)) {
    $repost = true;
    $update_url = unserialize($smu->response)->updateUrl;
  }
?>
<div class="smu">
  <h5>LinkedIn</h5>

  <div class="form-group">
    <?php echo form_label('Content source', 'smu_content_source'); ?>
    <?php echo form_dropdown('smu[content_source]', array('title' => 'Title', 'custom' => 'Custom'), (isset($data['content_source'])) ? $data['content_source'] : 'title', 'id="smu_content_source"'); ?>
  </div>

  <div class="form-group">
    <?php echo form_label('Content to post', 'smu_content'); ?>
    <?php echo form_textarea(array('name' => 'smu[content]', 'id' => 'smu_content', 'value' => (isset($data['content'])) ? $data['content'] : '', 'rows' => 5, 'placeholder' => 'Enter content to post here...')); ?>
    <span class="notes"></span>
  </div>

  <div class="form-group">
    <?php echo form_label('Share URL (Optional)', 'smu_url'); ?>
    <?php echo form_input(array('name' => 'smu[url]', 'id' => 'smu_url', 'value' => ((isset($data['url'])) ? $data['url'] : ''), 'placeholder' => 'Enter URL here...')); ?>
    <span class="help-text">Adding a URL will override the current default share URL.</span>
    <span class="char-limit"><span id="linkedin-char-limit">700</span> characters left for LinkedIn.</span>
  </div>

  <?php if($repost): ?>
    <div class="form-group">
      <?php echo form_label(form_checkbox(array('name' => 'smu[repost]', 'id' => 'smu_repost', 'value' => 'yes')).' <strong>This was already shared, click <a href="'.$update_url.'" target="_blank">here</a> to view the shared content. Would you like to reshare it?</strong>', 'smu_repost'); ?>
    </div>
  <?php endif; ?>

  <?php echo form_textarea(array('name' => $field_name, 'id' => $field_id, 'class' => 'hidden', 'value' => $field_data)); ?>

</div>

<script type="text/javascript">
  (function($) {
    var field = '[name="<?php echo $field_name; ?>"]';
    var smu = {
      encode: function() {
        var values = [];
        values.push('content<?php echo $key; ?>' + $('#smu_content').val());
        values.push('url<?php echo $key; ?>' + $('#smu_url').val());
        values.push('content_source<?php echo $key; ?>' + $('#smu_content_source').val());
        smu.linkedin_cc();
        return values.join('<?php echo $delimiter; ?>');
      },

      linkedin_cc: function() {
        var limit = 700;
        var count = $('#smu_content').val().length + 1 + $('#smu_url').val().length;
        $('#linkedin-char-limit').html((limit - count));
      },

      load_content_source: function() {
        $('#smu_content').val(($('#smu_content_source').val() == 'title') ? $('#smu_content_source').closest('form').find('input[name="title"]').val() : '');
        $(field).val(smu.encode());
      }
    };

    $('#smu_content_source').closest('form').find('input[name="title"]').on('keyup', function() {
      smu.load_content_source();
    });

    $('#smu_content_source').on('change', function() {
      smu.load_content_source();
    });

    $('#smu_content, #smu_url').on('keyup', function() {
      $(field).val(smu.encode());
    });

    $('#smu_content').on('keyup', function() {
      smu.linkedin_cc();
      $('#smu_content_source').val(($(this).closest('form').find('input[name="title"]').val() != $(this).val()) ? 'custom' : 'title');
    });

    $('#smu_content, #smu_url').on('change', function(e) {
      $(field).val(smu.encode());
    });

    smu.load_content_source();
    smu.linkedin_cc();
  })(jQuery);
</script>
<?php else: ?>
  <div class="smu">
    <p class="warning">
      No LinkedIn settings yet has been configured, click <a href="<?php echo (isset($urls->module_cp_url)) ? $urls->module_cp_url : '#'; ?>">here</a> to configure one now.
    </p>
  </div>
<?php endif; ?>
