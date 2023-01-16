<template>
  <vue-final-modal
    class="modal-container modal"
    content-class="sp-modal-content sp-modal-content-sm"
    name="bibliograpy-item-modal">
    <div class="sp-modal-content sp-modal-content-sm">
        <div class="modal-header">
            <h5 class="modal-title" v-if="state.data.id">
                {{ t('main.bibliography.modal.edit.title') }}
            </h5>
            <h5 class="modal-title" v-else>
                {{ t('main.bibliography.modal.new.title') }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body" :class="state.scrollStateBodyClasses" @paste="handlePasteFromClipboard($event)">
            <alert
                :message="t('main.bibliography.modal.paste_info')"
                :type="'note'"
                :noicon="false"
                :icontext="t('global.note')" />
            <form role="form" id="addBibliographyItemForm" class="col px-0" :class="state.scrollStateClasses" name="addBibliographyItemForm" @submit.prevent="submitItem()">
                <div class="row mb-3">
                    <label class="col-form-label col-md-3 text-end" for="type">{{ t('global.type') }}:</label>
                    <div class="col-md-9">
                        <multiselect
                            :classes="multiselectResetClasslist"
                            v-model="state.data.type"
                            :label="'name'"
                            :track-by="'name'"
                            :object="true"
                            :valueProp="'id'"
                            :searchable="true"
                            :options="bibliographyTypes"
                            :placeholder="t('global.select.placeholder')">
                            <template v-slot:option="{ option }">
                                <div class="d-flex justify-content-between w-100">
                                    <span>
                                        {{ t(`main.bibliography.types.${option.name}`) }}
                                    </span>
                                    <span class="small text-muted">
                                        {{ option.name }}
                                    </span>
                                </div>
                            </template>
                        </multiselect>
                    </div>
                </div>
                <template v-for="bibType in state.typeList" :key="bibType">
                    <bibtex-fieldset v-if="state.typeName == bibType" :data="state.fieldData" :type="bibType" :ref="el => fieldsetRefs[bibType] = el" @change="fieldsetStateUpdated" />
                </template>
                <div class="" v-if="state.data.file">
                    <a :href="state.data.file_url" target="_blank">
                        {{ state.data.file.split('/')[1] }}
                    </a>
                    <a href="#" class="text-danger text-decoration-none" @click.prevent="removeFile()">
                        <i class="fas fa-fw fa-trash"></i>
                    </a>
                </div>
                <div class="d-flex gap-2" v-else>
                    <file-upload
                        class="btn btn-sm btn-outline-primary clickable"
                        ref="upload_bib_item_attachment"
                        v-model="state.fileContainer"
                        :input-id="'upload_bib_item_attachment'"
                        :disabled="!can('bibliography_write|bibliography_create')"
                        :custom-action="importFile"
                        :directory="false"
                        :accept="'image/*,application/pdf,text/plain'"
                        :extensions="'jpg,jpeg,gif,png,txt,pdf'"
                        :multiple="false"
                        :drop="true"
                        @input-file="inputFile">
                            <span>
                                <i class="fas fa-fw fa-file-import"></i> {{ t('main.bibliography.modal.attach_file') }}
                            </span>
                    </file-upload>
                    <span v-if="state.file">
                        {{ state.file.name }}
                        <a href="#" class="text-reset text-decoration-none" @click.prevent="removeQueuedFile()">
                            <i class="fas fa-fw fa-times"></i>
                        </a>
                    </span>
                </div>
            </form>
            <bibtex-code :code="state.data.fields" :type="state.typeName" :show="true" />
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" :disabled="state.disabled" form="addBibliographyItemForm" v-if="state.data.id">
                <i class="fas fa-fw fa-save"></i> {{ t('global.update') }}
            </button>
            <button type="submit" class="btn btn-outline-success" :disabled="state.disabled" form="addBibliographyItemForm" v-else>
                <i class="fas fa-fw fa-plus"></i> {{ t('global.add') }}
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
            </button>
        </div>
    </div>
  </vue-final-modal>
</template>

<script>
    import * as bibtexParser from '@retorquere/bibtex-parser';

    import {
        computed,
        reactive,
        ref,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        can,
        getTs,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';
    import {
        bibliographyTypes,
    } from '@/helpers/bibliography.js';

    import FieldSet from '@/components/modals/bibliography/FieldSet.vue';

    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
        },
        components: {
            'bibtex-fieldset': FieldSet,
        },
        emits: ['save', 'closing'],
        setup(props, context) {
            const {
                data,
            } = toRefs(props);
            const { t } = useI18n();

            // FUNCTIONS
            const fromBibtexEntry = str => {
                try {
                    const content = bibtexParser.parse(str);
                    // only parse if str contains exactly one entry
                    if(!content || !content.entries || content.entries.length !== 1) return;
                    const entry = content.entries[0];
                    const type = bibliographyTypes.find(t => t.name == entry.type);
                    state.data.type = type;
                    state.data.fields.citekey = entry.key;
                    for(let k in entry.fields) {
                        const p = entry.fields[k];
                        state.data.fields[k] = p.join(', ');
                    }
                } catch(err) {
                }
            };
            const handlePasteFromClipboard = e => {
                const items = e.clipboardData.items;
                for(let i=0; i<items.length; i++) {
                    const c = items[i];
                    if(c.kind == 'string' && c.type == 'text/plain') {
                        c.getAsString(s => {
                            fromBibtexEntry(s);
                        });
                    }
                }
            };
            const importFile = (file, component) => {
            };
            const inputFile = (newFile, oldFile) => {
                if(!can('bibliography_write|bibliography_create')) return;

                state.fileRemoved = false;
            };
            const removeQueuedFile = _ => {
                state.fileRemoved = true;
                state.fileContainer = [];
            }
            const removeFile = _ => {
                state.fileRemoved = true;
                state.data.file = '';
                state.data.file_url = '';
            };
            const fieldsetStateUpdated = event => {
                state.disabled = !event.dirty || !event.valid;
                state.data.fields = {
                    ...state.data.fields,
                    ...event.values,
                };
            };
            const submitItem = _ => {
                const fsRef = fieldsetRefs.value[state.typeName];
                const updFields = fsRef.getData();
                const data = {
                    fields: updFields,
                    type: state.data.type,
                    id: state.data.id,
                };
                const file = state.fileRemoved ? 'delete' : (state.file ? state.file.file : null);
                context.emit('save', {
                    data: data,
                    file: file,
                });
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const fieldsetRefs = ref({});
            const state = reactive({
                id: `bibliography-item-modal-bibtex-code-${getTs()}`,
                data: data.value,
                fieldData: {...data.value},
                error: {},
                fileContainer: [],
                formMetas: {},
                scrollStateClasses: computed(_ => {
                    if(state.data.type) {
                        return ['scroll-y-auto', 'scroll-x-hidden'];
                    } else {
                        return ['scroll-visible'];
                    }
                }),
                scrollStateBodyClasses: computed(_ => {
                    if(state.data.type) {
                        return [];
                    } else {
                        return ['nonscrollable'];
                    }
                }),
                file: computed(_ => state.fileContainer.length > 0 ? state.fileContainer[0] : null),
                fileRemoved: false,
                disabled: true,
                typeName: computed(_ => state.data.type ? state.data.type.name : null),
                typeList: bibliographyTypes.map(t => t.name),
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                bibliographyTypes,
                multiselectResetClasslist,
                // PROPS
                // LOCAL
                handlePasteFromClipboard,
                importFile,
                inputFile,
                removeQueuedFile,
                removeFile,
                fieldsetStateUpdated,
                submitItem,
                closeModal,
                //STATE
                state,
                fieldsetRefs,
            }
        },
    }
</script>
