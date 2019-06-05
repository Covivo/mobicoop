<template>
  <section>
    <b-field>
      <b-autocomplete
        :id="name"
        :data="data"
        field="concatenedAddr"
        :placeholder="placeholder"
        :open-on-focus="true"
        icon-pack="fa"
        :loading="isFetching"
        @input="getAsyncData"
        @select="onSelected"
        @focus="setFocus(true)"
        @typing="setFocus(true)"
        @blur="setFocus(false)"
      >
        <template slot-scope="props">
          <div class="media">
            <div class="media-left">
              <b-icon
                pack="fas"
                icon="arrow-alt-circle-right"
              />
            </div>
            <div
              class="searchResult"
            >
              <em>{{ props.option.streetAddress }}</em>
              <small>
                <b>{{ props.option.addressLocality }}</b>
                <b v-if="props.option.postalCode">({{ props.option.postalCode }})</b>
              </small>
              <br>
              <strong v-if="props.option.addressCountry">{{ props.option.addressCountry.toUpperCase() }}</strong>
            </div>
          </div>
        </template>
      </b-autocomplete>
    </b-field>
  </section>
</template>

<script>
// import Autocomplete from "vuejs-auto-complete";
import axios from "axios";
import debounce from "lodash/debounce";

const defaultString = {
  type: String,
  default: ""
};
export default {
  name: "Geocomplete",
  props: {
    url: defaultString,
    name: defaultString,
    required: defaultString,
    placeholder: defaultString,
    iclass: defaultString,
    streetaddress: defaultString,
    postalcode: defaultString,
    addresslocality: defaultString,
    addresscountry: defaultString,
    longitude: defaultString,
    latitude: defaultString
  },
  data() {
    return this.initialData();
  },
  methods: {
    initialData() {
      return {
        focus: false,
        valstreetAddress: "",
        valpostalCode: "",
        valaddressLocality: "",
        valaddressCountry: "",
        vallongitude: 0,
        vallatitude: 0,
        address: "",
        data: [],
        selected: null,
        isFetching: false,
        nbOfRequest: 0
      };
    },
    setFocus(val){
      this.focus = val;
    },
    getAsyncData: debounce(function(address) {
      if(!this.focus) return; // We did not select the value, so we stop here
      this.isFetching = true;
      axios
        .get(`${this.url}${address}`)
        .then(res => {
          this.isFetching = false;
          
          // Add a property concatenedAddr to be shown into the autocomplete field after selection
          let addresses = res.data;
          // No Adresses return, we stop here
          if(!addresses.length){return;}
          addresses.forEach( (adress,adressKey) => {
            let streetAddress = addresses[adressKey].streetAddress ? `${addresses[adressKey].streetAddress} ` : '';
            let postalCode = addresses[adressKey].postalCode ? `${addresses[adressKey].postalCode} ` : '';
            let addressLocality = addresses[adressKey].addressLocality ? addresses[adressKey].addressLocality : '';
            addresses[adressKey].concatenedAddr = `${streetAddress}${postalCode}${addressLocality}`
          })
          // Set Data & show them
          if(this.isFetching) return; // Another request is fetching, we do not show the previous one
          this.data = [...res.data];
        })
        .catch(err => {
          this.data = [];
          console.error(err);
          this.isFetching = false;
        })
    }, 700),
    onSelected(value) {
      this.selected = value;
      this.$emit("geoSelected", { ...value, name: this.name });
      // this.focus = false;
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../css/_variables";
</style>
