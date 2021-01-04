<h2><?php $this->echoData('page_title'); ?></h2>
<p><?php $this->echoData('page_description'); ?></p>
<br/>
<p class="error-message-label"><?php echo $this->data('error_message'); ?></p>
<?php $backLink = $this->data('back_link'); ?>
<?php if($backLink != null) { ?>
    <a href="<?php echo $backLink; ?>"><< Kembali</a>
    <p/>
<?php } ?>
<?php $form = $this->data('form'); ?>
<?php if($this->data('hide_forms') == true || $form == null) return; ?>
<?php echo $form->renderOpen(); ?>
<?php echo $form->renderHiddenInputs(); ?>
<table class="data-table">
    <tbody>
    <?php foreach ($form->getInputs(true) as $input) { ?>
        <tr>
            <?php if($input->getType() != \m\extended\Input::TYPE_NONE) {?>
                <td style="width: 40%;" class="data-table-td data-display-caption"><?php echo $input->renderLabel(); ?></td>
                <td style="width: auto;" class="data-table-td data-display-content"><?php echo $input->renderControl('style="width: 100%; height: 28px;"'); ?></td>
            <?php } else { ?>
                <td colspan="2" style="text-decoration: underline; width: 40%;color: #ffffff; background-color: #a2a2a3;" class="data-table-td data-display-caption">
                    <?php echo $input->renderLabel(); ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
        <tr>
            <td class="data-table-td data-display-caption">&nbsp;</td>
            <td class="data-table-td data-display-content">
                <?php echo $form->renderActionButtons(); ?>
                <?php echo $form->renderSubmit('class="form-submit-button"'); ?>
            </td>
        </tr>
    </tbody>
</table>
<?php echo $form->renderClose(); ?>
