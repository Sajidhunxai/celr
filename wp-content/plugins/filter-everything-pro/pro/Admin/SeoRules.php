<?php


namespace FilterEverything\Filter\Pro\Admin;

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\Container;
use FilterEverything\Filter\Pro\Settings\Tabs\SeoRulesTab;

class SeoRules
{
    const SEO_RULE_KEY = 'wpc_seo_rules';

    const NONCE_ACTION = 'wpc-seo-rule-nonce';

    private $defaultFields = [];

    private $postType = ''; // Seo Rule object can't exists without post type

    private $sep = '#';

    private $sectionSep = '/';

    private $anyValue = '-1';

    private $anyTitle = '{any}';

    private $noValue = '0';

    public function registerHooks()
    {
        add_action( 'admin_print_scripts', array( $this, 'includeAdminJs' ) );

        add_action( 'save_post', array( $this, 'saveRule' ), 10, 2 );
        add_action( 'wp_ajax_wpc-get-indexed-filters', array( $this, 'sendIndexedFilers' ) );

        add_filter( 'wpc_input_type_text', array( $this, 'addSeoVarButton' ), 10, 2 );
        add_filter( 'wpc_input_type_textarea', array( $this, 'addSeoVarButton' ), 10, 2 );
        add_filter( 'wpc_input_type_select', array( $this, 'addCustomLabel' ), 10, 2 );
        add_action( 'wpc_after_seo_vars_button', [$this, 'addSeoVarToolTip'] );

        add_filter( 'post_updated_messages', [$this, 'seoRulesActionsMessages'] );
        add_filter( 'bulk_post_updated_messages', [ $this, 'seoRulesBulkActionsMessages' ], 10, 2 );

        add_filter( 'page_row_actions', [$this, 'seoRulesRowActions'], 10, 2 );

        add_action( 'restrict_manage_posts', [$this, 'restrictManagePosts'], 999 );

        add_action( 'wp_ajax_wpc-validate-seo-rules', [ $this, 'ajaxValidateSeoRules' ] );

    }

    public function ajaxValidateSeoRules()
    {
        $container      = Container::instance();
        $post           = $container->getThePost();
        $filterFields   = $container->getFilterFieldsService();

        // Will be validated later
        $data = isset( $post['validateData'] ) ? $post['validateData'] : false;
        $response = [];

        if( ! $data ){
            $filterFields->pushError(201);
        }

        if( ! isset( $data['_flrt_nonce'] ) || ! $this->verifyNonce( $data['_flrt_nonce'] ) ){
            $filterFields->pushError(201); // Default common error
        }

        $data['wpc_seo_rules']['ID'] = isset( $data['post_ID'] ) ? $data['post_ID'] : false;
        $this->validateRuleFields( $data['wpc_seo_rules'] );

        $filterFields->fillErrorsMessages();
        $errors = $filterFields->getErrors();

        // Send errors if they exist
        if( $errors && ! empty( $errors ) ){
            $response['errors'] = $errors;
            wp_send_json_error($response);
        }

        wp_send_json_success();
    }

    public function restrictManagePosts( $post_type )
    {
        if( $post_type === FLRT_SEO_RULES_POST_TYPE ){
            $output = ob_get_clean();
            ob_start();
        }
    }

    public function seoRulesRowActions( $actions, $post )
    {
        if( isset( $post->post_type ) && $post->post_type === FLRT_SEO_RULES_POST_TYPE ){
            $new_actions = [];

            foreach( $actions as $key => $action ){
                if( in_array( $key, array( 'edit', 'trash', 'untrash', 'delete' ) ) ){
                    $new_actions[$key] = $action;
                }
            }
            return $new_actions;
        }
        return $actions;
    }

    public function seoRulesActionsMessages( $messages )
    {
        if( ! isset( $messages[ FLRT_SEO_RULES_POST_TYPE ] ) ){
            $messages[ FLRT_SEO_RULES_POST_TYPE ] = array(
                0 => '',
                1 => esc_html__( 'SEO rule updated.', 'filter-everything' ),
                2 => esc_html__( 'Custom field updated.', 'filter-everything' ),
                3 => esc_html__( 'Custom field deleted.', 'filter-everything' ),
                4 => esc_html__( 'SEO rule updated.', 'filter-everything' ),
                5 => false,
                6 => esc_html__( 'SEO rule created.', 'filter-everything' ),
                7 => esc_html__( 'SEO rule saved.', 'filter-everything' ),
                8 => esc_html__( 'SEO rule submitted.', 'filter-everything' ),
                9 => esc_html__( 'SEO rule scheduled for', 'filter-everything' ),
                10 => esc_html__( 'SEO rule draft updated.', 'filter-everything' )
            );
        }

        return $messages;
    }

