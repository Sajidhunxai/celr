<template>
    <div class="chat-box">
        <div class="chat-image-box">
            <div class="chat-image-container">
                <img :src="image" alt="no-image" class="chat-image">
            </div>
        </div>
        <div class="chat-info-box">
            <div class="chat-sender-info">
                <div class="chat-user-box">
                    <span class="chat-user">{{ user }}</span>
                </div>
            </div>
            <div class="chat-text" :class="getClassByUserType()">
                {{ comment }}
            </div>
            <div class="chat-time-box">
                <span class="chat-time">{{ date }}</span>
            </div>
        </div>
        <div class="chat-action-box">
            <span @click="deleteComment" v-if="'open' === status && 'admin' === userType.type" class="chat-delete dashicons dashicons-trash"></span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ChatBox',
    props:{
        image: String,
        user: String,
        comment: String,
        date: String,
        status: String,
        id: Number,
        userType: Object,
    },
    methods: {
        deleteComment(){
            this.$emit( 'deleteComment', this.id );
        },

        getClassByUserType(){
            let textClass = 'customer-chat-text';

            if ( 'admin' === this.userType.type ) {
                textClass = 'admin-chat-text';
            } else if ( 'vendor' === this.userType.type ) {
                textClass = 'vendor-chat-text';
            }

            return textClass;
        }
    },
}
</script>

<style lang="less" scoped>
    .chat-box {
        width: 100%;
        display: flex;
        justify-content: space-between;
        margin-top: 20px;

        .chat-image-box {
            width: 8%;
            display: flex;
            justify-content: center;
            align-content: stretch;
            margin-left: 10px;

            .chat-image-container {
                display: inline-block;
                position: relative;
                width: 30px;
                height: 30px;
                overflow: hidden;
                border-radius: 50%;

                .chat-image {
                    width: auto;
                    height: 100%;
                }
            }
        }
        .chat-info-box {
            width: 88%;

            .chat-sender-info {
                display: flex;
                justify-content: space-between;
                height: 28px;
                align-items: center;
                .chat-user-box{
                    color: rgba(113, 113, 113, 1);
                    font-weight: 600;
                    font-size: .9rem;
                }
            }
            .chat-time-box {
                color: rgba(120, 129, 143, 0.5);
                font-weight: 400;
                float: right;
                margin-top: 9px;
            }
            .chat-text {
                border-radius: 0px 10px 10px 10px;
                display: flex;
                align-items: center;
                padding: 20px;
                margin-top: 10px;
            }
            .admin-chat-text {
                background: rgba(242, 250, 255, 1);
                color: rgba(26, 158, 212, 1);
            }
            .customer-chat-text {
                background: rgba(246, 246, 246, 1);
                color: rgba(113, 113, 113, 1);
            }
            .vendor-chat-text {
                background: rgba(243, 243, 255, 1);
                color: rgba(83, 83, 187, 1);
            }
        }
        .chat-action-box {
            width: 6%;
            display: flex;
            justify-content: center;
            align-items: center;

            .chat-delete {
                margin-top: 10px;
                display: none;
                visibility: hidden;
                color: rgba(224, 230, 240, 1);
                cursor: pointer;

                &:hover {
                    color: rgb(242, 98, 77, .4);
                }
            }
        }
        &:hover .chat-delete {
            display: block;
            visibility: visible;
        }
    }

    @media only screen and (max-width: 1000px) {
        .chat-box {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 20px;

            .chat-image-box {
                width: 13%;
                display: flex;
                justify-content: center;
                align-content: stretch;
                .chat-image-container{
                    display: inline-block;
                    position: relative;
                    width: 30px;
                    height: 30px;
                    overflow: hidden;
                    border-radius: 50%;
                    .chat-image{
                        width: auto;
                        height: 100%;
                    }
                }
            }
            .chat-info-box {
                width: 77%;

                .chat-sender-info {
                    display: flex;
                    justify-content: space-between;
                    height: 28px;
                    align-items: center;

                    .chat-user-box {
                        color: rgba(113, 113, 113, 1);
                        font-weight: 600;
                        font-size: .9rem;
                    }
                    .chat-time-box {
                        color: rgba(120, 129, 143, 0.5);
                        font-weight: 400;
                    }
                }
                .chat-text {
                    border-radius: 0px 10px 10px 10px;
                    display: flex;
                    align-items: center;
                    padding: 20px;
                    margin-top: 10px;
                }
                .admin-chat-text {
                    background: rgba(242, 250, 255, 1);
                    color: rgba(26, 158, 212, 1);
                }
                .customer-chat-text {
                    background: rgba(246, 246, 246, 1);
                    color: rgba(113, 113, 113, 1);
                }
                .vendor-chat-text {
                    background: rgba(243, 243, 255, 1);
                    color: rgba(83, 83, 187, 1);
                }
            }
            .chat-action-box {
                width: 10%;
                display: flex;
                justify-content: center;
                align-items: center;

                .chat-delete {
                    margin-top: 15px;
                    display: none;
                    visibility: hidden;
                    color: rgba(224, 230, 240, 1);
                    cursor: pointer;

                    &:hover {
                        color: rgb(242, 98, 77, .4);
                    }
                }
            }
            &:hover .chat-delete {
                display: block;
                visibility: visible;
            }
        }
    }
</style>
