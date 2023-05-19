<?php

namespace DynamicContentForElementor\AdminPages;

use DynamicContentForElementor\TemplateSystem as TemplateSystemEngine;
use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Assets;
class TemplateSystem
{
    private $options = [];
    public function __construct()
    {
        $this->options = get_option(DCE_TEMPLATE_SYSTEM_OPTION);
    }
    private function _dce_settings_select_template($dce_key, $templates)
    {
        ?>
	<span class="dce-template-select-wrapper">
		<a class="dce-template-quick-remove<?php 
        if (!isset($this->options[$dce_key]) || !$this->options[$dce_key]) {
            ?> hidden<?php 
        }
        ?>" target="_blank" href="#<?php 
        echo $dce_key;
        ?>"><span class="dashicons dashicons-no-alt"></span></a>
		<select class="dce-select-template dce-select js-dce-select" id="<?php 
        echo $dce_key;
        ?>" name="<?php 
        echo DCE_TEMPLATE_SYSTEM_OPTION;
        ?>[<?php 
        echo $dce_key;
        ?>]">
					<?php 
        foreach ($templates as $key => $value) {
            ?>
				<option value="<?php 
            echo $key;
            ?>"  <?php 
            echo isset($this->options[$dce_key]) ? selected($this->options[$dce_key], $key, \false) : '';
            ?>>
						<?php 
            echo $value;
            ?>
				</option>
			<?php 
        }
        ?>
		</select>
			<?php 
        $dce_quick_edit = admin_url('post.php?action=elementor&post=');
        ?>
		<a class="dce-template-quick-edit<?php 
        if (!isset($this->options[$dce_key]) || !$this->options[$dce_key]) {
            ?> hidden<?php 
        }
        ?>" target="_blank" data-href="<?php 
        echo $dce_quick_edit;
        ?>" href="<?php 
        echo $dce_quick_edit;
        echo isset($this->options[$dce_key]) ? $this->options[$dce_key] : '';
        ?>"><span class="dashicons dashicons-edit"></span></a>
	</span>
		<?php 
    }
    private function _dce_settings_select_template_blank($dce_key)
    {
        $dce_key_template = $dce_key . '_blank';
        $dce_template = isset($this->options[$dce_key_template]) ? $this->options[$dce_key_template] : \false;
        ?>
		<div class="dce-optionals">
			<input class="dce-checkbox" type="checkbox" <?php 
        if ($dce_template) {
            ?>checked="" <?php 
        }
        ?>name="<?php 
        echo DCE_TEMPLATE_SYSTEM_OPTION;
        ?>[<?php 
        echo $dce_key_template;
        ?>]" id="<?php 
        echo $dce_key_template;
        ?>" value="1" onClick="jQuery(this).closest('.dce-template-main-content').find('.dce-template-page-content').toggleClass('dce-template-page-content-original').toggleClass('dce-template-page-content-full');">
			<label class="dce-template-single-full" for="<?php 
        echo $dce_key_template;
        ?>">
				<?php 
        esc_html_e('Force Full Width Template', 'dynamic-content-for-elementor');
        ?> <a target="_blank" href="https://docs.elementor.com/article/316-using-elementor-s-full-width-page-template"><span class="dashicons dashicons-info"></span></a>
			</label>
		</div>
		<?php 
    }
    private function _dce_settings_select_template_layout($dce_key)
    {
        $dce_key_template = $dce_key . '_template';
        $dce_template = isset($this->options[$dce_key_template]) ? $this->options[$dce_key_template] : \false;
        ?>
		<div class="dce-options">
			<label for="<?php 
        echo $dce_key_template;
        ?>"><?php 
        esc_html_e('Select template', 'dynamic-content-for-elementor');
        ?></label>
			<select id="<?php 
        echo $dce_key_template;
        ?>" name="<?php 
        echo DCE_TEMPLATE_SYSTEM_OPTION;
        ?>[<?php 
        echo $dce_key_template;
        ?>]" class="dce-select js-dce-select">
				<option value=""<?php 
        if (!$dce_template) {
            ?> selected="selected"<?php 
        }
        ?>><?php 
        esc_html_e('Theme default (NO Before Archive)', 'dynamic-content-for-elementor');
        ?></option>
				<option value="blank"<?php 
        if ($dce_template == 'blank') {
            ?> selected="selected"<?php 
        }
        ?>><?php 
        esc_html_e('Blank FullWidth template', 'dynamic-content-for-elementor');
        ?></option>
				<option value="boxed"<?php 
        if ($dce_template == 'boxed') {
            ?> selected="selected"<?php 
        }
        ?>><?php 
        esc_html_e('Blank Boxed template', 'dynamic-content-for-elementor');
        ?></option>
				<option value="canvas"<?php 
        if ($dce_template == 'canvas') {
            ?> selected="selected"<?php 
        }
        ?>><?php 
        esc_html_e('Elementor Canvas', 'dynamic-content-for-elementor');
        ?></option>
			</select>
		</div>
		<?php 
    }
    private function _dce_settings_archive($dce_key)
    {
        $dce_col_md = $dce_key . '_col_md';
        $dce_col_sm = $dce_key . '_col_sm';
        $dce_col_xs = $dce_key . '_col_xs';
        ?>
		<div class="dce-optional">
			<label for="<?php 
        echo $dce_col_md;
        ?>"><?php 
        _e('Columns', 'dynamic-content-for-elementor');
        ?></label>
			<div id="<?php 
        echo $dce_key;
        ?>-switchers" class="dce-switchers">
				<div class="elementor-control-responsive-switchers dce-elementor-control-responsive-switchers">
					<?php 
        $dce_col_md_val = isset($this->options[$dce_col_md]) ? $this->options[$dce_col_md] : 4;
        $dce_col_sm_val = isset($this->options[$dce_col_sm]) ? $this->options[$dce_col_sm] : 3;
        $dce_col_xs_val = isset($this->options[$dce_col_xs]) ? $this->options[$dce_col_xs] : 2;
        ?>
					<div class="field-group">
						<input class="dce-input dce-input-md" type="number" min="1" name="<?php 
        echo DCE_TEMPLATE_SYSTEM_OPTION;
        ?>[<?php 
        echo $dce_col_md;
        ?>]" id="<?php 
        echo $dce_col_md;
        ?>" value="<?php 
        echo $dce_col_md_val;
        ?>">
						<input class="dce-input dce-input-sm" type="number" min="1" name="<?php 
        echo DCE_TEMPLATE_SYSTEM_OPTION;
        ?>[<?php 
        echo $dce_col_sm;
        ?>]" id="<?php 
        echo $dce_col_sm;
        ?>" value="<?php 
        echo $dce_col_sm_val;
        ?>">
						<input class="dce-input dce-input-xs" type="number" min="1" name="<?php 
        echo DCE_TEMPLATE_SYSTEM_OPTION;
        ?>[<?php 
        echo $dce_col_xs;
        ?>]" id="<?php 
        echo $dce_col_xs;
        ?>" value="<?php 
        echo $dce_col_xs_val;
        ?>">
					</div>
					<div class="switchers-group">
						<a onclick="jQuery('body').removeClass('elementor-device-mobile').removeClass('elementor-device-tablet').addClass('elementor-device-desktop');" class="elementor-responsive-switcher elementor-responsive-switcher-desktop" data-device="desktop">
							<i class="eicon-device-desktop"></i>
						</a>
						<a onclick="jQuery('body').removeClass('elementor-device-mobile').removeClass('elementor-device-desktop').addClass('elementor-device-tablet');" class="elementor-responsive-switcher elementor-responsive-switcher-tablet" data-device="tablet">
							<i class="eicon-device-tablet"></i>
						</a>
						<a onclick="jQuery('body').removeClass('elementor-device-tablet').removeClass('elementor-device-desktop').addClass('elementor-device-mobile');" class="elementor-responsive-switcher elementor-responsive-switcher-mobile" data-device="mobile">
							<i class="eicon-device-mobile"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php 
    }
    public function display_form()
    {
        ?>

		<div class="wrap">

		<h1><?php 
        echo esc_html(get_admin_page_title());
        ?></h1>

		<?php 
        // SAVING DCE TEMPLATE SETTINGS
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            update_option(DCE_TEMPLATE_SYSTEM_OPTION, Helper::recursive_sanitize_text_field($_POST[DCE_TEMPLATE_SYSTEM_OPTION]));
            update_option('dce_template', Helper::recursive_sanitize_text_field($_POST['dce_template']));
            $this->options = Helper::recursive_sanitize_text_field($_POST[DCE_TEMPLATE_SYSTEM_OPTION]);
            \DynamicContentForElementor\Plugin::instance()->admin_pages->notices->success(__('Your preferences have been saved.', 'dynamic-content-for-elementor'));
        }
        $templates = array(__('None', 'dynamic-content-for-elementor'));
        $get_templates = Helper::get_templates();
        if (!empty($get_templates)) {
            foreach ($get_templates as $template) {
                if ($template['type'] == 'widget') {
                    continue;
                }
                $templates[$template['template_id']] = '[' . $template['type'] . '] ' . $template['title'];
            }
        }
        $preview = array();
        $dceTemplate = array();
        $dceTemplate['post-types']['label'] = __('Post Types', 'dynamic-content-for-elementor');
        // Types
        $typesRegistered = TemplateSystemEngine::get_registered_types();
        foreach ($typesRegistered as $chiave) {
            $preview[$chiave] = get_post_type_archive_link($chiave);
            if ($chiave == 'page') {
                $preview[$chiave] = get_home_url();
                $id_privacy = get_option('wp_page_for_privacy_policy');
                if ($id_privacy) {
                    $preview[$chiave] = get_permalink($id_privacy);
                }
            }
            if ($chiave == 'post') {
                $page_for_post = get_option('page_for_posts');
                if ($page_for_post) {
                    $preview[$chiave] = get_permalink($page_for_post);
                }
            }
            $object_t = get_post_type_object($chiave)->labels;
            $label_t = $object_t->name;
            $dceTemplate['post-types']['options'][$chiave] = $label_t;
            $dceTemplate['post-types']['templates'][$chiave]['single'] = __('Single', 'dynamic-content-for-elementor');
            $dceTemplate['post-types']['templates'][$chiave]['archive'] = __('Archive', 'dynamic-content-for-elementor');
        }
        // Taxonomy
        $taxonomiesRegistered = get_taxonomies();
        $customTaxonomies = \array_diff($taxonomiesRegistered, TemplateSystemEngine::$excluded_taxonomies);
        $dceTemplate['taxonomies']['label'] = __('Taxonomies', 'dynamic-content-for-elementor');
        foreach ($customTaxonomies as $chiave) {
            $terms = get_terms($chiave);
            if (!empty($terms)) {
                $preview[$chiave] = get_term_link(\reset($terms));
            } else {
                $preview[$chiave] = get_home_url();
            }
            $object_t = get_taxonomy($chiave);
            $label_t = $object_t->label;
            $dceTemplate['taxonomies']['options'][$chiave] = $label_t;
            $dceTemplate['taxonomies']['templates'][$chiave]['single'] = __('Single', 'dynamic-content-for-elementor');
            $dceTemplate['taxonomies']['templates'][$chiave]['archive'] = __('Archive', 'dynamic-content-for-elementor');
        }
        $dceTemplate['other-pages']['label'] = __('Other Pages', 'dynamic-content-for-elementor');
        // Search
        $chiave = 'search';
        $preview[$chiave] = get_search_link('lorem ipsum');
        $dceTemplate['other-pages']['options'][$chiave] = __('Search', 'dynamic-content-for-elementor');
        $dceTemplate['other-pages']['templates'][$chiave]['archive'] = __('Archive', 'dynamic-content-for-elementor');
        // User
        $chiave = 'user';
        $preview[$chiave] = get_author_posts_url(get_current_user_id());
        $dceTemplate['other-pages']['options'][$chiave] = __('User', 'dynamic-content-for-elementor');
        $dceTemplate['other-pages']['templates'][$chiave]['archive'] = __('Archive', 'dynamic-content-for-elementor');
        ?>