    public function seoRulesBulkActionsMessages( $messages, $bulk_counts )
    {
        if( ! isset( $messages[ FLRT_SEO_RULES_POST_TYPE ] ) ){
            $messages[ FLRT_SEO_RULES_POST_TYPE ] = array(
                /* translators: %s: Number of posts. */
                'updated'   => esc_html( _n( '%s SEO rule updated.', '%s SEO rules updated.', $bulk_counts['updated'], 'filter-everything' ) ),
                'locked'    => ( 1 === $bulk_counts['locked'] ) ? esc_html__( '1 SEO rule not updated, somebody is editing it.', 'filter-everything' ) :
                    /* translators: %s: Number of posts. */
                    esc_html( _n( '%s SEO rule not updated, somebody is editing it.', '%s SEO rules not updated, somebody is editing them.', $bulk_counts['locked'], 'filter-everything' ) ),
                /* translators: %s: Number of posts. */
                'deleted'   => esc_html( _n( '%s SEO rule permanently deleted.', '%s SEO rules permanently deleted.', $bulk_counts['deleted'], 'filter-everything' ) ),
                /* translators: %s: Number of posts. */
                'trashed'   => esc_html( _n( '%s SEO rule moved to the Trash.', '%s SEO rules moved to the Trash.', $bulk_counts['trashed'], 'filter-everything' ) ),
                /* translators: %s: Number of posts. */
                'untrashed' => esc_html( _n( '%s SEO rule restored from the Trash.', '%s SEO rules restored from the Trash.', $bulk_counts['untrashed'], 'filter-everything' ) ),
            );
        }
        return $messages;
    }

    function setupDefaultFields()
    {
        $hasIndexedFilters = $this->getPostTypesWithIndexedFilters();
        if( empty( $hasIndexedFilters ) ){
            add_filter( 'wpc_input_type_select', array( $this, 'addEmptyPostTypeFieldMessage'), 10, 2 );
        }
        // All serialized data should be stored in post_excerpt field
        $this->defaultFields = array(
            // Rule post type can be stored in serialized data.
            // There is no need to find rule by post type
            // We can find it with rule key (post_name is index field)
            'rule_post_type' => array(
                'type' => 'Select',
                'label' => esc_html__('Post Type', 'filter-everything'),
                'name' => $this->generateInputName( 'rule_post_type' ),
                'id'   => $this->generateInputID('rule_post_type'),
                'class' => 'wpc-field-rule-post-type',
                'options' => $hasIndexedFilters,
                'default' => 'post',
                'instructions' => esc_html__('Select Post Type', 'filter-everything'),

            ),
            'rule_seo_title' => array(
                'type' => 'Text',
                'label' => esc_html__( 'SEO Title', 'filter-everything' ),
                'name' => $this->generateInputName( 'rule_seo_title' ),
                'id'   => $this->generateInputID('rule_seo_title' ),
                'class' => 'wpc-field-rule-seo-title wpc-vars-insertable',
                'data-caret' => '',
                'default' => '',
                'instructions' => wp_kses(
                    __( 'Appears between <br />&#x3C;title&#x3E; and &#x3C;/title&#x3E;', 'filter-everything' ),
                    array( 'br' => array() )
                ),
            ),
            'rule_meta_desc' => array(
                'type' => 'Textarea',
                'label' => esc_html__( 'Meta Description', 'filter-everything' ),
                'name' => $this->generateInputName('rule_meta_desc' ),
                'id'   => $this->generateInputID('rule_meta_desc' ),
                'class' => 'wpc-field-rule-meta-desc wpc-vars-insertable',
                'data-caret' => '',
                'default' => '',
            ),
            'rule_h1' => array(
                'type' => 'Text',
                'label' => esc_html__('H1 Title', 'filter-everything'),
                'name' => $this->generateInputName( 'rule_h1' ),
                'id'   => $this->generateInputID('rule_h1' ),
                'class' => 'wpc-field-rule-h1 wpc-vars-insertable',
                'data-caret' => '',
                'default' => '',
                'instructions' => wp_kses(
                    __('Appears between <br />&#x3C;h1&#x3E; and &#x3C;/h1&#x3E;', 'filter-everything' ),
                    array( 'br' => array() )
                )
            ),
            'rule_description' => array(
                'type' => 'Wpeditor',
                'label' => esc_html__( 'SEO Description', 'filter-everything' ),
                'name' => $this->generateInputName( 'rule_description' ),
                'id'   => $this->generateInputID('rule_description' ),
                'class' => 'wpc-field-rule-description',
                'default' => '',
                'textarea_rows' => 8,
                'instructions' => esc_html__( 'Appears on the page(s) matches this Rule and is visible for visitors', 'filter-everything' ),
                'particular' => 'post_content', // Determine that this is specific field should be stored in wp_post column
            )

        );
    }

    private function getPostTypesWithIndexedFilters()
    {
        $this->setPostType();
        $fse                = Container::instance()->getFilterSetService();
        $hasIndexedFilters  = [];

        foreach ($fse->getPostTypes() as $post_type => $postTypeLabel) {

            if (!empty($this->getIndexedFilters($post_type))) {
                $hasIndexedFilters[$post_type] = $postTypeLabel;
            }
        }

        return $hasIndexedFilters;
    }

    /**
     * @param $fieldId
     * @param $html
     * @return false|string
     */
    private function getSeoVarTemplate( $fieldId, $html )
    {
        $templateManager = Container::instance()->getTemplateManager();

        ob_start();

        $templateManager->includeAdminView( 'seo-vars', array(
            'field_id' => $fieldId,
            'field_html' => $html
        ) );

        return ob_get_clean();
    }

    public function addEmptyPostTypeFieldMessage( $html, $attributes )
    {
        if( isset( $attributes['id'] ) ){

            if( $attributes['id'] == $this->generateInputID('rule_post_type') ){
                $settingsUrl = admin_url( 'edit.php?post_type=filter-set&page=filters-settings&tab=seorules' );
                $seoRules = new SeoRulesTab();

                $html = __( 'There are no Post Types with filters available for SEO rules yet.<br />', 'filter-everything'); // Escaped later
                $html .= sprintf( __( 'You have to activate them on the <a href="%s" target="_blank">%s settings page</a> first.', 'filter-everything' ), $settingsUrl, $seoRules->getLabel() );
            }
        }

        return wp_kses( $html, array(
                'br' => array(),
                'a' => array('href' => true, 'target' => true )
            )
        );
    }

