<template>
    <div class="dokan-admin-single-store-support-ticket">
        <div class="loading" v-if="loading">
            <loading></loading>
        </div>
        <div class="dokan-chat-wraper" v-else>
            <div class="dokan-chat-page-container">
                <div class="dokan-chat-container">
                    <div class="dokan-chat-header">
                        <div class="dokan-chat-title-box">
                            <span class="dokan-chat-title">{{ topic.post_title !== undefined ? topic.post_title : '' }}</span>
                        </div>
                        <div class="dokan-chat-status-box">
                            <span class="dokan-chat-status" v-bind:class="'open' === topic.post_status ? 'dokan-chat-open' : 'dokan-chat-closed'">{{ topic.post_status !== undefined ? ( 'open' === topic.post_status ? __( 'Opened', 'dokan') : __( 'Closed', 'dokan') ) : '' }}</span>
                        </div>
                        <div class="dokan-chat-header-blank-last"></div>
                    </div>

                    <div class="dokan-chat-box-container" ref="chatBoxContainer">

                        <!-- The ticket text created by customer. -->
                        <ChatBox
                            :image="undefined !== topic.avatar_url ? topic.avatar_url : ''"
                            :user="undefined !== topic.post_author_name ? topic.post_author_name : ''"
                            :comment="undefined !== topic.post_content ? topic.post_content : ''"
                            :date="undefined !== topic.post_date_formated ? topic.post_date_formated : ''"

                            :status="undefined !== topic.post_status ? topic.post_status : 'closed'"
                            :id="0"
                            v-on:deleteComment="deleteComment"
                            :userType="defaultUserType"
                        />

                        <!-- All the comments of vendor, admin and customer after created ticket. -->
                        <ChatBox
                            v-for="comment in comments" :key="comment.comment_ID"
                            :image="comment.comment_user_type.type === 'admin' ? site_image_url : comment.avatar_url"
                            :user="comment.comment_user_type.type === 'admin' ? site_title :comment.comment_author"
                            :comment="undefined !== comment.comment_content ? comment.comment_content : ''"
                            :date="undefined !== comment.comment_date_formated ? comment.comment_date_formated : ''"

                            :status="undefined !== topic.post_status ? topic.post_status : 'closed'"
                            :id="Number(comment.comment_ID)"
                            v-on:deleteComment="deleteComment"
                            :userType="comment.comment_user_type"
                        />

                    </div>
                    <div v-if="'open' === topic.post_status" class="dokan-chat-replay-container">

                        <div class="dokan-chat-sender-selection-box">
                            <select v-model="selected_user" name="sender" id="sender">
                                <option value="admin">{{ __( 'As Admin', 'dokan' ) }}</option>
                                <option value="vendor">{{ __( 'As Vendor', 'dokan' ) }}</option>
                            </select>
                        </div>
                        <div class="dokan-chat-replay-box">
                            <textarea v-model="reply_text" class="dokan-chat-replay" v-bind:placeholder="__( 'Write something', 'dokan' )"></textarea>
                        </div>
                        <div class="dokan-chat-send-box">
                            <button v-if="! loading && ! chatLoading" @click="sendReplay" :disabled="disableReplyButton()" class="dokan-send-replay">{{ __( 'Send', 'dokan' ) }}</button>
                            <button v-else-if="loading || chatLoading" class="dokan-send-replay">
                               <span>{{ __( 'Sending...', 'dokan' ) }}</span>
                            </button>
                        </div>
                    </div>
                    <div v-else class="dokan-chat-replay-container">
                        <div class="dokan-dot-parent">
                            <div class="dokan-dot-container">
                                <div class="dokan-dot"></div>
                                <div class="dokan-dot"></div>
                                <div class="dokan-dot"></div>
                            </div>
                        </div>
                        <div class="dokan-closed-message">{{ __( 'This ticket is closed.', 'dokan' ) }}</div>
                    </div>
                </div>

                <div class="dokan-chat-summary-container">
                    <div class="dokan-chat-summary-holder">
                        <div class="dokan-chat-summary-header">
                            <span class="dokan-chat-summary">{{ __( 'Ticket summary', 'dokan' ) }}</span>
                            <span @click="collapseSummaryBox()" class="dokan-summary-arrow dashicons" v-bind:class="show_summary_box ? 'dashicons-arrow-down-alt2' : 'dashicons-arrow-up-alt2'"></span>
                        </div>

                        <div class="dokan-chat-summary-body" v-bind:class="show_summary_box ? 'dokan-chat-summary-hide' : ''">
                            <div class="dokan-chat-summary-box">
                                <div class="dokan-single-summary-box">
                                    <div class="dokan-summary-title">{{ __( 'Ticket ID:', 'dokan' ) }}</div>
                                    <div class="dokan-summary-info ticket-id">#{{ topic_id }}</div>
                                </div>
                                <div class="dokan-single-summary-box">
                                    <div class="dokan-summary-title">{{ __( 'Vendor:', 'dokan' ) }}</div>
                                    <div class="dokan-summary-info dokan-vendor-info">
                                        <div class="dokan-vendor-store">
                                            <router-link :to="'/vendors/' + vendor_id">
                                                {{ store_info.store_name !== undefined ? store_info.store_name : '' }}
                                            </router-link>
                                        </div>
                                        <div class="dokan-vendor-category">{{ getCategories }}</div>
                                    </div>
                                </div>
                                <div class="dokan-single-summary-box">
                                    <div class="dokan-summary-title">{{ __( 'Customer:', 'dokan' ) }}</div>
                                    <div class="dokan-summary-info dokan-customer-info">{{ undefined !== topic.post_author_name ? topic.post_author_name : '' }}</div>
                                </div>
                                <div class="dokan-single-summary-box">
                                    <div class="dokan-summary-title">{{ __( 'Conversation:', 'dokan' ) }}</div>
                                    <div class="dokan-summary-info conversation">{{ conversationCount }}</div>
                                </div>
                                <div class="dokan-single-summary-box">
                                    <div class="dokan-summary-title">{{ __( 'Created At:', 'dokan' ) }}</div>
                                    <div class="dokan-summary-info created-at">{{ topic.post_date_formated !== undefined ? topic.post_date_formated : '' }}</div>
                                </div>
                                <div class="dokan-single-summary-box">
                                    <div class="dokan-summary-title">{{ __( 'Email Notification:', 'dokan' ) }}</div>
                                    <div class="dokan-summary-info created-at">
                                        <switches :value="1" :enabled="emailNotification" @input="enableEmailNotification"></switches>
                                    </div>
                                </div>
                            </div>

                            <div class="dokan-chat-status-change-button">
                                <button v-if="topic.post_status === 'open'" @click="openOrClose('closed')" class="dokan-status-button dokan-close-ticket">{{ __( 'Close ticket', 'dokan' ) }}</button>
                                <button v-else @click="openOrClose('open')" class="dokan-status-button dokan-reopen-ticket">{{ __( 'Reopen ticket', 'dokan' ) }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let Loading = dokan_get_lib('Loading');
let Switches = dokan_get_lib('Switches');

import ChatBox from './ChatBox.vue';
import $ from 'jquery';

export default {
    name:'SingleSupportTicket',

    components:{
        Loading,
        ChatBox,
        Switches,
    },

    data(){
        return{
            loading: false,
            topic_id: Number( this.$route.query.topic ) || 0,
            vendor_id: Number( this.$route.query.vendor_id ) || 0,
            comments: [],
            topic: {},
            reply_text: '',
            store_info:{},
            chatLoading: false,
            site_image_url: '',
            site_title: '',
            selected_user: 'admin',
            show_summary_box: false,
            defaultUserType:{ "type":"customer", "text":"Customer" },
            emailNotification: false,
            adminEmailNotification: false,
        }
    },

    watch: {
        '$route.query.topic'() {
            this.topic_id = Number( this.$route.query.topic ) || 0;
        },
        '$route.query.vendor_id'() {
            this.vendor_id = Number( this.$route.query.vendor_id ) || 0;
        },
    },

    created() {
        this.fetchTopicData();
    },

    methods: {
        fetchTopicData( pageLoading = true ) {
            if ( true === pageLoading ) {
                this.loading = true;
            } else if ( false === pageLoading ) {
                this.chatLoading = true;
            }

            dokan.api.get( `/admin/support-ticket/${this.topic_id}`, { vendor_id: this.vendor_id } )
                .done((response, status, xhr) => {
                    this.comments = response.comments;
                    this.topic = response.topic;
                    this.store_info = response.store_info;
                    this.site_image_url = response.site_image_url;
                    this.site_title = response.site_title;
                    this.updateUnreadTicketCounter( response.unread_topics_count ? response.unread_topics_count : 0 );

                    this.adminEmailNotification = response.dokan_admin_email_notification_global && 'on' === response.dokan_admin_email_notification_global ? true : false;
                    this.emailNotification = response.dokan_admin_email_notification && 'on' === response.dokan_admin_email_notification ? true : false;

                    this.loading = false;
                    this.chatLoading = false;
            });
        },

        updateUnreadTicketCounter( unread ) {
            let badge      = $('.dokan-unread-ticket-count-in-list');
            let badgeCount = $('.dokan-unread-ticket-count-badge-in-list');

            badgeCount.html(unread);
            unread > 0 ? badge.show() : badge.hide()
        },

        enableEmailNotification(status) {
            dokan.api.post( `/admin/support-ticket/${this.topic_id}/email-notification`, { notification: status } )
            .done((response, status, xhr) => {
                this.$notify({
                    title: this.__( 'Success!', 'dokan' ),
                    type: response.type,
                    text: response.message,
                });

                this.emailNotification = response.result;
            });
        },

        sendReplay() {
            this.chatLoading = true;

            let data = {
                replay: this.reply_text,
                vendor_id: this.vendor_id,
                selected_user: this.selected_user,
            };

            dokan.api.post( `/admin/support-ticket/${this.topic_id}`, data )
                .done((response, status, xhr) => {
                    this.chatLoading = false;
                    this.reply_text = '';
                    this.fetchTopicData( false);
            });
        },

        openOrClose( status = '' ) {
            if ( '' !== status && ( 'open' === status || 'closed' === status ) ) {
                this.loading = true;
                dokan.api.post( `/admin/support-ticket/${this.topic_id}/status`, { status: status } )
                    .done((response, status, xhr) => {
                        this.loading = false;
                        this.fetchTopicData();

                        let result = response.data.result !== undefined ? response.data.result : 'error';
                        let message = response.data.message !== undefined ? response.data.message : '';

                        this.$notify({
                            title: this.__( 'Success!', 'dokan' ),
                            type: result,
                            text: message,
                        });
                });
            }
        },

        scroolTOBottom(){
            let messageBody = this.$refs.chatBoxContainer;
            if ( undefined !== messageBody ) {
                messageBody.scrollTop = messageBody.scrollHeight - messageBody.clientHeight;
            }
        },

        deleteComment( id ) {
            Swal.fire({
                title: this.__( 'Delete comment', 'dokan' ),
                text: this.__( 'Are you sure, you want to delete this comment!', 'dokan' ),
                icon: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonText: this.__( 'Yes', 'dokan' ),
                cancelButtonText: this.__( 'No', 'dokan' ),
                focusConfirm: false
            }).then((result) => {
                if (result.value) {
                    dokan.api.delete( `/admin/support-ticket/${id}/comment` )
                        .done((response) => {
                            if ( true === response ) {

                                this.loading = false;
                                this.pageLoading = false;
                                this.fetchTopicData();

                                this.$notify({
                                    title: this.__( 'Success!', 'dokan' ),
                                    type: 'success',
                                    text: this.__( 'Deleted comment successfully!', 'dokan' ),
                                });
                            } else {
                                this.$notify({
                                    title: this.__( 'Sorry!', 'dokan' ),
                                    type: 'warn',
                                    text: this.__( "Couldn't delete, please try again !", 'dokan' ),
                                });
                            }
                        }).fail((response, status, xhr) => {
                                this.$notify({
                                    title: this.__( 'Failed!', 'dokan' ),
                                    type: 'error',
                                    text: this.__( 'Failed to delete comment!', 'dokan' ),
                                });
                        })
                }
            }) ;
        },

        collapseSummaryBox( show = 'block' ) {
            this.show_summary_box = ! this.show_summary_box;
        },

        disableReplyButton() {
            if ( this.reply_text.length < 1 || this.reply_text === '' || this.reply_text === ' ' ) {
                return true;
            }

            return false;
        },
    },

    updated() {
        this.scroolTOBottom();
    },

    computed: {
        getCategories: function () {
            if ( undefined !== this.store_info.categories ) {
                return this.store_info.categories.map( item => item.name ).join(', ');
            }

            return '';
        },

        conversationCount: function () {
            return this.comments.length + 1;
        },
    },
}
</script>

<style lang="less" scoped>
.dokan-admin-single-store-support-ticket{
    position: relative;
        .loading {
            position: absolute;
            height: 100%;
            margin: 0 0 0 -15px;
            background: rgba(0,0,0, 0.2);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            .dokan-loader {
                margin-top: 30px;
            }
    }
}

.dokan-chat-wraper{
    height: auto;
    min-height: 100%;
    width: 100%;

    .dokan-chat-page-container{
        display: flex;
        justify-content: space-between;
        margin-top: 15px;

        .dokan-chat-container{
            width: 68%;
            background: #FFFFFF;
            border: 1px solid rgba(233, 238, 248, 0.9);
            box-sizing: border-box;
            border-radius: 3px;

            .dokan-chat-header{
                width: 100%;
                display: flex;
                border-bottom: 1px solid #F0F0F1;

                .dokan-chat-title-box{
                    width: 50%;
                    display: block;

                    .dokan-chat-title{
                        float: left;
                        padding: 20px 20px;
                        font-size: 1rem;
                        font-weight: 700;
                    }
                }
                .dokan-chat-status-box{
                    width: 44%;
                    display: block;

                    .dokan-chat-status{
                        float: right;
                        margin: 15px 5px 15px 20px;
                        padding: 5px 15px;
                        border-radius: 3px;
                    }
                    .dokan-chat-open{
                        background-color: rgba(46, 204, 113, 0.15);
                        color: rgba(3, 148, 64, 1);
                    }
                    .dokan-chat-closed{
                        background-color: rgba(168, 168, 168, 0.1);
                        color: rgba(130, 130, 130, 1);
                    }
                }
                .dokan-chat-header-blank-last{
                    width: 6%;
                    display: block;
                }
            }

            .dokan-chat-box-container{
                width: 100%;
                max-height: 400px;
                overflow-x: hidden;
                overflow-y: auto;
                scroll-behavior: smooth;

                &::-webkit-scrollbar {
                    width: 5px;
                }

                /* Track */
                &::-webkit-scrollbar-track {
                    background: #FFFFFF;
                }

                /* Handle */
                &::-webkit-scrollbar-thumb {
                    background: rgba(241, 133, 29, 0.3);
                    border-radius: 3px;
                }

                /* Handle on hover */
                &::-webkit-scrollbar-thumb:hover {
                    background: rgb(241, 133, 29,0.4);
                }
            }

            .dokan-chat-replay-container{
                display: flex;
                flex-direction: column;
                margin: 30px 20px 20px 20px;

                .dokan-chat-sender-selection-box{
                    margin-bottom: 15px;
                    margin-left: -5px;

                    select{
                        outline: none;
                        border: none;
                        color: #A1A1A1;
                    }
                }
                .dokan-chat-replay-box{
                    margin-bottom: 10px;

                    .dokan-chat-replay{
                        width: 100%;
                        height: 100px;
                        background: #FFFFFF;
                        border: 1px solid rgba(233, 238, 248, 0.9);
                        box-sizing: border-box;
                        border-radius: 3px;

                        &::-webkit-input-placeholder {
                            color: #DEDEDE;
                        }

                        &:-moz-placeholder { /* Firefox 18- */
                            color: #DEDEDE;
                        }

                        &::-moz-placeholder {  /* Firefox 19+ */
                            color: #DEDEDE;
                        }

                        &:-ms-input-placeholder {
                            color: #DEDEDE;
                        }

                        &::placeholder {
                            color: #DEDEDE;
                        }
                    }
                }
                .dokan-chat-send-box{
                    margin-bottom: 20px;
                    width: 100%;
                    display: block;

                    .dokan-send-replay{
                        float: right;
                        padding: 9px 20px;
                        background: #3D566E;
                        opacity: 0.4;
                        border-radius: 3px;
                        color: #FFFFFF;
                        border: none;
                        outline: none;
                        cursor: pointer;

                        &:hover{
                            background: #3D566E;
                            opacity: 0.7;
                        }
                        &:active{
                            background: #3D566E;
                            opacity: 1;
                        }
                    }
                }

                .dokan-dot-parent{
                    display: flex;
                    justify-content: center;
                    margin-top: 10px;
                    .dokan-dot-container{
                        width: 10%;
                        display: flex;
                        justify-content: space-evenly;
                        .dokan-dot{
                            width: 7px;
                            height: 7px;
                            left: 566.14px;
                            top: 775.13px;
                            background: #D8D8D8;
                            border-radius: 50%;
                        }
                    }
                }
                .dokan-closed-message{
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    color: rgba(187, 192, 199, 1);
                    margin-top: 10px;
                    font-size: 1rem;
                    font-weight: 200;
                }
            }
        }
        .dokan-chat-summary-container{
            width: 30%;
            .dokan-chat-summary-holder{
                background: #FFFFFF;
                border: 1px solid rgba(233, 238, 248, 0.9);
                box-sizing: border-box;
                border-radius: 3px;
                padding: 20px;

                .dokan-chat-summary-header{
                    font-size: 1rem;
                    font-weight: 700;
                    display: flex;
                    justify-content: space-between;

                    .dokan-summary-arrow{
                        cursor: pointer;
                        color: rgba(197, 197, 197,1);
                    }
                }
                .dokan-chat-summary-body{
                    .dokan-chat-summary-box{
                        margin-top: 20px;

                        .dokan-single-summary-box{
                            display: flex;
                            margin-top: 10px;

                            .dokan-summary-title{
                                width: 40%;
                                font-weight: 400;
                                color: rgba(120, 129, 143, 1);
                            }
                            .dokan-summary-info{
                                width: 60%;

                                .dokan-vendor-category{
                                    font-weight: 400;
                                    color: rgba(120, 129, 143, 1);
                                }
                            }
                            .dokan-ticket-id{
                                font-weight: 700;
                            }

                            .dokan-vendor-info a{
                                text-decoration: none;
                                font-weight: 700;
                                color: rgba(26, 158, 212, 1);

                                &:hover{
                                    color: rgba(26, 158, 212, 0.8);
                                }
                                &:active{
                                    color: rgba(26, 159, 212, 1);
                                }
                            }
                        }
                    }
                    .dokan-chat-status-change-button{
                        margin-top: 20px;
                        display: block;

                        .dokan-status-button{
                            border-radius: 3px;
                            padding: 10px 15px;
                            border: none;
                            outline: none;
                            color: #FFFFFF;
                            cursor: pointer;
                        }
                        .dokan-close-ticket{
                            background: rgb(26, 159, 212);
                            &:hover{
                                background: #1a9fd4c7;
                            }
                            &:active{
                                background: rgb(26, 159, 212);
                            }
                        }
                        .dokan-reopen-ticket{
                            background: rgba(242, 98, 77, 0.5);
                            &:hover{
                                background: rgba(242, 98, 77, 0.8);
                            }
                            &:active{
                                background: rgba(242, 98, 77, 0.5);
                            }
                        }
                    }
                }
                .dokan-chat-summary-hide{
                    display: none !important;
                }
            }
        }
    }
}

@media only screen and (max-width: 800px) {
    .dokan-chat-wraper{

        .dokan-chat-page-container{
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            flex-direction: column-reverse;

            .dokan-chat-container{
                width: 100%;
                margin-top: 10px;
            }

            .dokan-chat-summary-container{
                width: 100%;
            }
        }
    }
}

@media only screen and (max-width: 1000px) {
        .dokan-chat-wraper{

        .dokan-chat-page-container{
            .dokan-chat-container{
                .dokan-chat-header{
                    .dokan-chat-status-box{
                        width: 40%;
                    }
                    .dokan-chat-header-blank-last{
                        width: 10%;
                    }
                }
            }

            .dokan-chat-summary-container{
                .dokan-chat-summary-holder{
                    .dokan-chat-summary-body{

                        .dokan-chat-summary-box{
                            .dokan-single-summary-box{
                                .dokan-summary-title{
                                    width: 50%;
                                }
                                .dokan-summary-info{
                                    width: 50%;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
