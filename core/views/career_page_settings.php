<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Careers Settings</h2>
	
	<?php
	if ( $this->settings_saved ) :
	?>
	
	<div id="message" class="updated">
		<p>Careers settings saved</p>
	</div>
	
	<?php
	endif;
	?>
	
	<form method="post">
		<?php wp_nonce_field('save_settings', '_emnonce'); ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="apply-page">Apply Page</label>
					</th>
					<td>
						<?php
						new Em_Field(array(
							'name' => 'apply_page',
							'type' => 'page_dropdown'
						));
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="parent-page">Parent Page</label>
					</th>
					<td>
						<?php
						new Em_Field(array(
							'name' => 'parent_page',
							'type' => 'page_dropdown'
						));
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="summary_template">Summary Page Template</label>
					</th>
					<td>
						<?php
						new Em_Field(array(
							'name' => 'summary_template',
							'type' => 'textarea',
							'class' => 'large-text code',
							'default_val' => file_get_contents($this->base_dir . 'core/includes/tmpl-summary.htm'),
						));
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="detail_template">Detail Template</label>
					</th>
					<td>
						<?php
						new Em_Field(array(
							'name' => 'detail_template',
							'type' => 'textarea',
							'class' => 'large-text code',
							'default_val' => file_get_contents($this->base_dir . 'core/includes/tmpl-detail.htm'),
						));
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input id="submit" class="button-primary" type="submit" value="Save Changes" name="submit" />
		</p>
	</form>
</div>