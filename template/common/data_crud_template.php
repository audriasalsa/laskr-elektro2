<h2><?php $this->echoData('page_title'); ?></h2>
<p><?php $this->echoData('page_description'); ?></p>
<br/>
<p class="error-message-label"><?php echo $this->data('error_message'); ?></p>
<?php $form = $this->data('form'); ?>
<?php if($form != null) { ?>
    <?php echo $form->renderOpen(); ?>
    <?php echo $form->renderHiddenInputs(); ?>
    <table class="data-table">
        <tbody>
        <?php foreach ($form->getInputs(true) as $input) { ?>
            <tr>
                <td style="width: 40%;" class="data-table-td data-display-caption"><?php echo $input->renderLabel(); ?></td>
                <td style="width: auto;" class="data-table-td data-display-content"><?php echo $input->renderControl('style="width: 100%; height: 28px;"'); ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td class="data-table-td data-display-caption">&nbsp;</td>
            <td class="data-table-td data-display-content"><?php echo $form->renderSubmit('class="form-submit-button"'); ?></td>
        </tr>
        </tbody>
    </table>
    <?php echo $form->renderClose(); ?>
<?php } ?>
<?php include_once 'template/common/fragment/table_display_fragment.php'; ?>