<template>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label text-end">{{ t('global.name') }}:</label>
        <div class="col-md-10">
            <input
                v-model="localData.name"
                class="form-control"
                type="text"
                :readonly="readonly"
                @input="onChange"
            >
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label text-end">{{ t('global.email') }}:</label>
        <div class="col-md-10">
            <input
                v-model="localData.email"
                class="form-control"
                type="text"
                :readonly="readonly"
                @input="onChange"
            >
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label text-end">{{ t('global.description') }}:</label>
        <div class="col-md-10">
            <textarea
                v-model="localData.description"
                class="form-control"
                rows="1"
                :readonly="readonly"
                @input="onChange"
            />
        </div>
        <div class="offset-2 mt-1">
            <button
                type="button"
                class="btn btn-sm btn-outline-primary"
                @click="openMdEditor()"
            >
                Edit as Markdown
            </button>
        </div>
    </div>
    <div class="row">
        <label
            class="col-md-2 col-form-label text-end"
            for="public"
        >{{ t('main.preference.key.project.public') }}:</label>
        <div class="col-md-10 d-flex flex-row align-items-center">
            <div class="form-check form-switch">
                <input
                    id="public"
                    v-model="localData.public"
                    type="checkbox"
                    class="form-check-input"
                    :readonly="readonly"
                    :disabled="readonly"
                    @input="onChange"
                >
            </div>
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        _debounce,
        _cloneDeep,
    } from '@/helpers/helpers.js';
    import {
        showMarkdownEditor
    } from '@/helpers/modal.js';
    
    export default {
        props: {
            data: {
                required: true,
                type: Object,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
                readonly,
            } = toRefs(props);

            const localData =       (_cloneDeep(data.value));

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed', {
                        name: localData.name,
                        email: localData.email,
                        description: localData.description,
                        public: localData.public,
                });
            }, 250);
            const openMdEditor = _ => {
                showMarkdownEditor(localData.description, text => {
                    localData.description = text;
                    onChange();
                });
            };

            // DATA

            // RETURN
            return {
                t,
                // LOCAL
                localData,
                onChange,
                openMdEditor,
                // STATE
            };
        }
    }
</script>