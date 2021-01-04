<?php if(($this->data('action_links')) !== null) { ?>
    <?php foreach(($this->data('action_links')) as $actionLink) { ?>
        <?php if($actionLink instanceof \lib\ActionLink) { ?>
            <a class="<?php echo $actionLink->getCssClass(); ?>" href="<?php echo $actionLink->fullActionUrl(); ?>"><?php echo $actionLink->getCaption(); ?></a>
        <?php } else { ?>
            <a class="default-action-button" href="<?php echo $actionLink['url']; ?>"><?php echo $actionLink['caption']; ?></a>
        <?php } ?>
    <?php } ?>
<?php } ?>