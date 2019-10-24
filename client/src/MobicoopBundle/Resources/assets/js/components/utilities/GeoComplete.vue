<template>
  <v-form :ref="name">
    <v-autocomplete
      v-model="address"
      :loading="isLoading"
      :items="items"
      :label="label + (showRequired ? ' *' : '')"
      :hint="hint"
      :search-input.sync="search"
      hide-no-data
      clearable
      item-text="selectedDisplayedLabel"
      color="primary"
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
          {{ data.item.selectedDisplayedLabel }}
        </template>
      </template>
      <!-- template for list items  -->
      <template v-slot:item="data">
        <template>
          <v-list>
            <v-list-item>
              <v-list-item-avatar v-if="displayIcon">
                <v-icon v-text="data.item.icon" />
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title v-html="data.item.displayedLabel" />
                <v-list-item-subtitle v-html="data.item.displayedSecondLabel" />
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
  default: null
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
    displayNameInSelected: {
      type: Boolean,
      default: true
    },
    displayIcon: {
      type: Boolean,
      default: true
    },
    displayRegion: {
      type: Boolean,
      default: true
    },
    hint: defaultString,
    required: Boolean,
    showRequired: {
      type: Boolean,
      default: false
    },
    requiredError: defaultString,
    name: defaultString,
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
        return Object.assign({}, entry);
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
    initAddress: {
      immediate: true,
      handler(newVal, oldVal) {
        this.address = this.initAddress;
        this.entries = [];
        if (this.address) {
          this.address.icon = "mdi-map-marker";
          this.address.displayedLabel = `${this.address.displayLabel[0]}`;
          this.address.displayedSecondLabel = `${this.address.displayLabel[1]}`;
          this.address.selectedDisplayedLabel = `${this.address.displayLabel[0]}`;
          if (this.address.home) {
            this.address.displayedLabel = `${this.address.name} - ${this.address.displayLabel[0]}`;
            if (this.displayNameInSelected) this.address.selectedDisplayedLabel = `${this.address.name} - ${this.address.displayLabel[0]}`;
            if (this.displayIcon) this.address.icon = "mdi-home-map-marker";
          } else if (this.address.relayPoint) {
            if (this.displayIcon) this.address.icon = "mdi-parking";
          } else if (this.address.name) {
            this.address.displayedLabel = `${this.address.name} - ${this.address.displayLabel[0]}`;
            if (this.displayNameInSelected) this.address.selectedDisplayedLabel = `${this.address.name} - ${this.address.displayLabel[0]}`;
            if (this.displayIcon) this.address.icon = "mdi-map";
          } else if (this.address.venue) {
            if (this.displayIcon) this.address.icon = "mdi-map-marker-radius";
          }
          this.entries.push(this.address);
        } 
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

          // Modify property displayLabel to be shown into the autocomplete field after selection
          let addresses = res.data["hydra:member"];
          // No Adresses return, we stop here
          if (!addresses.length) {
            return;
          }
          addresses.forEach((address, addressKey) => {
            addresses[addressKey].icon = "mdi-map-marker";
            addresses[addressKey].displayedLabel = `${address.displayLabel[0]}`;
            addresses[addressKey].displayedSecondLabel = `${address.displayLabel[1]}`;
            addresses[addressKey].selectedDisplayedLabel = `${address.displayLabel[0]}`;
            if (address.home) {
              addresses[addressKey].displayedLabel = `${address.name} - ${address.displayLabel[0]}`;
              if (this.displayNameInSelected) addresses[addressKey].selectedDisplayedLabel = `${address.name} - ${address.displayLabel[0]}`;
              if (this.displayIcon) addresses[addressKey].icon = "mdi-home-map-marker";
            } else if (address.relayPoint) {
              if (this.displayIcon) addresses[addressKey].icon = "mdi-parking";
            } else if (address.name) {
              addresses[addressKey].displayedLabel = `${address.name} - ${address.displayLabel[0]}`;
              if (this.displayNameInSelected) addresses[addressKey].selectedDisplayedLabel = `${address.name} - ${address.displayLabel[0]}`;
              if (this.displayIcon) addresses[addressKey].icon = "mdi-map";
            } else if (address.venue) {
              if (this.displayIcon) addresses[addressKey].icon = "mdi-map-marker-radius";
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