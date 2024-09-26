<template>
    <div
        v-if="state.comments"
        class="px-1 mb-auto overflow-y-auto h-100 pe-2 d-flex flex-column"
    >
        <div
            v-if="state.fetching"
            class="mt-2"
        >
            <alert
                class="mb-0"
                type="info"
                :message="t('global.comments.fetching')"
            />
        </div>
        <div
            v-else-if="commentFetchFailed"
            class="mt-2"
        >
            <p class="alert alert-danger mb-0">
                {{ t('global.comments.fetching_failed') }}
                <button
                    type="button"
                    class="d-block mt-2 btn btn-sm btn-outline-success"
                    @click="fetchComments"
                >
                    <i class="fas fa-fw fa-sync" />
                    {{ t('global.comments.retry_failed') }}
                </button>
            </p>
        </div>
        <comment-list
            v-else
            class="flex-grow-1"
            :avatar="32"
            :comments="state.comments"
            :hide-button="false"
            :resource="resourceInfo"
            list-classes="overflow-y-auto"
        />
    </div>
</template>

<script>

    import {
        onMounted,
        onBeforeUpdate,
        reactive,
        computed,
        watch,
    } from 'vue';

    import {
        can,
    } from '@/helpers/helpers.js';

    import {
        getEntityComments,
    } from '@/api.js';

    import store from '@/bootstrap/store';

    export default {
        props: {
            subject: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const state = reactive({
                comments: computed(_=> props.subject.comments || []),
                count: computed(_ => props.subject.comments_count || 0),
                fetching: false,
                loadingState: 'not',
            });
            
            onMounted(() => {
                fetchComments();
            });

            watch(() => props.subject, (newSubject, oldSubject) => {
                if(newSubject.id == oldSubject.id) return;
                resetState();
                fetchComments();
            });

            onBeforeUpdate(() => {
                state.loadingState = 'not';
            });

            const resetState = _ => {
                state.loadingState = 'not';
            };

            const fetchComments = _ => {
                if(!props.subject?.id) return;
                if(!can('comments_read')) return;

                state.loadingState = 'fetching';
                getEntityComments(props.subject.id).then(comments => {
                    store.dispatch('setEntityComments', comments);
                    state.loadingState = 'fetched';
                }).catch(e => {
                    state.loadingState = 'failed';
                });
            };

            const commentsFetching = computed(_ => {
                return state.commentLoadingState === 'fetching';
            });

            const commentFetchFailed = computed(_ => {
                return state.commentLoadingState === 'failed';
            });

            const resourceInfo = computed(_ => {
                if(!props.subject) return {
                    id: 0,
                    type: 'entity',
                };

                return {
                    id: props.subject.id,
                    type: 'entity',
                };
            });

            return {
                t,
                commentFetchFailed,
                commentsFetching,
                fetchComments,
                resourceInfo,
                state,
            };
        }
    };
</script>