<?php $filterForm = $this->data('filter_form'); ?>
<?php if($filterForm != null) { ?>
    <?php echo $filterForm->renderOpen(); ?>
    <table class="data-table">
        <thead class="data-table-td data-display-content">
        <p>Filter berdasrkan:</p>
        </thead>
        <tbody>
        <tr>
            <?php foreach ($filterForm->getInputs(true) as $input) { ?>
                <td class="data-table-td data-display-content">
                    <?php echo $input->renderLabel(); ?>
                    <br/>
                    <?php echo $input->renderControl('style="width: 100%; height: 28px;"'); ?>
                </td>
            <?php } ?>
            <td class="data-table-td data-display-content">
                <?php echo $filterForm->renderSubmit('class="form-submit-button"'); ?>
            </td>
        </tr>
        </tbody>
    </table><br/>
    <?php echo $filterForm->renderClose(); ?>
<?php } ?>