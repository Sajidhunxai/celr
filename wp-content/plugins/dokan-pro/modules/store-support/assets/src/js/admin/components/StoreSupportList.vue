<template>
    <div class="support-ticket-list">
        <ul class="subsubsub">
            <li><router-link :class="currentTab()" :to="{ name: 'AdminStoreSupport', query: { status: 'open' }}" active-class="current" exact >{{ __( 'Open', 'dokan' ) }} <span class="count">{{ counts.open }}</span></router-link> | </li>
            <li><router-link :class="currentTab('closed' )" :to="{ name: 'AdminStoreSupport', query: { status: 'closed' }}" active-class="current" exact >{{ __( 'Closed', 'dokan' ) }} <span class="count">{{ counts.closed }}</span></router-link> | </li>
            <li><router-link :class="currentTab('all' )" :to="{ name: 'AdminStoreSupport', query: { status: 'all' }}" active-class="current" exact >{{ __( 'All', 'dokan' ) }} <span class="count">{{ counts.all }}</span></router-link> | </li>
        </ul>

        <search :title="__( 'Search ticket', 'dokan' )" @searched="doSearch"></search>

        <list-table
            :columns="columns"
            :rows="supportTickets"
            :notFound="nothingFound"
            :show-cb="showCb"
            :actions="actions"
            :bulk-actions="bulkActions"
            :loading="loading"
            :total-pages="totalPages"
            :per-page="perPage"
            :current-page="currentPage"
            :total-items="totalItems"
            :index="index"
            :sortOrder="sortOrder"
            :sortBy="sortBy"

            @pagination="goToPage"
            @bulk:click="onBulkAction"
            @searched="doSearch"
            @sort="sortCallback"
        >
            <template slot="ID" slot-scope="data">
                <router-link :to="{ name: 'AdminStoreSupport', query: { page_type: 'single', topic: data.row.ID, vendor_id: data.row.vendor_id, vendor_id: data.row.vendor_id }}">
                    <strong :class="'no' === data.row.reading ? 'not_read' : ''">#{{ data.row.ID }}</strong>
                </router-link>
            </template>

            <template slot="post_title" slot-scope="data">
                <router-link :to="{ name: 'AdminStoreSupport', query: { page_type: 'single', topic: data.row.ID, vendor_id: data.row.vendor_id }}">
                    <strong :class="'no' === data.row.reading ? 'not_read' : ''">{{ data.row.post_title }}</strong>
                </router-link>
            </template>

            <template slot="vendor_name" slot-scope="data">
                <router-link :to="'/vendors/' + data.row.vendor_id">
                    <strong :class="'no' === data.row.reading ? 'not_read' : ''" >{{ data.row.vendor_name }}</strong>
                </router-link>
            </template>

            <template slot="customer_name" slot-scope="data">
                <span :class="'no' === data.row.reading ? 'not_read' : ''">{{ data.row.customer_name }}</span>
            </template>

            <template slot="ticket_date" slot-scope="data">
                <span :class="'no' === data.row.reading ? 'not_read' : ''">{{ data.row.ticket_date }}</span>
            </template>

            <template slot="post_status" slot-scope="data">
                <span class="dokan-label" :class="'closed' === data.row.post_status ? 'dokan-label-danger' : 'dokan-label-success'">{{ data.row.post_status }}</span>
            </template>

            <template slot="action" slot-scope="data">
                <router-link :to="{ name: 'AdminStoreSupport', query: { page_type: 'single', topic: data.row.ID, vendor_id: data.row.vendor_id }}">
                    <span class="dashicons dashicons-visibility"></span>
                </router-link>
            </template>

            <template slot="filters">
                <span class="form-group">
                    <select
                        id="filter-vendors"
                        style="width: 120px;"
                        :data-placeholder="__( 'Filter by vendor', 'dokan' )"
                    />
                    <button
                        v-if="filter.vendor_id"
                        type="button"
                        class="button"
                        @click="filter.vendor_id = 0"
                    >&times;</button>
                </span>
                <span class="form-group">
                    <select
                        id="filter-customers"
                        style="width: 135px;"
                        :data-placeholder="__( 'Filter by customer', 'dokan' )"
                    />
                    <button
                        v-if="filter.customer_id"
                        type="button"
                        class="button"
                        @click="filter.customer_id = 0"
                    >&times;</button>
                </span>
                <span class="form-group">
                    <label for="to">{{ __( 'From', 'dokan' ) }} :</label>
                    <datepicker class="dokan-input admin-support-filter-date-picker" :value="from_date" format="yy-mm-d" v-model="from_date"></datepicker>

                    <label for="to">{{ __( 'To', 'dokan' ) }} :</label>
                    <datepicker class="dokan-input admin-support-filter-date-picker" :value="from_date" format="yy-mm-d" v-model="to_date"></datepicker>

                    <button @click="clickFilterSupportTickets" type="submit" class="button">{{ __( 'Filter', 'dokan' ) }}</button>
                </span>
            </template>

        </list-table>
    </div>
