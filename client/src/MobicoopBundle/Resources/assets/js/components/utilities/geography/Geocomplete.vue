<template>
  <div>
    <v-autocomplete
      v-model="selection"
      :label="label + (required ? ' *' : '')"
      :search-input.sync="search"
      :items="propositions"
      hide-no-data
      no-filter
      :required="required"
      :hint="hint"
      :rules="rules"
      :loading="loading"
      return-object
      :clearable="!chip"
      @change="change"
    >
      <template
        v-if="chip"
        v-slot:selection="data"
      >
        <template>
          <v-chip
            class="ma-2"
            style="max-width: 95%;"
            :color="chipColor(data.item.type)"
            :text-color="chipTextColor(data.item.type)"
            close
            label
            @click:close="clearSelection"
          >
            <v-icon
              v-if="data.item.icon"
              left
            >
              {{
                data.item.icon
              }}
            </v-icon>
            <v-icon
              v-else
              left
            >
              mdi-earth
            </v-icon>
            <span
              class="chip-overflow font-weight-medium"
            >{{ data.item.text }}
            </span>
            <country-flag
              v-if="data.item.value.countryCode != country"
              :country="data.item.value.countryCode"
              size="small"
            />
          </v-chip>
        </template>
      </template>

      <template
        v-else
        v-slot:selection="data"
      >
        {{ data.item.text }}
        <country-flag
          v-if="data.item.value.countryCode != country"
          :country="data.item.value.countryCode"
          size="small"
        />
      </template>
      <template
        v-if="!chip && selection"
        v-slot:prepend-inner
      >
        <v-icon
          v-if="selection.icon"
          :color="noChipColor(selection.type)"
          left
        >
          {{
            selection.icon
          }}
        </v-icon>
        <v-icon
          v-else
          left
        >
          mdi-earth
        </v-icon>
      </template>
      <!-- template for list items  -->
      <template v-slot:item="data">
        <template>
          <v-list-item-avatar
            v-if="data.item.icon"
            :class="iconColor(data.item.type)"
          >
            <v-icon :class="iconTextColor(data.item.type)">
              {{ data.item.icon }}
            </v-icon>
          </v-list-item-avatar>
          <v-list-item-content>
            <v-list-item-title :class="titleColor(data.item.type)">
              {{ data.item.propositionTitle }}
              <country-flag
                v-if="data.item.value.countryCode != country"
                :country="data.item.value.countryCode"
                size="small"
              />
            </v-list-item-title>
            <v-list-item-subtitle
              :class="subTitleColor(data.item.type)"
              v-text="data.item.propositionText"
            />
          </v-list-item-content>
        </template>
      </template>
    </v-autocomplete>
  </div>
</template>

<script>
import axios from "axios";
import { debounce } from "lodash";
import CountryFlag from "vue-country-flag";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/geography/Geocomplete/";