    public function addSeoVarToolTip( $field_id )
    {
        if( $field_id === 'rule_seo_title' ){
            echo flrt_tooltip( array(
                    'tooltip' => wp_kses(
                            __( 'Variables will be replaced with specific values on the page matches current SEO Rule.<br />For example on the page with URL path:<br />/color-blue/size-large/<br />variables {color} and {size} will be replaced with words "blue" and "large".', 'filter-everything' ),
                            array( 'br' => array() )
                        )
                    )
                );
        }

        return $field_id;
    }

    public function addCustomLabel( $html, $attributes )
    {
        $neededClasses = array(
            'wpc-field-rule-filter',
            'wpc-field-rule-wp-entity'
        );

        if( isset( $attributes['class'] ) && in_array( $attributes['class'], $neededClasses, true ) ){
            $html = '<label for="'.$attributes['id'].'">' . $attributes['title'] . '</label>' . $html;
        }

        return $html;
    }

    public function addSeoVarButton( $html, $attributes )
    {
        if( isset( $attributes['id'] ) ){

            if( $attributes['id'] == $this->generateInputID('rule_h1' ) ){
                $html = $this->getSeoVarTemplate( 'rule_h1', $html );
            }

            if( $attributes['id'] == $this->generateInputID('rule_seo_title' ) ){
                $html = $this->getSeoVarTemplate( 'rule_seo_title', $html );
            }

            if( $attributes['id'] == $this->generateInputID('rule_meta_desc') ){
                $html = $this->getSeoVarTemplate( 'rule_meta_desc', $html );
            }

        }

        return $html;
    }

    public function createSeoVarsList( $fields )
    {
        $seoVars = [];

        foreach ( $fields as $fieldKey => $field ){
            $seoVars[ $field['slug'] ] = $field['label'];
        }

        return apply_filters( 'wpc_seo_vars_list', $seoVars );
    }

    public function sendIndexedFilers()
    {
        $post      = Container::instance()->getThePost();
        $nonce     = isset( $post['_wpnonce'] ) ? $post['_wpnonce'] : false;
        $post_type = isset( $post['postType'] ) ? $post['postType'] : '';
        $post_id   = isset( $post['postId'] ) ? $post['postId'] : '';

        $errorResponse  = array(
            'message' => esc_html__('An error occured. Please, refresh the page and try again.', 'filter-everything')
        );

        if( ! $this->verifyNonce( $nonce ) ){
            wp_send_json_error($errorResponse);
        }

        $this->setPostType($post_type);
        $fields     = $this->getIntersectionFields();

        $seoVars    = $this->createSeoVarsList($fields);
        $ruleData   = $this->getRuleData( $post_id );

        // Get already existing rule field values
        if( isset( $ruleData['rule_post_type'] ) && $ruleData['rule_post_type'] == $post_type ){
            $excludeFields = array_keys( $this->getDefaultFields() );

            $this->addRuleFilterFields();
            $this->populateRuleFields($ruleData);

            $savedFields = $this->getDefaultFields();

            // Exclude default fields like rule_post_type, rule_h1 etc
            flrt_extract_vars( $savedFields, $excludeFields );
            $fields = wp_parse_args( $savedFields, $fields );
        }

        $response = [];

        ob_start();
        flrt_include_admin_view('filters-intersections', array(
                'fields'  => $fields
            )
        );
        $response['html'] = ob_get_clean();

        $response['seovars'] = $seoVars;
        wp_send_json_success($response);
    }

    public function setPostType( $postType = '' )
    {
        $this->postType = $postType ? $postType : 'post';
    }

    public function getPostType()
    {
        return $this->postType;
    }

    private function getDefaultFields()
    {
        return $this->defaultFields;
    }

    public function getIndexedFilters( $post_type = '' )
    {
        $indexedFilters = [];
        $em             = Container::instance()->getEntityManager();
        $container      = Container::instance();

        if( ! $post_type ){
            $post_type      = $this->getPostType();
        }

        $key = 'wpc_indexed_filters_' . $post_type;

        if( ! $indexedFilters = $container->getParam($key) ) {

            $relatedFilters = $em->getFiltersRelatedWithPostType($post_type);

            $allIndexedFilters      = get_option('wpc_seo_rules_settings', []);
            $filtersOrder           = get_option('wpc_filter_permalinks', []);
            $sortedIndexedFilters   = [];

            // Sort filters in the same order as in URL
            foreach ( (array) $filtersOrder as $entityKey => $slug) {
                foreach ( (array) $allIndexedFilters as $postType_EntityKey => $status) {
                    $itemKey = explode(":", $postType_EntityKey, 2);
                    $maybePostType = $itemKey[0]; // post|product etc

                    if (mb_strpos($postType_EntityKey, $entityKey) !== false && ($maybePostType === $post_type)) {
                        $sortedIndexedFilters[$postType_EntityKey] = $status;
                        break;
                    }

                }
            }

            /**
             * @bug if there are no filter post_tag, but post_tag enabled as indexed entity in Global settings
             * we can not show it as field to select terms for SEO rule
             */
            foreach ($sortedIndexedFilters as $item => $value) {
                $itemKey = explode(":", $item, 2);
                $maybePostType = $itemKey[0]; // post|product etc

                if ($maybePostType === $post_type && $value === 'on') {
                    $indexedFilters[] = $em->prepareFilterCommon($itemKey[1], $relatedFilters);
                }
            }

            $container->storeParam( $key, $indexedFilters );
        }

        return ( $indexedFilters ) ? $indexedFilters : [];
    }


