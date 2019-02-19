<template>
    <section>
        <p class="content"><b>Selected:</b> {{ selected }}</p>
        <b-field label="Find a place">
            <b-autocomplete
                    v-model="name"
                    :data="data"
                    placeholder="Enter a place"
                    field="title"
                    :loading="isFetching"
                    @keyup.native="getAsyncData"
                    @select="option => selected = option">

                <template slot-scope="props">
                    <div class="media">
                        <div class="media-content">
                            <b>{{ props.option.addressLocality }}</b>
                            <br>

                            {{ props.option.postalCode }},
                            {{ props.option.addressCountry }}
                            <br>
                            coordonnées : {{ props.option.latitude }} - {{ props.option.longitude }}
                        </div>
                    </div>
                </template>
            </b-autocomplete>
        </b-field>
    </section>
</template>

<script>
    import Vue from 'vue'
    import VueResource from 'vue-resource'
    import debounce from 'lodash/debounce'

    // I need to use VueResource for $http.
    Vue.use(VueResource)
    const API_URI = 'api.mobicoop.io'

    export default {
        data() {
            return {
                data: [],
                name: '',
                selected: null,
                isFetching: false
            }
        },
        methods: {
            getAsyncData: debounce(function () {
                if (!this.name.length) {
                    this.data = []
                    return
                }
                this.isFetching = true
                this.$http.get(`http://${API_URI}/geo_search?input=${this.name}`)
                    .then(({ data }) => {
                        this.data = []
                        data['hydra:member'].forEach((item) => this.data.push(item))
                        console.log(data)
                    })
                    .catch((error) => {
                        this.data = []
                        throw error
                    })
                    .finally(() => {
                        this.isFetching = false
                    })
            }, 500)
        }
    }
</script>