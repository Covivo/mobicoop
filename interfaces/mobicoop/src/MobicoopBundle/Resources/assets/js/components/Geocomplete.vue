<template>
  <section>
    <b-field>
      <b-autocomplete
        :id="name"
        :data="data"
        :placeholder="placeholder"
        field="addressLocality"
        :open-on-focus="true"
        icon="search-location"
        icon-pack="fa"
        :loading="isFetching"
        @input="getAsyncData"
        @select="onSelected"
      >
        <template slot-scope="props">
          <div class="media">
            <div class="media-left">
              <b-icon
                pack="fas"
                icon="arrow-alt-circle-right"
                type="is-mobicoopblue"
              />
            </div>
            <div class="searchResult">
              <em>{{ props.option.streetAddress }}</em>
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
      this.selected = value;
      this.$emit("geoSelected", { ...value, name: this.name });
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../css/_variables";
.searchResult {
  color: $mb-blue;
  text-decoration: none !important;
}

.dropdown-item {
  text-decoration: none !important;
}
</style>
