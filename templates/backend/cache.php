<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="pretix_widget_options" class="pretix-widget-admin-page-wrapper">
    <div id="header">
        <img width=128" src="<?php echo esc_url($this->get_url('assets/images/pretix-logo.svg')); ?>" />
        <h1>Widget Cache</h1>
    </div>
    <nav id="navigation"></nav>

    <div id="content">
        <?php
        // Check if the "Flush Cache" button is clicked
        if (is_admin() && isset($_POST['flush_cache'])) {
	        // Verify the nonce
	        if (isset($_POST['flush_cache_nonce_field']) && $this->_verify_nonce($_POST['flush_cache_nonce_field'], 'flush_cache_nonce')) {
		        // Nonce is valid, proceed with cache flushing
		        $response = $this->parent->cache->flush();
		        if ($response['status'] == 'success') {
			        echo '<div class="notice notice-success"><h2>' . esc_html($response['message']) . '</h2></div>';
		        } else {
			        echo '<div class="notice notice-error">';
			        echo '<h2>' . esc_html($response['message']) . '</h2>';
			        echo '<p>' . esc_html(implode('<br/>', $response['errors'])) . '</p>';
			        echo '<p><strong>' . esc_html__('Don\'t worry, the old files are still in the cache.', 'pretix-widget') . '</strong></p>';
			        echo '</div>';
		        }
	        } else {
		        // Nonce is not valid, handle it as desired (e.g., display an error message)
		        echo '<div class="notice notice-error"><h2>' . esc_html(__('Security check failed. Please try again.', 'pretix-widget')) . '</h2></div>';
	        }
        }

        // Check if the "Update" button is clicked
        if (is_admin() && isset($_POST['set_max_cache_time'])) {
	        if (isset($_POST['set_max_cache_time_nonce_field']) && $this->_verify_nonce(
			        $_POST['set_max_cache_time_nonce_field'],
			        'set_max_cache_time_nonce'
		        )) {
		        // Perform the cache time update
		        $cache_time = intval($this->_escape_request($_POST['set_max_cache_time']));
		        $response   = $this->parent->cache->set_max_cache_time($cache_time);

		        if ($response['status'] == 'success') {
			        echo '<div class="notice notice-success"><h2>' . esc_html($response['message']) . '</h2></div>';
		        } else {
			        echo '<div class="notice notice-error"><h2>' . esc_html($response['message']) . '</h2></div>';
		        }


	        } else {
		        // Nonce is not valid, handle it as desired (e.g., display an error message)
		        echo '<div class="notice notice-error"><h2>' . esc_html__(
				        'Security check failed. Please try again.',
				        'pretix-widget'
			        ) . '</h2></div>';
	        }
        }
        ?>
        <div class="flex full">
            <div class="left">
				<div class="container">
					<h2><?php esc_html_e('Cached Files', 'pretix-widget'); ?></h2>
					<?php foreach($this->parent->cache->get_files() as $file): ?>
						<div class="file">
							<div class="file-name"><?php echo esc_html($file['name']); ?></div>
							<div class="file-size"><?php echo esc_html($file['size']); ?></div>
							<div class="file-date"><?php echo esc_html($file['date']); ?></div>
						</div>
					<?php endforeach; ?>
					</div>
            </div>
            <div class="right">
                <div class="container">
	                <h2><?php esc_html_e('Set Max Cache Time', 'pretix-widget'); ?></h2>
	                <p>
                        <?php esc_html_e('You have the option to customize the maximum cache time for the pretix widget. By default, the cache time is set to 24 hours. Once this period elapses, the widget files will be automatically refreshed from the pretix server whenever a visitor accesses a page containing a pretix widget block or shortcode.', 'pretix-widget'); ?>
	                </p>

	                <form method="post" action="">
		                <label for="set_max_cache_time">
			                <strong><?php esc_html_e('Max cache time in hours', 'pretix-widget'); ?></strong>
		                </label>
		                <input type="number" name="set_max_cache_time" min="0" max="8760" value="<?php echo esc_html($this->parent->cache->get_max_cache_time());?>"/>
		                <?php wp_nonce_field('set_max_cache_time_nonce', 'set_max_cache_time_nonce_field'); ?>
		                <button type="submit" name="set_max_cache_time_submit" class="button button-primary"><?php esc_html_e('Update', 'pretix-widget');?></button>
	                </form>

	                <h2><?php esc_html_e('Cache', 'pretix-widget'); ?></h2>
	                <p>
                        <?php esc_html_e('Here you can delete all cached files at once and rebuild them. This feature is particularly useful if you have made changes to your pretix shop settings and want to ensure that the cached files are up-to-date.', 'pretix-widget'); ?>
	                </p>

	                <form method="post" action="">
		                <?php wp_nonce_field('flush_cache_nonce', 'flush_cache_nonce_field'); ?>
		                <button type="submit" name="flush_cache" class="button button-primary"><?php esc_html_e('Rebuild Cache', 'pretix-widget'); ?></button>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>
