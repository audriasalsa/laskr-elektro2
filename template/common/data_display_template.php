<h2><?php $this->echoData('page_title'); ?></h2>
<p><?php $this->echoData('page_description'); ?></p>
<br/>
<?php $backLink = $this->data('back_link'); ?>
<?php if($backLink != null) { ?>
    <a href="<?php echo $backLink; ?>"><< Kembali</a>
    <p/>
<?php } ?>
<table class="data-table">
    <tbody>
    <?php if(($this->data('success_message')) !== null) { ?>
        <p class="success-message-label"><?php echo $this->data('success_message'); ?></p>
    <?php } ?>
    <?php if(($this->data('error_message')) !== null) { ?>
        <p class="error-message-label"><?php echo $this->data('error_message'); ?></p>
    <?php } else { ?>
        <?php if($this->data('displayed_data') == null) { ?>
            <p class="error-message-label">No data found..</p>
        <?php } else { ?>
            <?php foreach ($this->data('displayed_data') as $row) { ?>
                <tr>
                    <td style="width: 40%;" class="data-table-td data-display-caption"><?php echo $row['caption']; ?></td>
                    <td style="width: auto;" class="data-table-td data-display-content"><?php echo $row['content']; ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    </tbody>
</table>
<br/>
<?php if(($this->data('action_links')) !== null) { ?>
    <?php foreach(($this->data('action_links')) as $actionLink) { ?>
        <?php if($actionLink instanceof \lib\ActionLink) { ?>
                <a class="<?php echo $actionLink->getCssClass(); ?>" href="<?php echo $actionLink->fullActionUrl(); ?>"><?php echo $actionLink->getCaption(); ?></a>
            <?php } else { ?>
                <a class="default-action-button" href="<?php echo $actionLink['url']; ?>"><?php echo $actionLink['caption']; ?></a>
            <?php } ?>
    <?php } ?>
<?php } ?>