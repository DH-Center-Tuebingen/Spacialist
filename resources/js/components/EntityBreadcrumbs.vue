<template>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb my-0 px-2 py-1">
            <li class="breadcrumb-item breadcrumb-item-slash" :class="{'active': p.last}" v-for="p in state.parsedList" :key="p.id">
                <span v-if="p.last" :title="p.name">
                    {{ truncate(p.name, 25) }}
                </span>
                <router-link v-else :to="{name: 'entitydetail', params: {id: p.id}}" append :title="p.name">
                    {{ truncate(p.name, 25) }}
                </router-link>
            </li>
        </ol>
    </nav>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs
    } from 'vue';

    import {
        truncate,
    } from '../helpers/filters.js';

    export default {
        props: {
            entity: {
                required: true,
                type: Object
            }
        },
        setup(props) {
            const {
                entity,
            } = toRefs(props);

            console.log(entity, "entity in breadcrumb");

            const state = reactive({
                parsedList: computed(_ => {
                    let l = [];
                    const revIds = entity.value.parentIds.slice().reverse();
                    const revNames = entity.value.parentNames.slice().reverse();
                    const cnt = revIds.length;
                    for(let i=0; i<cnt; i++) {
                        const pid = revIds[i];
                        const pname = revNames[i];
                        l.push({
                            id: pid,
                            name: pname,
                            last: i == cnt -1
                        });
                    }
                    return l;
                })
            });

            // RETURN
            return {
                // HELPERS
                truncate,
                // LOCAL
                // PROPS
                // STATE
                state,
            };
        }
    }
</script>