    /**
     * @return array configuration array for all indexed filters for current post type
     */
    public function getIntersectionFields()
    {
        $indexedFilters = $this->getIndexedFilters();

        // We need to get full filter info there
        $em = Container::instance()->getEntityManager();
        $intersectionFields = [];

        if( $indexedFilters ) {

            foreach ($indexedFilters as $filter) {

                if (empty($filter)) {
                    continue;
                }

                $entity = $em->getEntityByFilter($filter, $this->getPostType());

                $terms = [];
                $terms[$this->noValue] = sprintf(esc_html__('Without %s', 'filter-everything'), $filter['title']);
                $terms[$this->anyValue] = sprintf(esc_html__('Any %s', 'filter-everything'), $filter['title']);

                /**
                 * @feature Add post_meta terms that belongs this post type only. If current post type
                 * doesn't have post meta terms, but another post types have them, all them are in dropdown for
                 * SEO rule.
                 */
                $terms += $entity->getTermsForSelect();

                $field = array(
                    'type' => 'Select',
                    'label' => $filter['label'],
                    'title' => $filter['title'],
                    'slug' => $filter['slug'],
                    'name' => $this->generateInputName($filter['e_name'], 'filter'),
                    'id' => $this->generateInputID($filter['e_name']),
                    'class' => 'wpc-field-rule-filter',
                    'options' => $terms,
                    'default' => '0',
                    'instructions' => sprintf(__('Select %s', 'filter-everything'), $filter['label'])
                );

                $intersectionFields[$filter['e_name']] = $field;
            }
        }

        if( $wp_entity = $this->getWpNativeEntitiesFields() ){
            $intersectionFields = array_merge( array( 'wp_entity' => $wp_entity ), $intersectionFields );
        }

        return $intersectionFields;
    }

    /**
     * @param string $postType
     * @return array|false
     */
    public function getWpNativeEntitiesFields( $postType = '' )
    {
        if( ! $postType ){
            $postType = $this->getPostType();
        }

        $input  = false;
        $fields = [];
        $em     = Container::instance()->getEntityManager();

        $indexedEnames = [];
        $indexedFilters = $this->getIndexedFilters();

        if( $indexedFilters ){
            foreach ( $indexedFilters as $filter ) {
                $indexedEnames[] = $filter['e_name'];
            }
        }

        $args = array(
            'public' => true,
            'rewrite' => true
        );

        $excluded_taxonomies    = flrt_excluded_taxonomies();
        $excluded               = array_merge( $excluded_taxonomies, $indexedEnames );
        $taxonomies             = get_taxonomies( $args, 'objects' );

        foreach ( $taxonomies as $t => $taxonomy ) {

            if( ! in_array( $postType, $taxonomy->object_type ) ){
                continue;
            }

            if ( ! in_array( $taxonomy->name, $excluded ) ) {

                $label  = ucwords( flrt_ucfirst( mb_strtolower( $taxonomy->label ) ) );

                $terms = [];
                $terms[$taxonomy->name . ":-1"] = sprintf(esc_html__('Any %s', 'filter-everything'), $taxonomy->labels->singular_name);

                $terms += $em->getTaxonomyTermsForDropdown( $taxonomy->name, true);
                $fields[$taxonomy->name] = array(
                    'group_label' => $label,
                    'entities' => $terms
                );

            }
        }

        // Do not allow create SEO rule for custom posts author's page
        if( ! in_array( 'author', $indexedEnames ) && $postType === 'post' ){

            $terms = [];
            $terms["author:-1"] = esc_html__('Any Author', 'filter-everything');
            $terms += $em->getAuthorTermsForDropdown( true );

            $fields['author'] = array(
                'group_label' => esc_html__( 'Author', 'filter-everything' ),
                'entities' => $terms
            );
        }

        if( ! empty( $fields ) ){

            $noSelection = array(
                'no_selection' => array(
                    'group_label' => esc_html__('No archive page', 'filter-everything'),
                    'entities' => array(
                        '0' => esc_html__('Without archive page', 'filter-everything')
                    )
                )
            );

            $fields = array_merge( $noSelection, $fields );

            $input = array(
                'type'      => 'Select',
                'label'     => '{archive_title}',
                'title'     => esc_html__( 'Page archive for:', 'filter-everything' ),
                'slug'      => 'archive_title',
                'name'      => $this->generateInputName( 'wp_entity' ),
                'id'        => $this->generateInputID( 'wp_entity' ),
                'class'     => 'wpc-field-rule-wp-entity',
                'options'   => $fields,
                'default'   => '0',
                'instructions' => esc_html__('Include WordPress page for SEO rule', 'filter-everything')
            );
        }

        return $input;
    }

    private function addRuleFilterFields()
    {
        foreach( $this->getIntersectionFields() as $key => $field ){
            $this->addField( $key, $field );
        }
    }

    // When loading page with form only new rule requires GET parameters to setup new rule
    // Existing rule has information about selected values in the rule key
    // And existing rule values has bigger priority, than GET parameters
    public function getRuleInputs( $post_id = 0 )
    {
        $this->setupDefaultFields();

        if( $post_id ){
            $ruleData = $this->getRuleData( $post_id );

            if( isset( $ruleData['rule_post_type'] ) ){
                $this->setPostType($ruleData['rule_post_type']);
                $this->addRuleFilterFields();
                $this->populateRuleFields($ruleData);

            }else if( $has = $this->getPostTypesWithIndexedFilters() ) {
                $this->setPostType( array_key_first( $has ) );
                $this->addRuleFilterFields();
            }

        }

        return $this->getDefaultFields();

    }

