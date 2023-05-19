<template>
    <div class="admin-store-support-tickets">
        <h1 class="wp-heading-inline">
            <router-link :to="{ name: 'AdminStoreSupport' }" v-if="isSinglePage">
                <span class="back-to-tickets dashicons dashicons-arrow-left-alt2"></span>
                <span class="back-to-tickets ">{{ __( 'Back to ticket list', 'dokan' ) }}</span>
            </router-link>
            <span v-else>{{ __( 'Store Support', 'dokan' ) }}</span>
        </h1>

        <div class="help-block">
            <span class='help-text'><a href="https://wedevs.com/docs/dokan/modules/how-to-install-and-use-store-support/" target="_blank">{{ __( 'Need Any Help ?', 'dokan' ) }}</a></span>
            <span class="dashicons dashicons-smiley"></span>
        </div>

        <hr class="wp-header-end">

        <SingleSupportTicket v-if="isSinglePage"/>
        <StoreSupportList v-else/>
    </div>
</template>

<script>
import StoreSupportList from '../components/StoreSupportList.vue';
import SingleSupportTicket from '../components/SingleSupportTicket.vue';

export default {
    name: 'AdminStoreSupport',
    components: {
        StoreSupportList,
        SingleSupportTicket,
    },
    data() {
        return {
            isSinglePage: false,
        }
    },

    created() {
        if ( 'single' === this.$route.query.page_type ) {
            this.setSinglePage();
        } else {
            this.setSinglePage( false );
        }
    },

    watch: {
        '$route.query.page_type'() {
            if ( 'single' === this.$route.query.page_type ) {
                this.setSinglePage();
            } else {
                this.setSinglePage( false );
            }
        },
    },

    methods: {
        setSinglePage( singlePage = true ) {
            this.isSinglePage = singlePage
        },
    }
}
</script>

<style lang="less">
    .wp-heading-inline .dashicons-arrow-left-alt2{
        margin-top: 11px
    }
    .wp-heading-inline{
        .router-link-active{
            text-decoration: none;
        }
        .back-to-tickets{
            font-weight: 600;
            font-size: 13px;
            line-height: 18px;

            text-align: center;
            letter-spacing: 0.118182px;

            color: #929CA9;
        }
    }
    .admin-store-support-tickets {
        position: relative;

        .help-block {
            position: absolute;
            top: 10px;
            right: 10px;

            span.help-text {
                display: inline-block;
                margin-top: 4px;
                margin-right: 6px;
                a {
                    text-decoration: none;
                }
            }

            span.dashicons {
                font-size: 25px;
            }
        }
    }
</style>
