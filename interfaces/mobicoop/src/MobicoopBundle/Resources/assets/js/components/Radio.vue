<template>
    <b-radio 
        :v-model="model"
        :native-value="value">
        :label
    </b-radio>
</template>

<script>
    const defaultString = {
        type: String,
        default: ''
    }
    export default {
        components: { Radio },
        name: "radio",
        props: {
            model: defaultString,
        },
        data () {
            return this.initialData();
        },
        methods: {
            initialData(){
                return {
                    value: ''
                }
            }
        }
    }
</script>