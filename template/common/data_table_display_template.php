<h2><?php $this->echoData('page_title'); ?></h2>
<p><?php $this->echoData('page_description'); ?></p>
<?php $backLink = $this->data('back_link'); ?>
<?php if($backLink != null) { ?>
    <a href="<?php echo $backLink; ?>"><< Back</a>
<?php } ?>
<br/>
<p class="error-message-label"><?php echo $this->data('error_message'); ?></p>
<?php include_once 'template/common/fragment/data_filter_fragment.php'; ?>
<?php include_once 'template/common/fragment/table_display_fragment.php'; ?>
<br/>
<?php include_once 'template/common/fragment/bottom_action_links_fragment.php'; ?>