		<div class="dce-nav-menus-template nav-menus-php">
			<form action="" method="post">
			<?php 
        wp_nonce_field('dce-settings-page', 'dce-settings-page');
        ?>
				<div id="nav-menus-frame" class="wp-clearfix">
					<div id="menu-settings-column" class="metabox-holder">
						<div class="clear"></div>
						<div id="side-sortables" class="accordion-container">
							<div id="dce_template_disabler" class="text-center column-posts wp-tab-active">
								<br>
								<label class="dce-radio-container dce-radio-container-template" onclick="jQuery(this).closest('.accordion-container').find('.accordion-section').addClass('open'); jQuery('#menu-management-liquid');">
									<input value="active" type="radio"<?php 
        if (\DynamicContentForElementor\Plugin::instance()->template_system->is_active()) {
            ?> checked="checked"<?php 
        }
        ?> name="dce_template">
									<span class="dce-radio-checkmark"></span>
									<span class="dce-radio-label"><b><span class="dashicons dashicons-controls-play"></span> <?php 
        _e('Enable', 'dynamic-content-for-elementor');
        ?></b></span>
								</label>
								<label class="dce-radio-container dce-radio-container-template" onclick="jQuery(this).closest('.accordion-container').find('.accordion-section').removeClass('open'); jQuery('#menu-management-liquid');">
									<input value="inactive" type="radio"<?php 
        if (!\DynamicContentForElementor\Plugin::instance()->template_system->is_active()) {
            ?> checked="checked"<?php 
        }
        ?> name="dce_template">
									<span class="dce-radio-checkmark dce-radio-checkmark-disable"></span>
									<span class="dce-radio-label"><b><span class="dashicons dashicons-controls-pause"></span> <?php 
        _e('Disable', 'dynamic-content-for-elementor');
        ?></b></span>
								</label>
								<br><br>
								<hr class="mb-0" style="margin-bottom: 0;">
							</div>
							<ul class="outer-border">
									<?php 
        $k = 0;
        foreach ($dceTemplate as $tkey => $tvalue) {
            ?>
									<li class="control-section accordion-section<?php 
            if (!$k || \true) {
                ?> open<?php 
            }
            ?>" id="dce-<?php 
            echo $tkey;
            ?>">
										<h3 class="accordion-section-title hndle" tabindex="0" onclick="jQuery(this).parent().toggleClass('open')">
											<?php 
            echo $tvalue['label'];
            ?>
										</h3>
										<div class="accordion-section-content">
											<div class="dce-inside">
												<ul class="dce-template-list">
													<?php 
            foreach ($tvalue['options'] as $chiave => $label_t) {
                $dce_key = 'dyncontel_field_single' . ($tkey == 'taxonomies' ? '_taxonomy_' : '') . $chiave;
                $dce_akey = 'dyncontel_field_archive' . ($tkey == 'taxonomies' ? '_taxonomy_' : '') . $chiave;
                $dce_template_used_single = isset($this->options[$dce_key]) && $this->options[$dce_key] ? \true : \false;
                $dce_template_used_archive = isset($this->options[$dce_akey]) && $this->options[$dce_akey] ? \true : \false;
                $dce_template_used = $dce_template_used_single || $dce_template_used_archive ? \true : \false;
                $dashicon = '';
                if ($tkey == 'post-types') {
                    $obj = get_post_type_object($chiave);
                    if ($obj && $obj->menu_icon) {
                        $dashicon = $obj->menu_icon;
                    } else {
                        $dashicon = 'dashicons-' . $chiave . ' dashicons-admin-' . $chiave;
                        if ($chiave != 'page') {
                            $dashicon = 'dashicons-admin-post ' . $dashicon;
                        }
                    }
                    if ($chiave == 'attachment') {
                        $dashicon = 'dashicons-admin-media';
                    }
                }
                if ($tkey == 'taxonomies') {
                    $obj = get_taxonomy($chiave);
                    if ($obj && $obj->hierarchical) {
                        $dashicon = 'dashicons-category';
                    } else {
                        $dashicon = 'dashicons-tag';
                    }
                }
                if ($tkey == 'other-pages') {
                    $dashicon = 'dashicons-' . $chiave . ' dashicons-admin-' . $chiave;
                    if ($chiave == 'user') {
                        $dashicon = 'dashicons-admin-users';
                    }
                }
                ?>
														<li class="dce-template-list-li<?php 
                echo $chiave == 'post' ? ' nav-tab-selected' : '';
                ?>">
															<?php 
                $dce_tkey = $tkey . '-' . $chiave;
                ?>
															<?php 
                if ($dce_template_used_archive) {
                    ?><a class="dce-quick-goto-active-setting" href="#<?php 
                    echo $dce_tkey . '-archive';
                    ?>"><span class="pull-right dashicons dashicons-exerpt-view"></span></a><?php 
                }
                ?>
															<?php 
                if ($dce_template_used_single) {
                    ?><a class="dce-quick-goto-active-setting" href="#<?php 
                    echo $dce_tkey . '-single';
                    ?>"><span class="pull-right dashicons dashicons-welcome-widgets-menus"></span></a><?php 
                }
                ?>
															<a class="nav-tab-link" href="#<?php 
                echo $dce_tkey;
                ?>" onClick="
																	jQuery('.dce-template-edit').addClass('hidden');
																	jQuery(jQuery(this).attr('href')).removeClass('hidden');
																	jQuery('.dce-nav-menus-template .nav-tab-selected').removeClass('nav-tab-selected');
																	jQuery(this).parent().addClass('nav-tab-selected');
																	var scrollmem = jQuery('html').scrollTop() || jQuery('body').scrollTop();
																	location.hash = jQuery(this).attr('href').substr(1);
																	jQuery('html,body').scrollTop(scrollmem);
																	jQuery('.dce-quick-goto-active-setting-active').removeClass('dce-quick-goto-active-setting-active');
																	return false;
																">
																<?php 
                if ($dashicon) {
                    if (\filter_var($dashicon, \FILTER_VALIDATE_URL)) {
                        ?>
																		<img src="<?php 
                        echo $dashicon;
                        ?>" height="20" width="20" style="vertical-align: middle; filter: invert(70%);">
																	<?php 
                    } else {
                        ?>
																		<span class="dashicons <?php 
                        echo $dashicon;
                        ?>"></span>
																<?php 
                    }
                }
                ?>
																<abbr title="<?php 
                echo $chiave;
                ?>"><?php 
                echo $label_t;
                ?></abbr>
															</a>
														</li>
														<?php 
            }
            ?>
												</ul>
											</div>
										</div>
									</li>
										<?php 
            $k++;
        }
        ?>

