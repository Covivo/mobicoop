<template>
    <b-radio 
        :v-model="model"
        :native-value="value">
        toto
    </b-radio>
</template>

<script>
    const defaultString = {
        type: String,
        default: ''
    }
    export default {
        name: "radio",
        props: {
            model: defaultString,
            value: defaultString,
            label: defaultString
        },
        data () {
            return this.initialData();
        },
        methods: {
            initialData(){
                return {
                    model: '',
                    value: '',
                    label: ''
                }
            }
        }
    }
</script>