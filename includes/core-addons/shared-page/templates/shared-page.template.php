<?php
?>
    <div>
        <form action="options.php" method="post">
            <?php settings_fields('pffw_shared_page_options'); ?>
            <?php do_settings_sections('shared-page-options'); ?>

            <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form>
    </div>
    <?php





