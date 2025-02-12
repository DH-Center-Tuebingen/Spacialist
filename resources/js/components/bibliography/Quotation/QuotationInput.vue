<template>
    <div class="quotation-input d-flex align-items-center">
        <input
            v-model="state.editItem.description"
            type="text"
            class="form-control me-1"
        >
        <button
            type="button"
            class="btn btn-outline-success btn-sm me-1"
            @click.prevent="onUpdateReference(state.editItem)"
        >
            <i class="fas fa-fw fa-check" />
        </button>
        <button
            type="button"
            class="btn btn-outline-danger btn-sm"
            @click.prevent="cancelEditReference()"
        >
            <i class="fas fa-fw fa-times" />
        </button>
    </div>
</template>

<script>
    export default {

    };
</script>

<style lang='scss' scoped></style>