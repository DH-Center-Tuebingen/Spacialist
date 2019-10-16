<template>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb my-0 px-2 py-1">
            <li class="breadcrumb-item breadcrumb-item-slash" :class="{'active': p.last}" v-for="p in parsedList">
                <span v-if="p.last" :title="p.name"><!--
                    -->{{ p.name | truncate(25) }}
                </span>
                <router-link v-else :to="{name: 'entitydetail', params: {id: p.id}}" append :title="p.name">
                    {{ p.name | truncate(25) }}
                </router-link>
            </li>
        </ol>
    </nav>
</template>

<script>
    import { EventBus } from '../event-bus.js';

    export default {
        props: {
            entity: {
                required: true,
                type: Object
            }
        },
        beforeMount() {},
        methods: {
        },
        data () {
            return {
            }
        },
        computed: {
            parsedList() {
                let l = [];
                const revIds = this.entity.parentIds.slice().reverse();
                const revNames = this.entity.parentNames.slice().reverse();
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
            }
        }
    }
</script>
