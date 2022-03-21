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
      :prepend-inner-icon="prependIcon"
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
    prependIcon:{
      type: String,
      default: null
    },
    country: {
      type: String,
      default: 'FR'
    },
    showName: {
      type: Boolean,
      default: true
    },
    resultsOrder: {
      type: Array,
      default: () => []
    },
    restrict: {
      type: Array,
      default: () => []
    },
    palette: {
      type: Object,
      default() {
        return {
          nochip: "black",
          locality: {
            main: "indigo",
            text: "white",
          },
          street: {
            main: "deep-purple",
            text: "white",
          },
          housenumber: {
            main: "purple",
            text: "white",
          },
          venue: {
            main: "pink",
            text: "white",
          },
          other: {
            main: "teal",
            text: "white",
          },
          relaypoint: {
            main: "teal",
            text: "white",
          },
          user: {
            main: "teal",
            text: "white",
          },
          event: {
            main: "teal",
            text: "white",
          },
        };
      },
    },
  },

  data: () => ({
    search: null,
    items: [],
    selection: null,
    loading: null
  }),

  computed: {
    sort() {
      if (this.resultsOrder.length > 0) return this.resultsOrder;
      return [
        "user",
        "relaypoint",
        "locality",
        "housenumber",
        "street",
        "venue",
        "event"
      ];
    },
    colors() {
      return {
        locality: {
          "no-chip": this.palette.nochip,
          chip: this.palette.locality.main,
          "chip-text": this.palette.locality.main+" lighten-5",
          icon: this.palette.locality.main+" accent-2",
          "icon-text": this.palette.locality.text+"--text",
          title: this.palette.locality.main+"--text text--darken-3",
          subtitle: this.palette.locality.main+"--text text--lighten-1",
        },
        street: {
          "no-chip": this.palette.nochip,
          chip: this.palette.street.main,
          "chip-text": this.palette.street.main+" lighten-5",
          icon: this.palette.street.main+" accent-2",
          "icon-text": this.palette.street.text+"--text",
          title: this.palette.street.main+"--text text--darken-3",
          subtitle: this.palette.street.main+"--text text--lighten-1",
        },
        housenumber: {
          "no-chip": this.palette.nochip,
          chip: this.palette.housenumber.main,
          "chip-text": this.palette.housenumber.main+" lighten-5",
          icon: this.palette.housenumber.main+" accent-2",
          "icon-text": this.palette.housenumber.text+"--text",
          title: this.palette.housenumber.main+"--text text--darken-3",
          subtitle: this.palette.housenumber.main+"--text text--lighten-1",
        },
        venue: {
          "no-chip": this.palette.nochip,
          chip: this.palette.venue.main,
          "chip-text": this.palette.venue.main+" lighten-5",
          icon: this.palette.venue.main+" accent-2",
          "icon-text": this.palette.venue.text+"--text",
          title: this.palette.venue.main+"--text text--darken-3",
          subtitle: this.palette.venue.main+"--text text--lighten-1",
        },
        other: {
          "no-chip": this.palette.nochip,
          chip: this.palette.other.main,
          "chip-text": this.palette.other.main+" lighten-5",
          icon: this.palette.other.main+" accent-2",
          "icon-text": this.palette.other.text+"--text",
          title: this.palette.other.main+"--text text--darken-3",
          subtitle: this.palette.other.main+"--text text--lighten-1",
        },
        relaypoint: {
          "no-chip": this.palette.nochip,
          chip: this.palette.relaypoint.main,
          "chip-text": this.palette.relaypoint.main+" lighten-5",
          icon: this.palette.relaypoint.main+" accent-2",
          "icon-text": this.palette.relaypoint.text+"--text",
          title: this.palette.relaypoint.main+"--text text--darken-3",
          subtitle: this.palette.relaypoint.main+"--text text--lighten-1",
        },
        user: {
          "no-chip": this.palette.nochip,
          chip: this.palette.user.main,
          "chip-text": this.palette.user.main+" lighten-5",
          icon: this.palette.user.main+" accent-2",
          "icon-text": this.palette.user.text+"--text",
          title: this.palette.user.main+"--text text--darken-3",
          subtitle: this.palette.user.main+"--text text--lighten-1",
        },
        event: {
          "no-chip": this.palette.nochip,
          chip: this.palette.event.main,
          "chip-text": this.palette.event.main+" lighten-5",
          icon: this.palette.event.main+" accent-2",
          "icon-text": this.palette.event.text+"--text",
          title: this.palette.event.main+"--text text--darken-3",
          subtitle: this.palette.event.main+"--text text--lighten-1",
        },
      };
    },
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
      if (this.selection) {
        const address = {
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
        };
        if (this.selection.type == "event") {
          // so nice...
          address.event = {"id": this.selection.value.id, "name": this.selection.value.name}
        }
        return address;
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
          "name":this.address.event ? this.address.event.name : this.address.name,
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
      if (item.type == "venue" || item.type == "relaypoint" || item.type == "event") {
        text += item.name + ", ";
        if (item.houseNumber) text += item.houseNumber + ", ";
        if (item.streetName) text += item.streetName + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      if (item.type == "user") {
        if (this.showName) text += item.name + ", ";
        if (item.houseNumber) text += item.houseNumber + ", ";
        if (item.streetName) text += item.streetName + ", ";
        if (this.showName && item.postalCode) text += item.postalCode + ", ";
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
      if (item.type == "venue" || item.type == "relaypoint" || item.type == "event") {
        text += item.name + ", ";
        if (item.houseNumber) text += item.houseNumber + ", ";
        if (item.streetName) text += item.streetName + ", ";
        if (item.postalCode) text += item.postalCode + ", ";
      }
      if (item.type == "user") {
        if (this.showName) text += item.name + ", ";
        if (item.houseNumber) text += item.houseNumber + ", ";
        if (item.streetName) text += item.streetName + ", ";
        if (this.showName && item.postalCode) text += item.postalCode + ", ";
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
      case "event" : return "mdi-stadium-variant";
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
      this.change();
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
