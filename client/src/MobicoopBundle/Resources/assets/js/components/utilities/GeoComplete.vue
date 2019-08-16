<template>
  <v-form :ref="name">
    <v-autocomplete
      v-model="address"
      :loading="isLoading"
      :items="items"
      :label="label"
      :hint="hint"
      :search-input.sync="search"
      hide-no-data
      clearable
      item-text="concatenedAddr"
      color="success"
      return-object
      no-filter
      persistent-hint
      :required="required"
      :rules="geoRules"
      :disabled="disabled"
      @change="changedAddress()"
    >
      <!-- template for selected item  -->
      <template v-slot:selection="data">
        <template>
          {{ data.item.concatenedAddr }}
        </template>
      </template>
      <!-- template for list items  -->
      <template v-slot:item="data">
        <template>
          <v-list>
            <v-list-item>
              <v-list-item-avatar>
                <v-icon v-text="data.item.icon" />
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
  </v-form>
</template>

<script>
import axios from "axios";
import debounce from "lodash/debounce";
import merge from "lodash/merge";

import Translations from "@translations/components/utilities/GeoComplete.json";
import TranslationsClient from "@clientTranslations/components/utilities/GeoComplete.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

const defaultString = {
  type: String,
  default: ""
};
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    url: defaultString,
    label: defaultString,
    token: defaultString,
    disabled: {
      type: Boolean,
      default: false
    },
    hint: null,
    required: Boolean,
    requiredError: defaultString,
    name: String,
    initAddress: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      entries: [],
      isLoading: false,
      search: null,
      address: null,
      filter: null
    };
  },
  computed: {
    items() {
      return this.entries.map(entry => {
        const concatenedAddr = entry.concatenedAddr;
        const icon = entry.icon;
        return Object.assign({}, entry, { concatenedAddr, icon });
      });
    },
    geoRules() {
      if (this.required) {
        return [
          v => !!v || this.requiredError
        ];
      }
      return [];
    }
  },
  watch: {
    search(val) {
      if (val) {
        if (val.length > 2) {
          val && val !== this.address && this.getAsyncData(val);
        }
      }
    },
    initAddress() {
      this.address = this.initAddress;
      this.entries = [];
      if (this.address) {
        this.entries.push(this.address);
      }
    }
  },
  methods: {
    changedAddress() {
      this.$emit("address-selected", this.address);
    },
    getAsyncData: debounce(function(val) {
      this.isLoading = true;
      axios
        .get(`${this.url}${val}` + (this.token ? "&token=" + this.token : ""))
        .then(res => {
          this.isLoading = false;

          // Add a property concatenedAddr to be shown into the autocomplete field after selection
          let addresses = res.data["hydra:member"];
          // No Adresses return, we stop here
          if (!addresses.length) {
            return;
          }
          addresses.forEach((address, addressKey) => {
            let houseNumber = address.houseNumber
              ? `${address.houseNumber} `
              : "";
            let street = address.street ? `${address.street} ` : "";
            let streetAddress = address.streetAddress
              ? `${address.streetAddress} `
              : "";
            let computedStreet = streetAddress
              ? `${streetAddress}`
              : `${houseNumber}${street}`;
            let postalCode = address.postalCode ? `${address.postalCode} ` : "";
            let addressLocality = address.addressLocality
              ? address.addressLocality
              : "";
            addresses[
              addressKey
            ].concatenedAddr = `${computedStreet}${postalCode}${addressLocality}`;
            addresses[addressKey].icon = "mdi-map-marker";
            if (address.home) {
              addresses[
                addressKey
              ].concatenedAddr = `${address.name} - ${computedStreet}${postalCode}${addressLocality}`;
              addresses[addressKey].icon = "mdi-home-map-marker";
            } else if (address.relayPoint) {
              addresses[addressKey].icon = "mdi-parking";
            } else if (address.name) {
              addresses[
                addressKey
              ].concatenedAddr = `${address.name} - ${computedStreet}${postalCode}${addressLocality}`;
              addresses[addressKey].icon = "mdi-map";
            }
          });
          addresses.forEach((address, addressKey) => {
            let addressLocality = address.addressLocality
              ? address.addressLocality
              : "";
            if (!addressLocality) {
              // No locality return, do not show them (region, department ..)
              addresses.splice(addressKey, 1);
            }
          });
          // Set Data & show them
          if (this.isLoading) return; // Another request is fetching, we do not show the previous one
          this.entries = [...res.data["hydra:member"]];
        })
        .catch(err => {
          this.items = [];
          console.error(err);
        })
        .finally(() => (this.isLoading = false));
    }, 1000)
  }
};
</script>