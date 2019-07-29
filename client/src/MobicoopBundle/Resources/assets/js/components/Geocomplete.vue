<template>
  <section>
    <b-field>
      <b-autocomplete
        :id="name"
        ref="autocomplete"
        :data="data"
        :placeholder="placeholder"
        :open-on-focus="true"
        field="concatenedAddr"
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
            <div class="searchResult">
              <em>{{ props.option.streetAddress }}</em>
              <small>
                <b>{{ props.option.addressLocality }}</b>
                <b v-if="props.option.postalCode">({{ props.option.postalCode }})</b>
              </small>
              <br>
              <strong
                v-if="props.option.addressCountry"
              >{{ props.option.addressCountry.toUpperCase() }}</strong>
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
    selected: {
      type: Object,
      default: () => {
        return {
          concatenedAddr: ""
        };
      }
    }
  },
  data() {
    return this.initialData();
  },
  methods: {
    initialData() {
      return {
        focus: false,
        data: [],
        isFetching: false,
        nbOfRequest: 0
      };
    },
    setFocus(val) {
      this.focus = val;
    },
    getAsyncData: debounce(function(address) {
      if (!this.focus) return; // We did not select the value, so we stop here
      if (address == "") return; // the search is null, we stop
      this.isFetching = true;
      axios
        .get(`${this.url}${address}`)
        .then(res => {
          this.isFetching = false;

          // Add a property concatenedAddr to be shown into the autocomplete field after selection
          let addresses = res.data["hydra:member"];
          // No Adresses return, we stop here
          if (!addresses.length) {
            return;
          }
          addresses.forEach((adress, adressKey) => {
            let streetAddress = addresses[adressKey].streetAddress
              ? `${addresses[adressKey].streetAddress} `
              : "";
            let postalCode = addresses[adressKey].postalCode
              ? `${addresses[adressKey].postalCode} `
              : "";
            let addressLocality = addresses[adressKey].addressLocality
              ? addresses[adressKey].addressLocality
              : "";
            addresses[
              adressKey
            ].concatenedAddr = `${streetAddress}${postalCode}${addressLocality}`;
            if (!addressLocality) {
              // No locality return, do not show them (region, department ..)
              addresses.splice(adressKey, 1);
            }
          });
          // Set Data & show them
          if (this.isFetching) return; // Another request is fetching, we do not show the previous one
          this.data = [...res.data["hydra:member"]];
        })
        .catch(err => {
          this.data = [];
          console.error(err);
          this.isFetching = false;
        });
    }, 700),
    // switch data , from another autocmplete component.
    swap(data, selected) {
      this.data = data; // switch the list
      this.$refs.autocomplete.setSelected(selected); // set the selection to the sent
    },
    onSelected(value) {
      this.$emit("geoSelected", { ...value, name: this.name });
    }
  }
};
</script>