    public function getRuleData( $post_id )
    {
        $ruleData = [];
        $data = $this->queryRule( $post_id );
        $dataUnserialized = maybe_unserialize( $data->post_excerpt );

        /**
         * @feature remove this bullshit with setting up default fields and setPostType
         */
        $this->setupDefaultFields();

        foreach( $this->getDefaultFields() as $key => $field ){
            if( isset( $field['particular'] ) && $field['particular'] ){
                $fieldName = $field['particular'];
                $ruleData[$key] = $data->$fieldName;
            }else if ( isset( $dataUnserialized[$key] ) ) {
                $ruleData[$key] = $dataUnserialized[$key];
                unset( $dataUnserialized[$key] );
            }
        }

        $ruleData = $this->convertEntitiesAndSeoSlugs( $ruleData, 'show' );

        if( ! empty( $dataUnserialized ) ){
            return array_merge( $ruleData, $dataUnserialized );
        }

        return $ruleData;
    }

    private function populateRuleFields( $ruleData )
    {
        foreach ( $this->getDefaultFields() as $key => $field ){
            if( isset( $ruleData[$key] ) && $ruleData[$key] ){
                $this->setFieldValue( $key, $ruleData[$key] );
            }
        }
    }

    /**
     * @param $fieldKey
     * @param $fieldValue
     * @return bool
     */
    private function setFieldValue( $fieldKey, $fieldValue ){
        if( isset( $this->defaultFields[$fieldKey] ) ){
            $this->defaultFields[$fieldKey]['value'] = $fieldValue;
            return true;
        }
        return false;
    }

    private function addField( $fieldKey, $field )
    {
        if( ! isset( $this->defaultFields[$fieldKey] ) ){
            $this->defaultFields[$fieldKey] = $field;
            return true;
        }
        return false;
    }

    private function queryRule( $post_id )
    {
        global $wpdb;
        $sql[] = "SELECT {$wpdb->posts}.ID, {$wpdb->posts}.post_name, {$wpdb->posts}.post_content, {$wpdb->posts}.post_excerpt";
        $sql[] = "FROM {$wpdb->posts}";
        $sql[] = "WHERE {$wpdb->posts}.ID = %s";
        $sql[] = "LIMIT 0, 1";

        $sql = implode(" ", $sql);
        $result = $wpdb->get_row( $wpdb->prepare( $sql, $post_id ) );

        return $result;
    }
    /**
     * @param array $keys
     * @return array|false
     */
    public function getRules( $keys = [] )
    {
        $args = array(
            'post_type'      => FLRT_SEO_RULES_POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'post_name__in'  => $keys,
            'orderby'        => 'post_name__in'
        );

        $rulesQuery = new \WP_Query();
        $rulesQuery->parse_query($args);
        $rulesPosts = $rulesQuery->get_posts();

        if( ! empty( $rulesPosts ) ){
            $rules = [];

            foreach ( $rulesPosts as $rulesPost ){
                $rules[] = array(
                    'ID'           => (string) $rulesPost->ID,
                    'post_name'    => $rulesPost->post_name,
                    'post_content' => $rulesPost->post_content,
                    'post_excerpt' => $rulesPost->post_excerpt,
                );
            }

            return $rules;

        }

        return false;
    }

    public function saveRule( $post_id, $post )
    {
        $container = Container::instance();
        $postData  = $container->getThePost();

        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if( wp_is_post_revision( $post_id ) ) {
            return $post_id;
        }

        if( $post->post_type !== FLRT_SEO_RULES_POST_TYPE ) {
            return $post_id;
        }

        $nonce = filter_input( INPUT_POST, '_flrt_nonce' );

        if( ! $this->verifyNonce( $nonce ) ) {
            return $post_id;
        }

        if( ! current_user_can( 'manage_options' ) ) {
            return $post_id;
        }

        remove_action( 'save_post', array( $this, 'saveRule' ), 10, 2 );

        $allRulesValid  = true;
        $filterFields   = $container->getFilterFieldsService();

        // save fields
        if( isset( $postData['wpc_seo_rules'] ) && ! empty( $postData['wpc_seo_rules'] ) ) {
            $postData['wpc_seo_rules']['ID'] = $post_id;

            if( ! $this->validateRuleFields( $postData['wpc_seo_rules'] ) ){
                $allRulesValid = false;
            }

            if( $allRulesValid ){
                $this->saveRuleFields( $postData['wpc_seo_rules'] );
            } else {
                flrt_redirect_to_error( $post_id, $filterFields->getErrorCodes() );
            }

        }

        add_action( 'save_post', array( $this, 'saveRule' ), 10, 2 );

        return $post_id;
    }

