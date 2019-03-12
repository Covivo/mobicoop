<template>
    <b-radio 
        :v-model="model"
        :name="name"
        :id="id"
        :native-value="value"
        :checked="checked"
        >
        {{ label }}
    </b-radio>
</template>

<script>
    const defaultString = {
        type: String,
        default: ''
    }
    const defaultBoolean = {
        type: Boolean,
        default: false
    }
    export default {
        name: "vradio",
        props: {
            id: defaultString,
            name: defaultString,
            model: defaultString,
            value: defaultString,
            label: defaultString,
            checked: defaultBoolean
        }
        // data () {
        //     console.log(this.model, this.checked, this.value)
        //     return {
        //         [this.model]: this.checked ? this.value.toString() : null
        //     }
        // },
        // mounted() {
        //     if (this.checked) this[this.model] = this.value.toString(); 
        // }
        // methods: {
        //     initialData(){
        //         return {
        //             model: '',
        //             value: '',
        //             label: ''
        //         }
        //     }
        // }
    }
</script>