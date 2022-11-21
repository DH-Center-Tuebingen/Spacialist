<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        :lock-scroll="false"
        name="bibliograpy-item-details-modal">
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.bibliography.modal.details.title') }}
                    <small>
                        -
                        {{ bibtexEntryToText(state.data.title) }}
                    </small>
                </h5>
                <button type="button" class="btn-close" aria-label="Close" @click="closeModal()">
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end mb-2">
                    <i class="fas fa-fw fa-user-edit"></i>
                    <span class="ms-1">
                        {{ date(state.data.last_updated, undefined, true, true) }}
                    </span>
                    -
                    <a href="#" @click.prevent="showUserInfo(state.user)" class="fw-medium" v-if="state.user">
                        {{ state.user.name }}
                        <user-avatar :user="state.user" :size="20" class="align-middle"></user-avatar>
                    </a>
                </div>
                <dl class="row bg-primary bg-opacity-25 mx-0 py-2 rounded dl-0-mb">
                    <dt class="col-md-3 text-end">
                        {{ t(`global.type`) }}
                    </dt>
                    <dd class="col-md-9 font-monospace" :title="state.data.type">
                        {{ t(`main.bibliography.types.${state.data.type}`) }}
                    </dd>
                    <dt class="col-md-3 text-end">
                        {{ t('main.bibliography.column.author') }}
                    </dt>
                    <dd class="col-md-9 font-monospace">
                        {{ bibtexEntryToText(state.data.author) }}
                    </dd>
                    <dt class="col-md-3 text-end">
                        {{ t('main.bibliography.column.title') }}
                    </dt>
                    <dd class="col-md-9 font-monospace">
                        {{ bibtexEntryToText(state.data.title) }}
                    </dd>
                    <dt class="col-md-3 text-end">
                        {{ t('main.bibliography.column.citekey') }}
                    </dt>
                    <dd class="col-md-9 font-monospace">
                        {{ bibtexEntryToText(state.data.citekey) }}
                    </dd>
                    <template v-for="(content, field) in state.filteredFields" :key="`item-detail-field-${field}`">
                        <dt class="col-md-3 text-end" :title="field">
                            {{ t(`main.bibliography.column.${field}`) }}
                        </dt>
                        <dd class="col-md-9 font-monospace">
                            <span v-if="field == 'howpublished'">
                                {{ createAnchorFromUrl(bibtexEntryToText(content, field)) }}
                            </span>
                            <span v-else>
                                {{ bibtexEntryToText(content, field) }}
                            </span>
                        </dd>
                    </template>
                </dl>
                <bibtex-code :code="state.toBibtexify" :type="state.data.type" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" @click="closeModal()">
                    <i class="fas fa-fw fa-times"></i> {{ t('global.close') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        createAnchorFromUrl,
        except,
        getUserBy,
        getTs,
    } from '@/helpers/helpers.js';
    import {
        bibtexEntryToText,
        bibtexify,
        date,
    } from '@/helpers/filters.js';
    import {
        showUserInfo,
    } from '@/helpers/modal.js';

    export default {
        props: {
            id: {
                required: true,
                type: Number
            },
        },
        emits: ['closing'],
        setup(props, context) {
            const {
                id,
            } = toRefs(props);
            const { t } = useI18n();
            const toast = useToast();

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                id: `bibliography-item-detail-modal-bibtex-code-${getTs()}`,
                data: {
                    id: null,
                    citekey: '',
                    user: null,
                    last_updated: null,
                    title: '',
                    author: '',
                    type: '',
                    data: {},
                },
                user: computed(_ => getUserBy(state.data.user)),
                filteredFields: computed(_ => {
                    const filtered = {};
                    for(let k in state.data.data) {
                        if(state.data.data[k]) {
                            filtered[k] = state.data.data[k];
                        }
                    }
                    return filtered;
                }),
                toBibtexify: computed(_ => {
                    return {
                        citekey: state.data.citekey,
                        title: state.data.title,
                        author: state.data.author,
                        ...state.filteredFields,
                    };
                }),
            });

            const item = store.getters.bibliography.find(item => item.id == id.value);
            if(item) {
                const {
                    citekey,
                    user_id,
                    title,
                    author,
                    type,
                    updated_at,
                    ...data
                } = except(item, ['id', 'created_at', 'file', 'file_url']);

                state.data.id = id.value;
                state.data.citekey = citekey;
                state.data.user = user_id;
                state.data.last_updated = updated_at;
                state.data.title = title;
                state.data.author = author;
                state.data.type = type;
                state.data.data = data;
            }

            // RETURN
            return {
                t,
                // HELPERS
                createAnchorFromUrl,
                getUserBy,
                bibtexEntryToText,
                bibtexify,
                date,
                showUserInfo,
                // PROPS
                // LOCAL
                closeModal,
                //STATE
                state,
            }
        },
    }
</script>
