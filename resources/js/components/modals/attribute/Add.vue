<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="add-attribute-modal">
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.datamodel.attribute.modal.new.title') }}
                </h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
                </button>
            </div>
            <div class="modal-body nonscrollable">
                <attribute-template
                    :type="'default'"
                    :external="state.formId"
                    @created="add"
                    @updated="updateAttribute"
                    @validation="checkValidation" />
                <div v-if="state.hasColumns">
                    <hr/>
                    <div class="bg-secondary bg-opacity-25 p-2 rounded">
                        <h5 class="d-flex flex-row justify-content-between">
                            <div>
                                {{ t('global.column', 2) }}
                                <span class="badge bg-primary">
                                    {{ state.columns.length }}
                                </span>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-sm btn-outline-success" :form="state.tableFormId" :disabled="!state.tableColumnValidated">
                                    <i class="fas fa-fw fa-plus"></i> {{ t('global.add_column') }}
                                </button>
                            </div>
                        </h5>
                        <attribute-template
                            :type="'table'"
                            :external="state.tableFormId"
                            @created="addColumn"
                            @validation="checkTableValidation"  />
                    </div>
                </div>
                <div v-if="state.previewAttribute">
                    <hr>
                    <h5>
                        {{ t('global.preview') }}:
                    </h5>
                    <attribute-list
                        :attributes="state.previewAttribute.attribute"
                        :disable-drag="true"
                        :options="{'hide_labels': true}"
                        :nolabels="true"
                        :values="state.previewAttribute.values"
                        :selections="{}"
                        :preview="true"
                        :preview-data="state.previewData" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-success" :form="state.formId" :disabled="!state.validated">
                    <i class="fas fa-fw fa-plus"></i> {{ t('global.add') }}
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                    <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        randomId,
    } from '@/helpers/helpers.js';

    import AttributeTemplate from '@/components/AttributeTemplate.vue';

    export default {
        components: {
            'attribute-template': AttributeTemplate,
        },
        props: {
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const add = e => {
                const attribute = {...e};
                if(state.hasColumns && state.columns.length > 0) {
                    attribute.columns = state.columns.slice();
                }
                context.emit('confirm', attribute);
            };
            const addColumn = e => {
                state.columns.push(e);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };
            const updateAttribute = e => {
                state.attribute = e;
            };
            const checkValidation = e => {
                state.validated = e;
            };
            const checkTableValidation = e => {
                state.tableColumnValidated = e;
            };
            const getInitialValue = attribute => {
                switch(attribute.type) {
                    case 'string':
                    case 'stringf':
                    case 'iconclass':
                    case 'rism':
                    case 'geography':
                        return '';
                    case 'integer':
                    case 'double':
                        return 0;
                    case 'boolean':
                        return 0;
                    case 'percentage':
                        return 50;
                    case 'serial':
                        let str = attribute.textContent;
                        let toRepl = '%d';
                        let ctr = "1954";
                        if(!str) {
                            str = 'Find_%05d_Placeholder';
                        }
                        let hasIdentifier = false;
                        let isSimple = true;
                        let matches = str.match(/.*(%d).*/);
                        if(matches && matches[1]) {
                            hasIdentifier = true;
                            isSimple = true;
                        } else {
                            matches = str.match(/.*(%\d*d).*/);
                            if(matches && matches[1]) {
                                hasIdentifier = true;
                                isSimple = false;
                            }
                        }
                        if(hasIdentifier && !isSimple) {
                            toRepl = matches[1];
                            let pad = parseInt(toRepl.substring(1, toRepl.length-1));
                            ctr = ctr.padStart(pad, '0');
                        }
                        return str.replaceAll(toRepl, ctr);
                    case 'list':
                    case 'string-mc':
                    case 'entity-mc':
                        return [];
                    case 'date':
                        return new Date();
                    case 'sql':
                        return t('global.preview_not_available');
                    case 'epoch':
                    case 'dimension':
                    case 'entity':
                    case 'string-sc':
                    case 'table':
                        return {};
                }
            };

            // DATA
            const fakeId = randomId();
            const state = reactive({
                attribute: {},
                columns: [],
                validated: false,
                tableColumnValidated: false,
                formId: 'new-attribute-form-external-submit',
                tableFormId: 'new-attribute-form-table-type-external-submit',
                hasColumns: computed(_ => {
                    return state.attribute.type == 'table';
                }),
                previewAttribute: computed(_ => {
                    if(!state.attribute.type) return null;

                    return {
                        attribute: [{
                            ...state.attribute,
                            datatype: state.attribute.type,
                            id: fakeId,
                            isDisabled: false,
                        }],
                        values: {
                            [fakeId]: {
                                value: getInitialValue(state.attribute),
                            },
                        },
                    };
                }),
                previewData: computed(_ => {
                    if(!state.attribute.type) return null;
                    // Currently only table type supports additional data
                    if(state.attribute.type != 'table') return null;

                    const columns = {};
                    if(state.columns.length == 0) {
                        columns.is_preview = true;
                    } else {
                        state.columns.forEach((c, i) => {
                            const colId = fakeId + (i+1);
                            columns[colId] = {
                                id: colId,
                                parent_id: fakeId,
                                recursive: c.recursive,
                                datatype: c.type,
                                thesaurus_url: c.label.concept_url
                            };
                        });
                    }
                    return {
                        [fakeId]: columns,
                    };
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                add,
                addColumn,
                closeModal,
                updateAttribute,
                checkValidation,
                checkTableValidation,
                // STATE
                state,
            }
        },
    }
</script>