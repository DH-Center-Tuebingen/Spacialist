<template>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label text-end">{{ t('global.name') }}:</label>
        <div class="col-md-10">
            <input class="form-control" type="text" v-model="data.name" :readonly="readonly" @input="onChange" />
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label text-end">{{ t('global.email') }}:</label>
        <div class="col-md-10">
            <input class="form-control" type="text" v-model="data.email" :readonly="readonly" @input="onChange" />
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label text-end">{{ t('global.description') }}:</label>
        <div class="col-md-10">
            <textarea class="form-control" rows="1" v-model="data.description" :readonly="readonly" @input="onChange" />
        </div>
        <div class="offset-2 mt-1">
            <button type="button" class="btn btn-sm btn-outline-primary" @click="openMdEditor()">
                Edit as Markdown
            </button>
        </div>
    </div>
    <div class="row">
        <label class="col-md-2 col-form-label text-end" for="public">{{ t('main.preference.key.project.public') }}:</label>
        <div class="col-md-10 d-flex flex-row align-items-center">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="public" v-model="data.public" :readonly="readonly" :disabled="readonly" @input="onChange" />
            </div>
        </div>
    </div>
</template>

<script>
    import {
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        _debounce
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

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed', {
                    value: {
                        name: data.value.name,
                        email: data.value.email,
                        description: data.value.description,
                        public: data.value.public,
                    }
                });
            }, 250);
            const openMdEditor = _ => {
                showMarkdownEditor(data.value.description, text => {
                    data.value.description = text;
                    onChange();
                });
            };

            // DATA

            // RETURN
            return {
                t,
                // LOCAL
                onChange,
                openMdEditor,
                // PROPS
                data,
                readonly,
                // STATE
            };
        }
    }
</script>