							</ul>
						</div>
					</div>

					<div id="menu-management-liquid">
						<div id="menu-management">

								<?php 
        $i = 0;
        foreach ($dceTemplate as $tkey => $tvalue) {
            foreach ($tvalue['options'] as $chiave => $label_t) {
                ?>
									<div id="<?php 
                echo $tkey . '-' . $chiave;
                ?>" class="menu-edit dce-template-edit <?php 
                echo $i ? 'hidden' : '';
                ?>">

										<div class="nav-menu-header">
											<div class="major-publishing-actions wp-clearfix">

												<ul class="dce-template-tabs <?php 
                echo $tkey;
                ?>-tabs wp-tab-bar">
													<?php 
                $t = 0;
                foreach ($tvalue['templates'][$chiave] as $skey => $svalue) {
                    if ($skey == 'archive' && $chiave == 'attachment') {
                        continue;
                    }
                    ?>
													<li class="dce-wp-tab<?php 
                    if (!$t) {
                        ?> wp-tab-active<?php 
                    }
                    ?>">
														<a class="nav-tab-link" href="#<?php 
                    echo $tkey . '-' . $chiave . '-' . $skey;
                    ?>" onClick="
																				jQuery('#<?php 
                    echo $tkey . '-' . $chiave;
                    ?> .dce-template-post-body').addClass('hidden');
																				jQuery(jQuery(this).attr('href')).removeClass('hidden');
																				jQuery('#<?php 
                    echo $tkey . '-' . $chiave;
                    ?> .dce-wp-tab').removeClass('wp-tab-active');
																				jQuery(this).parent().addClass('wp-tab-active');
																				var scrollmem = jQuery('html').scrollTop() || jQuery('body').scrollTop();
																				location.hash = jQuery(this).attr('href').substr(1);
																				jQuery('html,body').scrollTop(scrollmem);
																				jQuery('.dce-quick-goto-active-setting-active').removeClass('dce-quick-goto-active-setting-active');
																				jQuery('.dce-quick-goto-active-setting[href=#<?php 
                    echo $tkey . '-' . $chiave . '-' . $skey;
                    ?>]').addClass('dce-quick-goto-active-setting-active');
																				return false;
														">
															<span class="dashicons dashicons-<?php 
                    echo $skey == 'archive' ? 'exerpt-view' : 'welcome-widgets-menus';
                    ?>"></span>
															<?php 
                    echo $svalue;
                    ?>
														</a>
													</li>
														<?php 
                    $t++;
                }
                ?>
												</ul>

												<div class="publishing-action">
													<input type="submit" name="save_menu_header" class="save_menu save_menu_header button button-primary button-large menu-save" value="<?php 
                _e('Save Settings', 'dynamic-content-for-elementor');
                ?>">
												</div>
											</div>
										</div>

											<?php 
                $k = 0;
                foreach ($tvalue['templates'][$chiave] as $skey => $svalue) {
                    ?>
											<div id="<?php 
                    echo $tkey . '-' . $chiave . '-' . $skey;
                    ?>" class="post-body dce-template-post-body dce-template-post-body-<?php 
                    echo $skey;
                    ?> <?php 
                    echo $k ? 'hidden' : '';
                    ?>"">
												<div class="post-body-content" class="wp-clearfix">
													<div class="tabs-panel-template" class="tabs-panel tabs-panel-active">
														<div class="tabs-panel-inner">
															<h1>
																<?php 
                    echo $svalue;
                    ?> / <abbr title="<?php 
                    echo $chiave;
                    ?>"><?php 
                    echo $label_t;
                    ?></abbr>
																<?php 
                    if (isset($preview[$chiave]) && $preview[$chiave]) {
                        ?>
																<a href="<?php 
                        echo $preview[$chiave];
                        ?>" target="_blank" class="dce-template-preview"><span class="dashicons dashicons-admin-links"></span></a>
																<?php 
                    }
                    ?>
															</h1>
															<br>
															<div class="dce-template-panel dce-template-main">

																<?php 
                    if ($skey != 'single') {
                        ?>
																<div class="dce-template-panel dce-template-before">
																	<?php 
                        $dce_key = 'dyncontel_before_field_' . $skey . ($tkey == 'taxonomies' ? '_taxonomy_' : '') . $chiave;
                        // compatibility with old settings
                        if ($dce_key == 'dyncontel_before_field_archiveuser') {
                            if (!isset($this->options[$dce_key]) && isset($this->options['dyncontel_field_singleuser'])) {
                                $this->options[$dce_key] = $this->options['dyncontel_field_singleuser'];
                            }
                        }
                        if ($dce_key == 'dyncontel_before_field_archivesearch') {
                            if (!isset($this->options[$dce_key]) && isset($this->options['dyncontel_field_singlesearch'])) {
                                $this->options[$dce_key] = $this->options['dyncontel_field_singlesearch'];
                            }
                        }
                        ?>
																	<div class="dce-template-icon dce-template-before-icon">
																		<div class="dce-template-icon-bar dce-template-before-icon-bar<?php 
                        echo isset($this->options[$dce_key]) && $this->options[$dce_key] ? ' dce-template-icon-bar-template' : '';
                        ?>"></div>
																	</div>
																	<h4><?php 
                        _e('Before', 'dynamic-content-for-elementor');
                        ?></h4>
																	<?php 
                        $this->_dce_settings_select_template($dce_key, $templates);
                        ?>
																</div>
																<?php 
                    }
                    ?>

																<div class="dce-template-main-content">

																	<?php 
                    $dce_key = 'dyncontel_field_' . $skey . ($tkey == 'taxonomies' ? '_taxonomy_' : '') . $chiave;
                    $template = 'original';
                    if ($skey == 'single') {
                        if (isset($this->options[$dce_key . '_blank']) && $this->options[$dce_key . '_blank']) {
                            if ($this->options[$dce_key . '_blank'] == 'canvas') {
                                $template = 'canvas';
                            } else {
                                $template = 'full';
                            }
                        }
                    } else {
                        if (isset($this->options[$dce_key]) && $this->options[$dce_key]) {
                            if (isset($this->options[$dce_key . '_template']) && $this->options[$dce_key . '_template']) {
                                $template = $this->options[$dce_key . '_template'];
                            } else {
                                $template = 'canvas';
                            }
                        }
                    }
                    if ($template == 'blank' && $chiave == 'user') {
                        if (isset($this->options[$dce_key]) && $this->options[$dce_key]) {
                            $template = 'canvas';
                        } else {
                            $template = 'original';
                        }
                    }
                    ?>
																	<div class="dce-template-page dce-template-content-<?php 
                    echo isset($this->options[$dce_key]) && $this->options[$dce_key] ? 'template' : 'original';
                    ?> dce-template-content-<?php 
                    echo $template;
                    ?>">
																		<div class="dce-template-page-content dce-template-page-content-<?php 
                    echo $template;
                    ?>">
																			<span class="dce-template-page-content-preview"></span>
																			<span class="dce-template-page-content-preview"></span>
																			<span class="dce-template-page-content-preview"></span>
																			<span class="dce-template-page-content-preview"></span>
																		</div>
																	</div>
																	<?php 
                    if ($skey == 'single') {
                        ?>
																		<h4><?php 
                        _e('Page Template', 'dynamic-content-for-elementor');
                        ?></h4>
																		<?php 
                        $this->_dce_settings_select_template($dce_key, $templates);
                        ?>
																		<br><br>
																		<?php 
                        $dce_key = 'dyncontel_field_' . $skey . ($tkey == 'taxonomies' ? '_taxonomy_' : '') . $chiave;
                        $dce_tkey = $dce_key . '_blank';
                        $dce_template = isset($this->options[$dce_tkey]) ? $this->options[$dce_tkey] : \false;
                        ?>
																		<div class="dce-template-single-type">
																			<h4><?php 
                        _e('Layout', 'dynamic-content-for-elementor');
                        ?></h4>
																			<label class="dce-radio-container dce-radio-container-template">
																				<input value="0" type="radio"<?php 
                        if (!$dce_template || $dce_template == '0') {
                            ?> checked="checked"<?php 
                        }
                        ?> name="<?php 
                        echo DCE_TEMPLATE_SYSTEM_OPTION;
                        ?>[<?php 
                        echo $dce_tkey;
                        ?>]">
																				<span class="dce-radio-checkmark"></span>
																				<span class="dce-radio-label"><?php 
                        _e('Default', 'dynamic-content-for-elementor');
                        ?></span>
																			</label>
																			<label class="dce-radio-container dce-radio-container-template">
																				<input value="header-footer" type="radio"<?php 
                        if ($dce_template == '1' || $dce_template == 'header-footer') {
                            ?> checked="checked"<?php 
                        }
                        ?> name="<?php 
                        echo DCE_TEMPLATE_SYSTEM_OPTION;
                        ?>[<?php 
                        echo $dce_tkey;
                        ?>]">
																				<span class="dce-radio-checkmark"></span>
																				<span class="dce-radio-label"><?php 
                        _e('Full-Width', 'dynamic-content-for-elementor');
                        ?></span>
																			</label>
																			<label class="dce-radio-container dce-radio-container-template">
																				<input value="canvas" type="radio"<?php 
                        if ($dce_template == 'canvas') {
                            ?> checked="checked"<?php 
                        }
                        ?> name="<?php 
                        echo DCE_TEMPLATE_SYSTEM_OPTION;
                        ?>[<?php 
                        echo $dce_tkey;
                        ?>]">
																				<span class="dce-radio-checkmark"></span>
																				<span class="dce-radio-label"><?php 
                        _e('Canvas', 'dynamic-content-for-elementor');
                        ?></span>
																			</label>
																		</div>
																		<?php 
                    }
                    if ($skey == 'archive') {
                        ?>
																		<h4><?php 
                        _e('Template', 'dynamic-content-for-elementor');
                        ?></h4>
																		<?php 
                        $this->_dce_settings_select_template($dce_key, $templates);
                        ?>
																		<br>

																		<?php 
                        $teaser_template = isset($this->options[$dce_key]) ? $this->options[$dce_key] : 0;
                        $dce_tkey = $dce_key . '_template';
                        $dce_template = isset($this->options[$dce_tkey]) ? $this->options[$dce_tkey] : 'canvas';
                        //false;
                        ?>
																		<div class="dce-template-archive-type<?php 
                        echo !$teaser_template ? ' hidden' : '';
                        ?>">
																			<h4><?php 
                        _e('Layout', 'dynamic-content-for-elementor');
                        ?></h4>
																			<label class="dce-radio-container dce-radio-container-template">
																				<input value="canvas" type="radio"<?php 
                        if (!$dce_template || $dce_template == 'canvas') {
                            ?> checked="checked"<?php 
                        }
                        ?> name="<?php 
                        echo DCE_TEMPLATE_SYSTEM_OPTION;
                        ?>[<?php 
                        echo $dce_tkey;
                        ?>]">
																				<span class="dce-radio-checkmark"></span>
																				<span class="dce-radio-label"><?php 
                        _e('Canvas', 'dynamic-content-for-elementor');
                        ?></span>
																			</label><label class="dce-radio-container dce-radio-container-template">
																				<input value="boxed" type="radio"<?php 
                        if ($dce_template == 'boxed') {
                            ?> checked="checked"<?php 
                        }
                        ?> name="<?php 
                        echo DCE_TEMPLATE_SYSTEM_OPTION;
                        ?>[<?php 
                        echo $dce_tkey;
                        ?>]">
																				<span class="dce-radio-checkmark"></span>
																				<span class="dce-radio-label"><?php 
                        _e('Boxed', 'dynamic-content-for-elementor');
                        ?></span>
																			</label><label class="dce-radio-container dce-radio-container-template">
																				<input value="blank" type="radio"<?php 
                        if ($dce_template == 'blank') {
                            ?> checked="checked"<?php 
                        }
                        ?> name="<?php 
                        echo DCE_TEMPLATE_SYSTEM_OPTION;
                        ?>[<?php 
                        echo $dce_tkey;
                        ?>]">
																				<span class="dce-radio-checkmark"></span>
																				<span class="dce-radio-label"><?php 
                        _e('Full-Width', 'dynamic-content-for-elementor');
                        ?></span>
																			</label>

																			<br><br>
																			<div class="dce-template-archive-blocks<?php 
                        if (!\in_array($dce_template, array('full', 'boxed', 'blank'))) {
                            ?> hidden<?php 
                        }
                        ?>">
																				<?php 
                        $this->_dce_settings_archive($dce_key);
                        ?>
																			</div>
																		</div>

																	<?php 
                    }
                    ?>
																</div>

																	<?php 
                    if ($skey != 'single') {
                        ?>
																<div class="dce-template-panel dce-template-after">
																		<?php 
                        $dce_key = 'dyncontel_after_field_' . $skey . ($tkey == 'taxonomies' ? '_taxonomy_' : '') . $chiave;
                        ?>
																	<div class="dce-template-icon dce-template-after-icon">
																		<div class="dce-template-icon-bar dce-template-after-icon-bar<?php 
                        echo isset($this->options[$dce_key]) && $this->options[$dce_key] ? ' dce-template-icon-bar-template' : '';
                        ?>"></div>
																	</div>
																	<h4><?php 
                        _e('After', 'dynamic-content-for-elementor');
                        ?></h4>
																		<?php 
                        $this->_dce_settings_select_template($dce_key, $templates);
                        ?>
																</div>
																<?php 
                    }
                    ?>

															</div>

														</div>
													</div>



												</div>
											</div>
												<?php 
                    $k++;
                }
                ?>

										<div class="nav-menu-footer">
											<div class="major-publishing-actions wp-clearfix">
												<div class="publishing-action">
													<input type="submit" name="save_menu_footer" class="save_menu save_menu_footer button button-primary button-large menu-save" value="<?php 
                _e('Save Settings', 'dynamic-content-for-elementor');
                ?>">
												</div>
											</div>
										</div>

									</div>
										<?php 
                $i++;
            }
        }
        ?>
						</div>
					</div>
				</div>
				<input type="hidden" value="update" name="action">
			</form>
		</div>

		<script>
		jQuery(function(){

			// reopen last settings
			if (location.hash) {
				var hash = location.hash;
				var mbtn = '#'+jQuery('.nav-tab-link[href='+hash+']').closest('.dce-template-edit').attr('id');
				jQuery('.nav-tab-link[href='+mbtn+']').trigger('click');
				jQuery('.nav-tab-link[href='+hash+']').trigger('click');
			}

			jQuery('.dce-quick-goto-active-setting').on('click', function(){
				var href = jQuery(this).attr('href');
				var mbtn = jQuery(this).closest('.dce-template-list-li').find('.nav-tab-link');
				mbtn.trigger('click');
				jQuery('.nav-tab-link[href='+href+']').trigger('click');
				var scrollmem = jQuery('html').scrollTop() || jQuery('body').scrollTop();
				location.hash = href.substr(1);
				jQuery('html,body').scrollTop(scrollmem);
				jQuery(this).addClass('dce-quick-goto-active-setting-active');
				return false;
			});

			jQuery('.dce-template-quick-remove').on('click', function(){
				var quick_remove = jQuery(this).closest('.dce-template-select-wrapper').find('.dce-select-template');
				quick_remove.val(0);
				quick_remove.trigger('change');
				jQuery(this).addClass('hidden');
				return false;
			});

			jQuery('.dce-select-template').on('change', function(){
				var quick_edit = jQuery(this).closest('.dce-template-select-wrapper').find('.dce-template-quick-edit');
				var quick_remove = jQuery(this).closest('.dce-template-select-wrapper').find('.dce-template-quick-remove');
				if (jQuery(this).val() > 0) {
					quick_remove.removeClass('hidden');
					quick_edit.removeClass('hidden');
					quick_edit.attr('href', quick_edit.data('href')+jQuery(this).val());
				} else {
					quick_edit.addClass('hidden');
					quick_edit.addClass('hidden');
				}
			});

			jQuery('.dce-template-post-body-single .dce-select-template').on('change', function(){
				if (jQuery(this).val() > 0) {
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-page').removeClass('dce-template-content-original').addClass('dce-template-content-template');
				} else {
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-page').addClass('dce-template-content-original').removeClass('dce-template-content-template');
				}
			});

			jQuery('.dce-template-post-body-archive .dce-select-template').on('change', function(){
				if (jQuery(this).val() > 0) {
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-archive-type').removeClass('hidden');
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-page').removeClass('dce-template-content-original').addClass('dce-template-content-template');
					jQuery(this).closest('.dce-template-main-content').find('.dce-radio-container input[type=radio]:checked').trigger('click');
				} else {
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-page').addClass('dce-template-content-original').removeClass('dce-template-content-template');
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-archive-type').addClass('hidden');
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-page-content').attr('class', 'dce-template-page-content dce-template-page-content-original');
				}
			});





			jQuery('.dce-template-before .dce-select-template, .dce-template-after .dce-select-template').on('change', function(){
				if (jQuery(this).val() > 0) {
					jQuery(this).closest('.dce-template-panel').find('.dce-template-icon-bar').addClass('dce-template-icon-bar-template');
				} else {
					jQuery(this).closest('.dce-template-panel').find('.dce-template-icon-bar').removeClass('dce-template-icon-bar-template');
				}
			});

			jQuery('.dce-template-post-body-archive .dce-radio-container-template').on('click', function(){
				var value = jQuery(this).find('input[type=radio]').val();
				if (value && value != 'canvas') {
					jQuery(this).closest('.dce-template-main').find('.dce-template-archive-blocks').removeClass('hidden');
				} else {
					jQuery(this).closest('.dce-template-main').find('.dce-template-archive-blocks').addClass('hidden');
				}
				if (!value) {
					jQuery(this).closest('.dce-template-main').find('.dce-template-teaser').addClass('hidden');
				} else {
					jQuery(this).closest('.dce-template-main').find('.dce-template-teaser').removeClass('hidden');
				}
				if (!value) {
					value = 'original';
				}
				if (value == 'blank') {
					value = 'full';
				}
				jQuery(this).closest('.dce-template-main-content').find('.dce-template-page-content').attr('class', 'dce-template-page-content dce-template-page-content-'+value);
			});

			jQuery('.dce-template-post-body-single .dce-radio-container-template').on('click', function(){
				var value = jQuery(this).find('input[type=radio]').val();
				if (value && value != 'canvas') {
					jQuery(this).closest('.dce-template-main').find('.dce-template-single-blocks').removeClass('hidden');
				} else {
					jQuery(this).closest('.dce-template-main').find('.dce-template-single-blocks').addClass('hidden');
				}
				if (!value || value == '0') {
					value = 'original';
				}
				if (value == 'header-footer' || value == 1 || value == '1') {
					value = 'full';
				}
				if (value == 'canvas' || value == 2 || value == '2') {
					value = 'canvas';
				}
				jQuery(this).closest('.dce-template-main-content').find('.dce-template-page-content').attr('class', 'dce-template-page-content dce-template-page-content-'+value);

				jQuery(this).closest('.dce-template-main-content').find('.dce-template-page').removeClass('dce-template-content-canvas').removeClass('dce-template-content-default').removeClass('dce-template-content-full');
				if (value != 'original') {
					jQuery(this).closest('.dce-template-main-content').find('.dce-template-page').addClass('dce-template-content-'+value);
				}
			});

			<?php 
        if (!\DynamicContentForElementor\Plugin::instance()->template_system->is_active()) {
            ?>
				jQuery('#menu-management-liquid');
				jQuery('#menu-settings-column .accordion-section').removeClass('open');
			<?php 
        }
        ?>
		});

		</script>
		</div>

		<?php 
    }
}
