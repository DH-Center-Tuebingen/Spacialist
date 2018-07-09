<template>
    <div>
        <i class="fas fa-fw fa-spa"></i>
        <span>{{data.name}}</span>
        <span class="pl-1 font-italic mb-0" v-if="data.context_type_id">
            {{ $translateConcept($getEntityType(data.context_type_id).thesaurus_url) }}
        </span>
        <span class="pl-1" v-show="data.children_count">
            ({{ data.children_count }})
        </span>
    </div>
</template>

<script>
export default {
    props: {
        data: {
            required: true
        }
    }
}
</script>
