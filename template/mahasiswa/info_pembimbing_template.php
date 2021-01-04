<h2><?php $this->echoData('page_title'); ?></h2>
<p><?php $this->echoData('page_description'); ?></p>
<br/>
<?php if(($this->data('error_message')) !== null) { ?>
    <p class="error-message-label"><?php echo $this->data('error_message'); ?></p>
<?php } ?>
<?php if(!empty($this->data('data_pembimbing_1'))) { ?>
    <h2>Pembimbing-1</h2>
    <table class="data-table">
        <tbody>
        <?php foreach ($this->data('data_pembimbing_1') as $row) { ?>
            <tr>
                <td style="width: 40%;" class="data-table-td data-display-caption"><?php echo $row['caption']; ?></td>
                <td style="width: auto;" class="data-table-td data-display-content"><?php echo $row['content']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
<?php if(!empty($this->data('data_pembimbing_2'))) { ?>
    <h2>Pembimbing-2</h2>
    <table class="data-table">
        <tbody>
        <?php foreach ($this->data('data_pembimbing_2') as $row) { ?>
            <tr>
                <td style="width: 40%;" class="data-table-td data-display-caption"><?php echo $row['caption']; ?></td>
                <td style="width: auto;" class="data-table-td data-display-content"><?php echo $row['content']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <br />
    <strong>Perhatian:</strong> Anda belum punya dosen pembimbing-2. Segera tuntaskan proposal Anda dan lapor ke panitia!
<?php } ?>
