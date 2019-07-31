<template>
  <v-autocomplete
    v-model="address"
    :loading="isLoading"
    :items="items"
    :label="label"
    :search-input.sync="search"
    hide-no-data
    hide-details
    clearable
    item-text="concatenedAddr"
    color="success"
    return-object
    no-filter
    @change="changedAddress()"
  >
    <!-- template for selected item  -->
    <template v-slot:selection="data">
      <template>
        <v-list>
          <v-list-item>
            <v-list-item-avatar>
              <v-icon
                v-text="data.item.icon"
              />
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title v-html="data.item.concatenedAddr" />
              <v-list-item-subtitle v-html="data.item.addressCountry" />
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </template>
    </template>
    <!-- template for list items  -->
    <template v-slot:item="data">
      <template>
        <v-list>
          <v-list-item>
            <v-list-item-avatar>
              <v-icon
                v-text="data.item.icon"
              />
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title v-html="data.item.concatenedAddr" />
              <v-list-item-subtitle v-html="data.item.addressCountry" />
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </template>
    </template>
  </v-autocomplete>
</template>

<script>
import Translations from "../../../translations/components/GeoComplete.json";
import axios from "axios";
import debounce from "lodash/debounce";

const defaultString = {
  type: String,
  default: ""
};
export default {
  i18n: {
    messages: Translations
  },
  props: {
    url: defaultString,
    label: defaultString
  },
  data () {
    return {
      entries: [],
      isLoading: false,
      search: null,
      address: null,
      filter: null
    }
  },
  computed: {
    items () {
      return this.entries.map(entry => {
        const concatenedAddr = entry.concatenedAddr
        const icon = 'mdi-map-marker'
        return Object.assign({}, entry, { concatenedAddr, icon })
      })
    },
  },
  watch: {
    search (val) {
      if (val) {
        if (val.length>2){
          val && val !== this.address && this.getAsyncData(val)
        }
      }
    }
  },
  methods: {
    changedAddress: function() {
      this.$emit('address-selected',this.address);
    },
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
          //console.error(res.data['hydra:member']);
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