</template>

<script>
let ListTable = dokan_get_lib('ListTable');
let Search = dokan_get_lib('Search');
let Datepicker = dokan_get_lib('Datepicker');

import $ from 'jquery';

export default {
    name: 'StoreSupportList',

    components: {
        ListTable,
        Search,
        Datepicker
    },

    data() {
        return {
            from_date: '',
            to_date: '',
            showCb: true,
            totalItems: 0,
            perPage: 20,
            totalPages: 1,
            loading: false,
            currentPage: 1,
            index: "ID",
            counts: {
                all: 0,
                closed: 0,
                open: 0
            },
            actions: [],
            bulkActions: [
                {
                    key: 'close',
                    label: 'Close'
                }
            ],
            filter:{
                vendor_id: 0,
                customer_id: 0,
            },
            nothingFound: this.__( 'No tickets found.', 'dokan' ),
            columns: {
                'ID': {
                    label: this.__( 'Topic', 'dokan' ),
                    sortable: true,
                },
                'post_title': {
                    label: this.__( 'Title', 'dokan' ),
                },
                'vendor_name': {
                    label: this.__( 'Vendor', 'dokan' ),
                },
                'customer_name': {
                    label: this.__( 'Customer', 'dokan' ),
                },
                'post_status': {
                    label: this.__( 'Status', 'dokan' ),
                },
                'ticket_date': {
                    label: this.__( 'Date', 'dokan' ),
                    sortable: true,
                },
                'action': {
                    label: this.__( 'Action', 'dokan' ),
                },
            },
            supportTickets: [],
            currentStatus: '',
            sortBy: 'ID',
            sortOrder: 'desc',
        }
    },

    created() {
        this.fetchAllSupportTickets();
    },

    methods: {
        currentTab( tab = 'open' ) {
            return tab === this.currentStatus ? 'current' : '';
        },

        fetchAllSupportTickets( args = {} ){
            let self = this;

            self.loading = true;
            this.currentStatus = this.$route.query.status || 'open';

            const data = {
                ...args,
                per_page: self.perPage,
                page: self.currentPage,
                post_status: this.$route.query.status || 'open',
                orderby : 'ID' === this.sortBy ? this.sortBy : 'date',
                order : 'asc' === this.sortOrder ? 'ASC' : 'DESC',
            };

            dokan.api.get('/admin/support-ticket', data)
                .done((response, status, xhr) => {
                    self.supportTickets = response;
                    self.loading = false;

                    self.updatedCounts(xhr);
                    self.updatePagination(xhr);
            });
        },

        updatedCounts(xhr) {
            this.counts.all    = parseInt( xhr.getResponseHeader('X-Status-All') );
            this.counts.closed = parseInt( xhr.getResponseHeader('X-Status-Closed') );
            this.counts.open   = parseInt( xhr.getResponseHeader('X-Status-Open') );
            this.updateUnreadTicketCounter( xhr.getResponseHeader('X-Ticket-Unread') );
        },

        updateUnreadTicketCounter( unread ) {
            let badge      = $('.dokan-unread-ticket-count-in-list');
            let badgeCount = $('.dokan-unread-ticket-count-badge-in-list');

            badgeCount.html(unread);
            unread > 0 ? badge.show() : badge.hide();
        },

        updatePagination(xhr) {
            this.totalPages = parseInt( xhr.getResponseHeader('X-WP-TotalPages') );
            this.totalItems = parseInt( xhr.getResponseHeader('X-WP-Total') );
        },

        goToPage(page){
            this.currentPage = page;
            this.fetchAllSupportTickets();
        },

        onBulkAction(action, items){
            if ( 'close' === action ) {
                let jsonData     = {};
                jsonData[action] =  items;

                this.loading = true;

                dokan.api.put('/admin/support-ticket/batch', jsonData)
                .done(response => {
                    this.loading = false;
                    this.fetchAllSupportTickets();
                });
            }
        },

        doSearch(payload){
            if ( '' !== payload ) {
                this.fetchAllSupportTickets( { search: payload } );
            } else {
                this.fetchAllSupportTickets();
            }
        },

        setRoute( query ) {
            this.$router.push( {
                name: 'AdminStoreSupport',
                query: query
            } );
        },

        clearSelection(element) {
            $(element).val(null).trigger('change');
        },

        getFromDate() {
            return moment().startOf( 'month' ).format( 'Y-M-D' );
        },

        getToDate() {
            return moment().endOf( 'month' ).format( 'Y-M-D' );
        },

        clickFilterSupportTickets(){
            let filter = {
                from_date: this.from_date,
                to_date: this.to_date,
                vendor_id: this.filter.vendor_id,
                customer_id: this.filter.customer_id
            }

            this.fetchAllSupportTickets( { filter: filter } );
        },

        sortCallback(column, order) {
            this.sortBy = column;
            this.sortOrder = order;

            this.fetchAllSupportTickets();
        },
    },

    watch: {
        '$route.query.status'() {
            this.currentPage = 1;
            this.filter.vendor_id = 0;
            this.filter.customer_id = 0;
            this.from_date = '';
            this.to_date = '';

            this.fetchAllSupportTickets();
        },

        'filter.vendor_id'(vendor_id) {
            if ( 0 === vendor_id ) {
                this.clearSelection('#filter-vendors');
            }
        },

        'filter.customer_id'(customer_id) {
            if ( 0 === customer_id ) {
                this.clearSelection('#filter-customers');
            }
        }
    },

    mounted() {
        const self = this;

        $('#filter-vendors').selectWoo({
            ajax: {
                url: "".concat(dokan.rest.root, "dokan/v1/stores"),
                dataType: 'json',
                headers: {
                    "X-WP-Nonce" : dokan.rest.nonce
                },
                data(params) {
                    return {
                        search: params.term
                    };
                },
                processResults(data) {
                    return {
                        results: data.map((store) => {
                            return {
                                id: store.id,
                                text: store.store_name
                            };
                        })
                    };
                }
            }
        });

        $('#filter-vendors').on('select2:select', (e) => {
            self.filter.vendor_id = e.params.data.id;
        });

        $('#filter-customers').selectWoo({
            ajax: {
                url: "".concat(dokan.rest.root, "dokan/v1/admin/support-ticket/customers"),
                dataType: 'json',
                headers: {
                    "X-WP-Nonce" : dokan.rest.nonce
                },
                data(params) {
                    return {
                        search: params.term
                    };
                },
                processResults(data) {
                    return {
                        results: data.map((data) => {
                            return {
                                id: data.ID,
                                text: data.display_name
                            };
                        })
                    };
                }
            },
            delay: 250
        });

        $('#filter-customers').on('select2:select', (e) => {
            self.filter.customer_id = e.params.data.id;
        });
    },
}
</script>

<style scoped>
    .dokan-label {
        display: inline;
        padding: 0.2em 0.6em 0.3em;
        font-size: 75%;
        font-weight: bold;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25em;
    }
    .dokan-label-success {
        background-color: #5cb85c;
    }
    .dokan-label-danger {
        background-color: #d9534f;
    }

    .admin-support-filter-date-picker{
        width: 95px;
    }

    .not_read {
        font-weight: bold;
    }
</style>
