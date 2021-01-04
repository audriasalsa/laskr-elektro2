<?php $headers = $this->data('headers'); ?>
<?php $displayedData = $this->data('displayed_data'); ?>
<?php if($headers != null && $displayedData != null) { ?>
    <?php if($this->data('hide_table') == true) return; ?>
    <div id="table_container" style="width: 99%; overflow: scroll; border: 1px solid black; padding: 4px;">
        <table class="sortable data-table">
            <thead>
            <?php foreach ($headers as $header) { ?>
                <th class="sortable-th data-display-caption"><?php echo $header; ?></th>
            <?php } ?>
            </thead>
            <tbody>
            <?php foreach ($displayedData as $row) { ?>
                <tr>
                    <?php foreach ($row as $column => $value) { ?>
                        <td class="data-table-td data-display-content"><?php echo $value; ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } else { ?>
    <h4 style="font-style: italic; color: #5a5a5a;">
        <?php $emptyDataMessage = $this->data('empty_data_message'); ?>
        <?php if($emptyDataMessage != null) { ?>
            <?php echo $emptyDataMessage; ?>
        <?php } else { ?>
            <?php $this->echoData('no_data_caption', 'No record in the database'); ?>
        <?php } ?>
    </h4>
<?php } ?>