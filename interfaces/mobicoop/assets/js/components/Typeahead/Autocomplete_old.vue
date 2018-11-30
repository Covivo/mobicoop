<template>
    <input
            :data="addresses"
            v-model="addressSearch"
            :serializer="s => s.text"
            placeholder="Type an address..."
            @hit="selectedAddress = $event"
    />
</template>

<script>
    import _ from 'underscore'

    const API_URL = 'http://localhost:8080/GeoSearch?input=:query'

    export default {
        name: 'Autocomplete',

        data() {
            return {
                addresses: [],
                addressSearch: '',
                selectedAddress: null
            }
        },

        methods: {
            async getAddresses(query) {

                const res = await fetch(API_URL.replace(':query', query))
                const suggestions = await res.json()
                this.addresses = suggestions['hydra:member'][0]['locality']
                //for
                console.log(this.addresses)
            }
        },
        watch: {
            addressSearch: _.debounce(function(addr) { this.getAddresses(addr) }, 500)
        }
    }
</script>