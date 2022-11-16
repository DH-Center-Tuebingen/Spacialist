<template>
  <vue-final-modal
    class="modal-container modal"
    content-class="sp-modal-content sp-modal-content-sm"
    :lock-scroll="false"
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
        <div class="modal-body"
        @paste="handlePasteFromClipboard($event)">
            <alert
                :message="t('main.bibliography.modal.paste_info')"
                :type="'note'"
                :noicon="false"
                :icontext="t('global.note')" />
            <form role="form" id="addBibliographyItemForm" class="col px-0 scroll-y-auto" name="addBibliographyItemForm" @submit.prevent="submitItem()">
                <div class="mb-3">
                    <label class="col-form-label col-md-3" for="type">{{ t('global.type') }}:</label>
                    <div class="col-md-9">
                        <multiselect
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
                <div v-for="field in state.typeFields" :key="field.id">
                    <label class="col-form-label col-md-3">
                        {{ t(`main.bibliography.column.${field}`) }}:
                    </label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" :class="getValidClass(state.error, field)" v-model="state.data.fields[field]"/>
    
                        <div class="invalid-feedback">
                            <span v-for="(msg, i) in state.error[field]" :key="i">
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                </div>
            </form>
            <h5 class="mt-3 d-flex gap-1">
                {{ t('main.bibliography.modal.new.bibtex_code') }}
                <small class="clickable" @click="toggleShowBibtexCode()">
                    <span v-show="state.bibtexCodeShown">
                        <i class="fas fa-fw fa-caret-up"></i>
                    </span>
                    <span v-show="!state.bibtexCodeShown">
                        <i class="fas fa-fw fa-caret-down"></i>
                    </span>
                </small>
                <small class="clickable text-primary" @click="copyToClipboard(state.id)">
                    <i class="fas fa-fw fa-copy"></i>
                </small>
            </h5>
            <span v-show="state.bibtexCodeShown" :id="state.id" v-html="bibtexify(state.data.fields, state.typeName)"></span>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" form="addBibliographyItemForm" v-if="state.data.id">
                <i class="fas fa-fw fa-save"></i> {{ t('global.update') }}
            </button>
            <button type="submit" class="btn btn-outline-success" form="addBibliographyItemForm" v-else>
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
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        getErrorMessages,
        getValidClass,
        getTs,
        copyToClipboard,
    } from '@/helpers/helpers.js';
    import {
        bibtexify,
    } from '@/helpers/filters.js';
    import {
        bibliographyTypes,
    } from '@/helpers/bibliography.js';

    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
            onSuccess: {
                required: false,
                type: Function
            },
            onClose: {
                required: false,
                type: Function
            }
        },
        emits: ['closing'],
        setup(props, context) {
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
            const toggleShowBibtexCode = _ => {
                state.bibtexCodeShown = !state.bibtexCodeShown;
            };
            const submitItem = _ => {
                if(props.onSuccess) {
                    props.onSuccess(state.data).then(response => {
                        context.emit('closing', false);
                    }).catch(e => {
                        state.error = getErrorMessages(e);
                    });
                } else {
                    context.emit('closing', false);
                }
            };
            const closeModal = _ => {
                if(props.onClose) {
                    props.onClose(state.data);
                }
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                id: `bibliography-item-modal-bibtex-code-${getTs()}`,
                data: props.data,
                bibtexCodeShown: true,
                error: {},
                typeName: computed(_ => state.data.type ? state.data.type.name : null),
                typeFields: computed(_ => {
                    if(state.data.type) {
                        return state.data.type.fields;
                    }
                    return bibliographyTypes[0].fields;
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                getValidClass,
                copyToClipboard,
                bibtexify,
                bibliographyTypes,
                // PROPS
                // LOCAL
                handlePasteFromClipboard,
                toggleShowBibtexCode,
                submitItem,
                closeModal,
                //STATE
                state,
            }
        },
    }
</script>
