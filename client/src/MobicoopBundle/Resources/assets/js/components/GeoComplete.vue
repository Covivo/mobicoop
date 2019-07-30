<template>
  <v-autocomplete
    v-model="address"
    :loading="isLoading"
    :items="items"
    :search-input.sync="search"
    hide-no-data
    hide-details
    item-text="concatenedAddr"
    color="success"
    return-object
    no-filter
  />
</template>

<script>
import axios from "axios";
import debounce from "lodash/debounce";

const defaultString = {
  type: String,
  default: ""
};
export default {
  props: {
    url: defaultString,
  },
  data () {
    return {
      entries: [],
      isLoading: false,
      search: null,
      address: null,
      filter: null,
    }
  },
  computed: {
    items () {
      return this.entries.map(entry => {
        const concatenedAddr = entry.concatenedAddr
        return Object.assign({}, entry, { concatenedAddr })
      })
    },
  },
  watch: {
    search (val) {
      if (val.length>2){
        val && val !== this.address && this.getAsyncData(val)
      }
    }
  },
  methods: {
    getAsyncData: debounce(function(val) {
      this.isLoading = true;
      axios
        .get(`${this.url}${val}`)
        .then(res => {
          this.isLoading = false;
          
          // Add a property concatenedAddr to be shown into the autocomplete field after selection
          let addresses = res.data['hydra:member'];
          // No Adresses return, we stop here
          if(!addresses.length){return;}
          addresses.forEach( (adress,adressKey) => {
            let streetAddress = addresses[adressKey].streetAddress ? `${addresses[adressKey].streetAddress} ` : '';
            let postalCode = addresses[adressKey].postalCode ? `${addresses[adressKey].postalCode} ` : '';
            let addressLocality = addresses[adressKey].addressLocality ? addresses[adressKey].addressLocality : '';
            addresses[adressKey].concatenedAddr = `${streetAddress}${postalCode}${addressLocality}`;
            if(!addressLocality){ // No locality return, do not show them (region, department ..)
              addresses.splice(adressKey,1);
            } 
          })
          // Set Data & show them
          if(this.isLoading) return; // Another request is fetching, we do not show the previous one
          this.entries = [...res.data['hydra:member']];
        })
        .catch(err => {
          this.items = [];
          console.error(err);
        })
        .finally(() => (this.isLoading = false))

    }, 1000),

  }
}
</script>