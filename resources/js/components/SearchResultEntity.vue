<template>
    <div class="d-flex flex-row align-items-center">
        <i class="fas fa-fw fa-monument fa-lg mx-2" />
        <div class="media col px-0 ps-1">
            <div class="media-body small">
                <h6
                    class="my-0"
                    style="white-space: pre;"
                >
                    {{ data.name }}
                </h6>
            </div>
            <div class="ms-3 media-image-placeholder" />
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
        },
    }
</script>
