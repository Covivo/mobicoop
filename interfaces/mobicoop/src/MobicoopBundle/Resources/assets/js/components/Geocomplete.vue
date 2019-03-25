<template>
  <section>
    <b-field>
      <b-autocomplete
        v-model="address"
        :data="data"
        :placeholder="placeholder"
        field="Depart"
        icon="search-location"
        icon-pack="fa"
        :loading="isFetching"
        @input="getAsyncData"
        @select="option => selected = option"
      >
        <template slot-scope="props">
          <div class="media">
            <div class="media-left">
              <img width="32" :src="`https://image.tmdb.org/t/p/w500/${props.option.poster_path}`">
            </div>
            <div class="media-content searchResult">
              <small>
                <b>{{ props.option.addressLocality }}</b>
                <b v-if="props.option.postalCode">({{ props.option.postalCode }})</b>
              </small>
              <br>
              <strong>{{ props.option.addressCountry.toUpperCase() }}</strong>
            </div>
          </div>
        </template>
      </b-autocomplete>
    </b-field>
  </section>
  <!-- <div>
    <autocomplete
      :source="url"
      :results-display="formattedDisplay"
      :name="name"
      :placeholder="placeholder"
      :input-class="iclass"
      :required="required"
      @selected="onSelected"
    ></autocomplete>
    <input type="hidden" :name="streetaddress" :value="valstreetAddress">
    <input type="hidden" :name="postalcode" :value="valpostalCode">
    <input type="hidden" :name="addresslocality" :value="valaddressLocality">
    <input type="hidden" :name="addresscountry" :value="valaddressCountry">
    <input type="hidden" :name="longitude" :value="vallongitude">
    <input type="hidden" :name="latitude" :value="vallatitude">
  </div>-->
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
  name: "geocomplete",
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
        valstreetAddress: "",
        valpostalCode: "",
        valaddressLocality: "",
        valaddressCountry: "",
        vallongitude: 0,
        vallatitude: 0,
        address: "",
        data: [],
        selected: null,
        isFetching: false
      };
    },
    getAsyncData: debounce(function(address) {
      this.isFetching = true;
      axios
        .get(`${this.url}${address}`)
        .then(res => {
          this.data = [...res.data];
        })
        .catch(err => {
          this.data = [];
          console.error(err);
        })
        .then(_ => {
          this.isFetching = false;
        });
    }, 700),
    formattedDisplay(result) {
      let resultToShow = `${result.streetAddress} ${result.postalCode} ${
        result.addressLocality
      } ${result.addressCountry}`;
      return resultToShow.trim();
    },
    onSelected(value) {
      for (let property in value.selectedObject) {
        this["val" + property] =
          typeof value.selectedObject[property] === "string"
            ? value.selectedObject[property].trim()
            : value.selectedObject[property];
      }
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../css/_variables";
.searchResult {
  color: $mb-blue;
}
</style>
