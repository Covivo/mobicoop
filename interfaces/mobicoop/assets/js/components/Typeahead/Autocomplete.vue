<template>
    <vue-bootstrap-typeahead
            :data="addresses"
            v-model="addressSearch"
            size="lg"
            placeholder="Type an address..."
            @hit="selectedAddress = $event"
    />
</template>

<script>
    import _ from 'underscore'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'

    const API_URL = 'http://localhost:8080/GeoSearch?input=:query'

    export default {
        name: 'Autocomplete',
        components: {
            /* eslint-disable vue/no-unused-components */
            'vue-bootstrap-typeahead' : VueBootstrapTypeahead
        },

        data() {
            return {
                addresses: [],
                addressSearch: '',
                selectedAddress: null
            }
        },

        watch: {
            addressSearch: _.debounce(function(addr) { this.getAddresses(addr) }, 500)
        },

        methods: {
            async getAddresses(query) {

                const res = await fetch(API_URL.replace(':query', query))
                const suggestions = await res.json()
                const suggestionsArray = []
                for (var i in suggestions['hydra:member']){
                    if(suggestions['hydra:member'][i]['locality'] != null)
                        suggestionsArray.push(suggestions['hydra:member'][i]['locality'])
                }
                console.log(suggestionsArray)
                this.addresses = suggestionsArray
            }
        }
    }


</script>