<template>
  <div>
    <v-autocomplete
      v-model="selection"
      group="autocomplete"
      :label="label + (required ? ' *' : '')"
      :search-input.sync="search"
      :items="propositions"
      hide-no-data
      no-filter
      :required="required"
      :hint="hint"
      :rules="rules"
      :aria-label="ariaLabel"
      :aria-labelledby="ariaLabelledBy"
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
              v-if="data.item.value.countryCode && data.item.value.countryCode != country"
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
          v-if="data.item.value.countryCode && data.item.value.countryCode != country"
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
      default: false
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
      default: () => ({})
    },
    ariaLabel: {
      type: String,
      default: null
    },
    ariaLabelledBy: {
      type: String,
      default: null
    }
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
    defaultPalette() {
      if (Object.keys(this.palette).length > 0) return this.palette;
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
    colors() {
      return {
        locality: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.locality.main,
          "chip-text": this.defaultPalette.locality.text,
          icon: this.defaultPalette.locality.main+" accent-2",
          "icon-text": this.defaultPalette.locality.text+"--text",
          title: this.defaultPalette.locality.main+"--text text--darken-3",
          subtitle: this.defaultPalette.locality.main+"--text text--lighten-1",
        },
        street: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.street.main,
          "chip-text": this.defaultPalette.street.text,
          icon: this.defaultPalette.street.main+" accent-2",
          "icon-text": this.defaultPalette.street.text+"--text",
          title: this.defaultPalette.street.main+"--text text--darken-3",
          subtitle: this.defaultPalette.street.main+"--text text--lighten-1",
        },
        housenumber: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.housenumber.main,
          "chip-text": this.defaultPalette.housenumber.text,
          icon: this.defaultPalette.housenumber.main+" accent-2",
          "icon-text": this.defaultPalette.housenumber.text+"--text",
          title: this.defaultPalette.housenumber.main+"--text text--darken-3",
          subtitle: this.defaultPalette.housenumber.main+"--text text--lighten-1",
        },
        venue: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.venue.main,
          "chip-text": this.defaultPalette.venue.text,
          icon: this.defaultPalette.venue.main+" accent-2",
          "icon-text": this.defaultPalette.venue.text+"--text",
          title: this.defaultPalette.venue.main+"--text text--darken-3",
          subtitle: this.defaultPalette.venue.main+"--text text--lighten-1",
        },
        other: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.other.main,
          "chip-text": this.defaultPalette.other.text,
          icon: this.defaultPalette.other.main+" accent-2",
          "icon-text": this.defaultPalette.other.text+"--text",
          title: this.defaultPalette.other.main+"--text text--darken-3",
          subtitle: this.defaultPalette.other.main+"--text text--lighten-1",
        },
        relaypoint: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.relaypoint.main,
          "chip-text": this.defaultPalette.relaypoint.text,
          icon: this.defaultPalette.relaypoint.main+" accent-2",
          "icon-text": this.defaultPalette.relaypoint.text+"--text",
          title: this.defaultPalette.relaypoint.main+"--text text--darken-3",
          subtitle: this.defaultPalette.relaypoint.main+"--text text--lighten-1",
        },
        user: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.user.main,
          "chip-text": this.defaultPalette.user.text,
          icon: this.defaultPalette.user.main+" accent-2",
          "icon-text": this.defaultPalette.user.text+"--text",
          title: this.defaultPalette.user.main+"--text text--darken-3",
          subtitle: this.defaultPalette.user.main+"--text text--lighten-1",
        },
        event: {
          "no-chip": this.defaultPalette.nochip,
          chip: this.defaultPalette.event.main,
          "chip-text": this.defaultPalette.event.text,
          icon: this.defaultPalette.event.main+" accent-2",
          "icon-text": this.defaultPalette.event.text+"--text",
          title: this.defaultPalette.event.main+"--text text--darken-3",
          subtitle: this.defaultPalette.event.main+"--text text--lighten-1",
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
      let text = '';
      if (item.type == 'street') {
        text += item.streetName + ', ';
        if (item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'housenumber') {
        text += item.houseNumber + ', ' + item.streetName + ', ';
        if (item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'venue' || item.type == 'event') {
        text += item.name + ', ';
        if (item.houseNumber) text += item.houseNumber + ', ';
        if (item.streetName) text += item.streetName + ', ';
        if (item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'user') {
        if (this.showName) text += item.name + ', ';
        if (item.houseNumber) text += item.houseNumber + ', ';
        if (item.streetName) text += item.streetName + ', ';
        if (this.showName && item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'relaypoint') {
        if (item.name) text += item.name + ', ';
        if (item.houseNumber) text += item.houseNumber + ', ';
        if (item.streetName) text += item.streetName + ', ';
        if (item.postalCode && item.streetName) text += item.postalCode + ', ';
      }
      if (item.locality) text += item.locality;
      if (item.type == 'locality' && item.regionCode !== null)
        text += ', ' + item.regionCode;
      if (item.type == 'locality' && item.countryCode !== this.country)
        text += ', ' + item.country;
      if (item.type == 'relaypoint' && text == '') {
        text = this.$t('gps') + ' [' + item.lat + ', ' + item.lon + ']';
      }
      if (text.slice(text.length - 2) == ', ') {
        text = text.slice(0, -2);
      }
      return text;
    },
    propositionTitle(item) {
      let text = '';
      if (item.type == 'street') {
        text += item.streetName + ', ';
        if (item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'housenumber') {
        text += item.houseNumber + ', ' + item.streetName + ', ';
        if (item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'venue' || item.type == 'event') {
        text += item.name + ', ';
        if (item.houseNumber) text += item.houseNumber + ', ';
        if (item.streetName) text += item.streetName + ', ';
        if (item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'user') {
        if (this.showName) text += item.name + ', ';
        if (item.houseNumber) text += item.houseNumber + ', ';
        if (item.streetName) text += item.streetName + ', ';
        if (this.showName && item.postalCode) text += item.postalCode + ', ';
      }
      if (item.type == 'relaypoint') {
        if (item.name) text += item.name + ', ';
        if (item.houseNumber) text += item.houseNumber + ', ';
        if (item.streetName) text += item.streetName + ', ';
        if (item.postalCode && item.streetName) text += item.postalCode + ', ';
      }
      if (item.locality) text += item.locality;
      if (item.type == 'relaypoint' && text == '') {
        text = this.$t('gps') + ' [' + item.lat + ', ' + item.lon + ']';
      }
      if (text.slice(text.length - 2) == ', ') {
        text = text.slice(0, -2);
      }
      return text;
    },
    propositionText(item) {
      let text = '';
      if (item.regionCode) text += item.regionCode;
      if (item.region) text += (text != '' ? ', ' : '') + item.region;
      if (item.macroRegion)
        text += (text != '' ? ', ' : '') + item.macroRegion;
      if (item.country) text += (text != '' ? ', ' : '') + item.country;
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