    private function saveRuleFields( $ruleFields ){
        $ruleFields = $this->sanitizeFields( $ruleFields );
        $ruleFields = wp_unslash( $ruleFields );

        // Backup of fields
        $_ruleFields    = $ruleFields;
        $filterFields   = flrt_extract_vars($_ruleFields, array('filter') );
        $wp_entity      = flrt_extract_vars($_ruleFields, array('wp_entity') );
        $wpEntity       = reset($wp_entity);
        $ruleDesc       = flrt_extract_vars($_ruleFields, array('rule_description') );

        // Remove elements, that shouldn't be serialized
        flrt_extract_vars( $_ruleFields, array( 'ID' ) );
        $this->setPostType( $_ruleFields['rule_post_type'] );

        $ruleFilters = $this->getRuleFilters( $filterFields['filter'], $_ruleFields['rule_post_type'] );
        $to_serialize = array_merge( $_ruleFields, $filterFields['filter'], $wp_entity );

        $ruleDesc     = $this->convertEntitiesAndSeoSlugs( $ruleDesc, 'save' );
        $to_serialize = $this->convertEntitiesAndSeoSlugs( $to_serialize, 'save' );

        $ruleKey = $this->generateRuleKey( $ruleFilters, $wpEntity );

        // Create array of data to save.
        $to_save = array(
            'ID'			=> $ruleFields['ID'],
            'post_status'	=> 'publish',
            'post_type'		=> FLRT_SEO_RULES_POST_TYPE,
            'post_title'	=> $this->generateRuleTitle( $ruleFilters, $wpEntity ),
            'post_name'     => $ruleKey,
            'post_content'	=> $ruleDesc['rule_description'],
            'post_excerpt'  => maybe_serialize( $to_serialize )
        );

        // Unhook wp_targeted_link_rel() filter from WP 5.1 corrupting serialized data.
        remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );

        $to_save = wp_slash( $to_save );

        // Update or Insert.
        if( $ruleFields['ID'] ) {
            wp_update_post( $to_save );
        } else	{
            $ruleFields['ID'] = wp_insert_post( $to_save );
        }

        // Update meta_fields
        update_post_meta( $ruleFields['ID'], 'wpc_seo_rule_post_type', $_ruleFields['rule_post_type'] );