export default {
  name: "Geocomplete",

  components: {
    CountryFlag,
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },

  props: {
    chip: {
      type: Boolean,
      default: true
    },
    uri: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: null
    },
    hint: {
      type: String,
      default: null
    },
    address: {
      type: Object,
      default: null
    },
    required:  {
      type: Boolean,
      default: false
    },
    country: {
      type: String,
      default: 'FR'
    },
    sort: {
      type: Array,
      default() {
        return [
          "user",
          "relaypoint",
          "housenumber",
          "street",
          "locality",
          "venue",
        ];
      }
    },
    colors: {
      type: Object,
      default() {
        return {
          locality: {
            "no-chip": "black",
            chip: "indigo",
            "chip-text": "indigo lighten-5",
            icon: "indigo accent-2",
            "icon-text": "white--text",
            title: "indigo--text text--darken-3",
            subtitle: "indigo--text text--lighten-1",
          },
          street: {
            "no-chip": "black",
            chip: "deep-purple",
            "chip-text": "deep-purple lighten-5",
            icon: "deep-purple accent-2",
            "icon-text": "white--text",
            title: "deep-purple--text text--darken-3",
            subtitle: "deep-purple--text text--lighten-1",
          },
          housenumber: {
            "no-chip": "black",
            chip: "purple",
            "chip-text": "purple lighten-5",
            icon: "purple accent-2",
            "icon-text": "white--text",
            title: "purple--text text--darken-3",
            subtitle: "purple--text text--lighten-1",
          },
          venue: {
            "no-chip": "black",
            chip: "pink",
            "chip-text": "pink lighten-5",
            icon: "pink accent-2",
            "icon-text": "white--text",
            title: "pink--text text--darken-3",
            subtitle: "pink--text text--lighten-1",
          },
          other: {
            "no-chip": "black",
            chip: "teal",
            "chip-text": "teal lighten-5",
            icon: "teal accent-2",
            "icon-text": "white--text",
            title: "teal--text text--darken-3",
            subtitle: "teal--text text--lighten-1",
          },
          relaypoint: {
            "no-chip": "black",
            chip: "teal",
            "chip-text": "teal lighten-5",
            icon: "teal accent-2",
            "icon-text": "white--text",
            title: "teal--text text--darken-3",
            subtitle: "teal--text text--lighten-1",
          },
          user: {
            "no-chip": "black",
            chip: "teal",
            "chip-text": "teal lighten-5",
            icon: "teal accent-2",
            "icon-text": "white--text",
            title: "teal--text text--darken-3",
            subtitle: "teal--text text--lighten-1",
          },
        };
      },
    },
  },

  data: () => ({
    search: null,
    items: [],
    selection: null,
    loading: null,
    restrict: []
  }),

  computed: {
    rules() {
      if (this.required) {
        return [
          v => !!v || this.$t('required')
        ];
      }
      return [];
    },
    propositions() {
      return this.sort
        .map( (type) => this.propositionsForType(type))
        .filter( (type) => type !== null)
        .flat();
    },
    selectionToAddress() {
      if (this.selection)
        return {
          "houseNumber":this.selection.value.houseNumber,
          "street":this.selection.value.streetName,
          "postalCode":this.selection.value.postalCode,
          "addressLocality":this.selection.value.locality,
          "region":this.selection.value.region,
          "regionCode":this.selection.value.regionCode,
          "macroRegion":this.selection.value.macroRegion,
          "addressCountry":this.selection.value.country,
          "countryCode":this.selection.value.countryCode,
          "latitude":this.selection.value.lat,
          "longitude":this.selection.value.lon,
          "name":this.selection.value.name,
          "providedBy":this.selection.value.provider,
          "distance":this.selection.value.distance,
          "type":this.selection.type,
          "id":this.selection.value.id
        }
      return null;
    },
    addressToSelection() {
      if (this.address)
        return  {
          "houseNumber":this.address.houseNumber,
          "streetName":this.address.street,
          "postalCode":this.address.postalCode,
          "locality":this.address.addressLocality,
          "region":this.address.region,
          "macroRegion":this.address.macroRegion,
          "country":this.address.addressCountry,
          "countryCode":this.address.countryCode,
          "lat":this.address.latitude,
          "lon":this.address.longitude,
          "name":this.address.name,
          "provider":this.address.providedBy,
          "distance":this.address.distance,
          "type":this.address.type,
          "id":this.address.id,
          "regionCode":this.address.regionCode
        };
      return null;
    }
  },

  watch: {
    address: {
      immediate: true,
      handler() {
        this.setSelection();
      }
    },
    search(val) {
      if (val) {
        this.debouncedSearch();
      } else if (val === "") {
        this.clearPropositions();
      }
    },
  },

  created() {
    this.debouncedSearch = debounce(this.getPropositions, 500);
  },

  methods: {
    propositionsForType(type) {
      const propositions = this.items
        .filter((item) => item.type == type)
        .map((item) => this.createProposition(item));
      if (propositions.length>0) {
        return [
          { divider: true },
          { header: this.$t(type) },
          ...propositions
        ];
      }
      return null;
    },
    createProposition(item) {
      return {
        text: this.selectionText(item),
        propositionTitle: this.propositionTitle(item),
        propositionText: this.propositionText(item),
        value: item,
        group: this.$t(item.type),
        type: item.type,
        icon: this.getIcon(item.type)
      }
    },
    selectionText(item) {
      let text = "";
      if (item.type == "street") {
        text += item.streetName + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      if (item.type == "housenumber") {
        text += item.houseNumber + ", " + item.streetName + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      if (item.type == "venue" || item.type == "relaypoint" || item.type == "user") {
        text += item.name + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      text += item.locality;
      if (item.type == "locality" && item.regionCode !== null)
        text += ", " + item.regionCode;
      if (item.type == "locality" && item.countryCode !== this.country)
        text += ", " + item.country;
      return text;
    },
    propositionTitle(item) {
      let text = "";
      if (item.type == "street") {
        text += item.streetName + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      if (item.type == "housenumber") {
        text += item.houseNumber + ", " + item.streetName + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      if (item.type == "venue" || item.type == "relaypoint" || item.type == "user") {
        text += item.name + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      text += item.locality;
      return text;
    },
    propositionText(item) {
      let text = "";
      if (item.regionCode !== null) text += item.regionCode;
      if (item.region !== null) text += (text != "" ? ", " : "") + item.region;
      if (item.macroRegion !== null)
        text += (text != "" ? ", " : "") + item.macroRegion;
      if (item.country !== null) text += (text != "" ? ", " : "") + item.country;
      return text;
    },
    getIcon(type) {
      switch(type) {
      case "locality" : return "mdi-city-variant";
      case "street" : return "mdi-road-variant";
      case "housenumber" : return "mdi-home-map-marker";
      case "venue" : return "mdi-map-marker";
      case "relaypoint" : return "mdi-parking";
      case "user" : return "mdi-home-heart";
      }
      return "mdi-earth";
    },
    setSelection() {
      if (!this.address) {
        this.clearSelection();
      } else if (!this.selection || !(
        this.selection.id === this.address.id &&
        this.selection.type === this.address.type
      )) {
        this.selection = this.createProposition(this.addressToSelection);
        this.items = [this.selection.value];
      }
    },
    getPropositions() {
      if (
        this.search &&
        (
          !this.selection ||
          (
            this.selection &&
            this.selection.text &&
            this.selection.text !== this.search
          )
        )
      ) {
        this.clearPropositions();
        this.loading = true;
        axios
          .get(this.uri + "?search=" + this.search, {
            headers: { Authorization: 'Bearer ' + this.$store.getters['a/token'] },
          })
          .then((response) => {
            this.setItems(response.data["hydra:member"]);
          })
          .catch(error => {
            if (error.response) {
              switch (error.response.status) {
              case 401:
                if (error.response.data.message == 'Expired JWT Token') {
                  return this.refreshToken()
                    .then( () => {
                      this.getPropositions();
                    });
                }
              }
            }
            this.clearPropositions();
          })
          .finally(() => {
            this.loading = false;
          });
      }
    },
    clearPropositions() {
      this.items = [];
    },
    clearSelection() {
      this.selection = null;
      this.clearPropositions();
    },
    setItems(items) {
      if (this.restrict.length == 0) {
        this.items = items;
      } else {
        this.items = items.filter((item) =>
          this.restrict.includes(item.type)
        );
      }
    },
    change(address) {
      this.$emit("address-selected", this.selectionToAddress);
      if (!address) this.clearPropositions();
    },
    noChipColor(type) {
      return this.colors[type]["no-chip"];
    },
    chipColor(type) {
      return this.colors[type]["chip"];
    },
    chipTextColor(type) {
      return this.colors[type]["chip-text"];
    },
    iconColor(type) {
      return this.colors[type]["icon"];
    },
    iconTextColor(type) {
      return this.colors[type]["icon-text"];
    },
    titleColor(type) {
      return this.colors[type]["title"];
    },
    subTitleColor(type) {
      return this.colors[type]["subtitle"];
    },
    refreshToken() {
      return axios
        .post('/refreshToken')
        .then( response => {
          if (response.data.token) {
            this.$store.commit('a/setToken',response.data.token);
          }
          return Promise.resolve();
        })
        .catch( error => {
          return Promise.reject(error);
        });
    }
  },
};
</script>

<style lang="scss" scoped>
.chip-overflow {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.small-flag {
  margin-left:-0.5em !important;
}
</style>