        return $ruleFields;
    }

    private function getRuleFilters( $eNames, $postType )
    {
        $filters    = [];
        if( ! $eNames ){
            return $filters;
        }
        $em         = Container::instance()->getEntityManager();

        foreach( $eNames as $e_name => $term_id_or_slug ){

            $filters[ $e_name ] = $em->getCommonFilterValues( $e_name, $postType );

            if( $term_id_or_slug == $this->anyValue ){
                $filters[ $e_name ]['values'][] = $this->getAnyTitle();
                $filters[ $e_name ]['name']     = 'Any';
            } else {
                $entity = $em->getEntityByFilter( $filters[$e_name] );
                $term   = $entity->getTerm( $term_id_or_slug );
                $filters[ $e_name ]['values'][] = $term->slug;
                $filters[ $e_name ]['name']     = $term->name;
            }
        }

        return $filters;
    }

    private function convertEntitiesAndSeoSlugs( $ruleFields, $action = 'save' )
    {
        $entities = [];
        $slugs = [];

        if( isset( $ruleFields['rule_post_type'] ) ){
            $this->setPostType( $ruleFields['rule_post_type'] );
        }

        $seoEntitiesAndSlugs = apply_filters( 'wpc_seo_entities_and_slugs', $this->getIntersectionFields() );

        foreach( $seoEntitiesAndSlugs as $fieldIndex => $field ){
            $entities[]  = '{'.$fieldIndex.'}';
            $slugs[] = '{'.$field['slug'].'}';
        }

        $ruleSeoFields = array(
            'rule_h1',
            'rule_seo_title',
            'rule_meta_desc',
            'rule_description'
        );

        foreach ( $ruleSeoFields as $ruleSeoField ) {
            if( $action === 'save' ){
                if( isset( $ruleFields[ $ruleSeoField ] ) ){
                    $ruleFields[ $ruleSeoField ] = str_replace( $slugs, $entities, $ruleFields[ $ruleSeoField ] );
                }
            }
            if( $action === 'show' ){
                if( isset( $ruleFields[ $ruleSeoField ] ) ){
                    $ruleFields[ $ruleSeoField ] = str_replace( $entities, $slugs, $ruleFields[ $ruleSeoField ] );
                }
            }
        }

        return $ruleFields;
    }

    /**
     * @param $wpEntity
     * @return string relateive link URL or empty string
     */
    private function getWpEntityRelativeLink( $wpEntity )
    {
        $link = '';
        if( ! $wpEntity ){
            return $link = '';
        }

        list( $entity, $id ) = explode( ":", $wpEntity, 2 );

        if( $id > 0 ){

            switch( $entity ){
                case 'author':
                    $url = get_author_posts_url( $id );
                break;
                case 'data':
                    // Maybe in future
                    $url = '';
                break;
                default:
                    $url = get_term_link( (int) $id, $entity );
                break;
            }

            $url = untrailingslashit( $url );

        }else if( $id == -1 ){

            switch( $entity ){
                case 'author':
                    global $wp_rewrite;
                    $authorlink = $wp_rewrite->get_author_permastruct();
                    $url = str_replace( "%author%", $this->getAnyTitle(), $authorlink );
                    break;
                case 'data':
                    // Maybe in future
                    $url = '';
                    break;
                default:
                    global $wp_rewrite;
                    $termlink = $wp_rewrite->get_extra_permastruct( $entity );
                    $url = str_replace( "%$entity%", $this->getAnyTitle(), $termlink );
                    break;
            }

        }

        if( ! is_wp_error( $url ) ){
            $link = str_replace( home_url(), '', $url );
        }

        return trim($link, '/');
    }

    /**
     * Ok for Polylang free
     */
    private function generateRuleTitle( $filters, $wp_entity = '' )
    {
        $name = [];

        if( $wp_entity ){
            $name[] = $this->getWpEntityRelativeLink( $wp_entity );
        }

        foreach( $filters as $e_name => $filter ){
            $name[] = $filter['slug'] . '-' . reset($filter['values'] );
        }

        $title = implode('/', $name );

        if( flrt_wpml_active() && defined( 'ICL_LANGUAGE_CODE' ) ){

            $title      = apply_filters( 'wpml_permalink', home_url($title), ICL_LANGUAGE_CODE );
            $home_url   = get_option('siteurl');

            $wpml_url_format = apply_filters( 'wpml_setting', 0, 'language_negotiation_type' );

            if( $wpml_url_format === '2' ){
                $home_url = apply_filters( 'wpml_home_url', home_url() );
            }
            // In case if WPML configured to /?lang=uk URLs
            if( $wpml_url_format === '3' ){
                $home_url = apply_filters( 'wpml_home_url', home_url() );
                $home_url = remove_query_arg('lang', $home_url);
            }

        }else{
            $home_url = home_url();
        }

        $title = str_replace( $home_url, '', $title );

        return '.../' . trim($title, '/');
    }

    public function generateRuleKey( $filters, $wpEntity = '' )
    {
        $sections[] = $this->getPostType();

        /**
         * @bug now /author/admin and /author-admin have the same rule key
         */
        foreach ( $filters as $slug => $maybeWpEntity ) {
            if( isset( $maybeWpEntity['wp_entity'] ) && $maybeWpEntity['wp_entity']  ){
                $sections[] = $this->generateRuleSection( $maybeWpEntity, true );
                unset( $filters[$slug] );
                break;
            }
        }

        if( $wpEntity ){
            $sections[] = '@-' . str_replace(":", $this->sep, $wpEntity );
        }

        // To make single global order for all rules
        usort( $filters, $this->compareFilterEntitiesAndEnames() );

        foreach( $filters as $filter ){
            $sections[] = $this->generateRuleSection( $filter );
        }

        // E.g. product/product_cat#19/product_tag#51/pa_size#26/pa_color#-1
        $result = implode( $this->sectionSep, $sections );

        return md5( $result );
    }

    private function generateRuleSection( $filter, $wp_entity = false ){
        $section = $filter['e_name'];
        if( $wp_entity ){
            $section = '@-' . $section;
        }
        $value   = reset( $filter['values'] );

        if( $value === $this->anyTitle ){
            $section .= $this->sep . $this->anyValue;
            return $section;
        }

        $em     = Container::instance()->getEntityManager();
        $entity = $em->getEntityByFilter($filter);

        if( $filter['entity'] === 'taxonomy' ){

            $termId = $entity->getTermId($value);
            $section .= $this->sep . $termId;

        }else if( $filter['entity'] === 'author' ){

            $authorId = $entity->getTermId($value);
            $section .= $this->sep . $authorId;

        }else if( in_array( $filter['entity'], array('post_meta', 'post_meta_exists') ) ) {

            $section .= $this->sep . $value;

        }else{
            $section = '';
        }

        return $section;
    }

    private function validateRuleFields( $ruleFields )
    {
        $valid          = true;
        $filterFields   = Container::instance()->getFilterFieldsService();

        // Check permissions
        if( ! current_user_can( 'manage_options' ) ) {
            $filterFields->pushError(202);
            $valid = false;
        }
        /**
         * @todo validate Rule Fields !!! IMPORTANT
         *
         * 1. Do not allow save rule without filters wpc_seo_rules[filter] empty
         * 2. Do not allow save rule with existing intersection (the same post_name)
         * 3. Do not allow save rule with empty all SEO data
         */
        // Validate rule post type
        if( isset( $ruleFields['rule_post_type'] ) ){
            $hasIndexedFilters = array_keys( $this->getPostTypesWithIndexedFilters() );

            if( ! in_array( $ruleFields['rule_post_type'], $hasIndexedFilters, true ) ){
                $filterFields->pushError(21); // Invalid post type
                return false;
            }

        }else{
            $filterFields->pushError(50); // Empty Post Type
            $valid = false;
        }

        if( isset( $ruleFields['filter'] ) ){
            if(  ! is_array( $ruleFields['filter'] ) ){

                $filterFields->pushError(201); // Common error
                $valid = false;

            }else{

                $this->setPostType($ruleFields['rule_post_type']);
                $intersectionFilters = array_keys( $this->getIntersectionFields() );
                $allZeroValues = true;

                foreach ( $ruleFields['filter'] as $filter => $value ){
                    if( ! in_array( $filter, $intersectionFilters, true ) ){
                        $filterFields->pushError(53); // Forbidden filter
                        $valid = false;
                    }

                    if( $value !== '0' ){
                        $allZeroValues = false;
                    }
                }

                if( $allZeroValues ){
                    $filterFields->pushError(51); // No filters selected
                    $valid = false;
                }

            }

        } else {
            $filterFields->pushError(51); // No filters
            $valid = false;
        }

        // All SEO fields are empty
        if( ! $ruleFields['rule_h1']
            &&
            ! $ruleFields['rule_seo_title']
            &&
            ! $ruleFields['rule_meta_desc']
            &&
            ! $ruleFields['rule_description'] ){

            $filterFields->pushError(52); // No SEO data
            $valid = false;
        }

        /**
         *  Validate ID
         */
        if( isset( $ruleFields['ID'] ) ){

            $savedRule = get_post( $ruleFields['ID'] );

            // Other post type
            if( ! $savedRule || ! isset( $savedRule->post_type ) || $savedRule->post_type !== FLRT_SEO_RULES_POST_TYPE ){
                $filterFields->pushError(54); // Invalid filter ID
                $valid = false;
            }

        } else {
            $filterFields->pushError(54); // Invalid filter ID
            $valid = false;
        }

        /**
         * Validate existing rule
         */

        $ruleFields 	    = $this->sanitizeFields( $ruleFields );
        $ruleFields 	    = wp_unslash( $ruleFields );
        $_ruleFields        = $ruleFields;
        $onlyfilterFields   = flrt_extract_vars($_ruleFields, array('filter') );
        $wp_entity          = flrt_extract_vars($_ruleFields, array('wp_entity') );
        $wpEntity           = reset($wp_entity);

        if( ! empty( $onlyfilterFields ) ) {
            $ruleFilters = $this->getRuleFilters($onlyfilterFields['filter'], $_ruleFields['rule_post_type']);
            $ruleKey = $this->generateRuleKey($ruleFilters, $wpEntity);

            global $wpdb;

            $sql[] = "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts}";

            if( flrt_wpml_active() && defined( 'ICL_LANGUAGE_CODE' ) ){
                $sql[] = "LEFT JOIN {$wpdb->prefix}icl_translations AS wpml_translations";
                $sql[] = "ON {$wpdb->posts}.ID = wpml_translations.element_id";

                $sql[] = "AND wpml_translations.element_type IN(";
                $LANG_IN[] = $wpdb->prepare( "CONCAT('post_', '%s')", $_ruleFields['rule_post_type'] );
                $sql[] = implode(",", $LANG_IN );
                $sql[] = ")";
            }

            $sql[] = "WHERE 1=1";
            $sql[] = $wpdb->prepare( "AND {$wpdb->posts}.post_name = '%s'", $ruleKey);

            if( flrt_wpml_active() && defined( 'ICL_LANGUAGE_CODE' ) ){
                $sql[] = $wpdb->prepare("AND wpml_translations.language_code = '%s'", ICL_LANGUAGE_CODE);
            }

            $query = implode(' ', $sql);

            $the_post = $wpdb->get_row($query);

            if (isset($the_post->ID)) {
                $post_id = intval($the_post->ID);
                $rule_id = intval($ruleFields['ID']);

                if ($post_id > 0 && $post_id !== $rule_id) {
                    $filterFields->pushError(55); // SEO Rule already exists
                    $valid = false;
                }
            }
        }

        return apply_filters( 'wpc_validate_seo_rules', $valid, $filterFields );
    }

    private function sanitizeFields( $ruleFields )
    {
        $_ruleFields = $ruleFields;
        foreach( $_ruleFields['filter'] as $e_name => $value ){
            if( $value === '0' ){
                unset( $ruleFields['filter'][$e_name] );
            }
        }

        return $ruleFields;
    }

    public function getAnyTitle()
    {
        return $this->anyTitle;
    }

    public function includeAdminJs()
    {
        $screen = get_current_screen();

        if( ! is_null( $screen ) && property_exists( $screen, 'id' ) && $screen->id === FLRT_SEO_RULES_POST_TYPE ){
            global $post_id;

            $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
            $ver    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? rand(0, 1000) : FLRT_PLUGIN_VER;

            // Disable autosavings
            wp_dequeue_script( 'autosave' );

            wp_enqueue_script('wpc-filters-admin-seo-rules', FLRT_PLUGIN_DIR_URL . 'assets/js/wpc-seo-rules-admin'.$suffix.'.js', array('jquery', 'wp-util'), $ver );

            $this->setPostType( 'post' );

            if( $post_id ){
                $ruleData = $this->getRuleData($post_id);
                $postType = isset( $ruleData['rule_post_type'] ) ? $ruleData['rule_post_type'] : '';
                $this->setPostType( $postType );
            } elseif( $has = $this->getPostTypesWithIndexedFilters() ) {
                $this->setPostType( array_key_first( $has ) );
            }

            $fields     = $this->getIntersectionFields();
            $seoVars    = $this->createSeoVarsList( $fields );

            $l10n = array(
                'seovars' => $seoVars,
                'noSeoVarsMsg' => esc_html__( 'No SEO vars', 'filter-everything' )
            );

            wp_localize_script( 'wpc-filters-admin-seo-rules', 'wpcSeoVars', $l10n );
        }
    }

    private function compareFilterEntitiesAndEnames(){
        return function ($a, $b) {

            $value_1 = $a['entity'] . $a['e_name'];
            $value_2 = $b['entity'] . $b['e_name'];

            $result = strcmp($value_1, $value_2);

            if ( $result === 0 ) {
                return 0;
            }

            return ($result > 0) ? +1 : -1;
        };
    }

    public function generateInputName( $key, $subKey = '' )
    {
        $name = self::SEO_RULE_KEY;

        if( $subKey ){
            $name .= '['.$subKey.']';
        }
        $name .= '['. $key . ']';

        return $name;
    }

    public function generateInputID( $key )
    {
        return self::SEO_RULE_KEY . '-' . $key;
    }

    public static function createNonce()
    {
        return wp_create_nonce( self::NONCE_ACTION );
    }

    private function verifyNonce( $nonce )
    {
        return wp_verify_nonce( $nonce, self::NONCE_ACTION );
    }
}

$seoRules = new SeoRules();
if( is_admin() ){
    $seoRules->registerHooks();
}