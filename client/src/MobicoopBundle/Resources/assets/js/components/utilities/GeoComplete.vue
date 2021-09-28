<template>
  <v-form :ref="name">
    <v-autocomplete
      id="address"
      v-model="address"
      :loading="isLoading"
      :items="items"
      :label="alternativeLabel ? $t(alternativeLabel) : label + (required ? ' *' : '')"
      :hint="hint"
      :search-input.sync="search"
      hide-no-data
      clearable
      item-text="selectedDisplayedLabel"
      item-value="key"
      color="primary"
      return-object
      no-filter
      persistent-hint
      :required="required"
      :rules="geoRules"
      :disabled="disabled"
      :prepend-inner-icon="prependIcon"
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
        <template v-if="typeof data.item !== 'object'">
          <v-list-item-content v-text="data.item" />
        </template>
        <template v-else>
          <v-list>
            <v-list-item id="content">
              <v-list-item-avatar v-if="displayIcon">
                <v-avatar size="36">
                  <v-img
                    :src="data.item.icon"
                    contain
                  />
                </v-avatar>
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
import moment from "moment";  
import axios from "axios";
import debounce from "lodash/debounce";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/GeoComplete/";

const defaultString = {
  type: String,
  default: null
};
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    url: defaultString,
    label: defaultString,
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
    required:  {
      type: Boolean,
      default: false
    },
    name: defaultString,
    initAddress: {
      type: Object,
      default: null
    },
    prependIcon:{
      type: String,
      default: ""
    },
    alternativeLabel:{
      type:String,
      default: null
    },
  },
  data() {
    return {
      entries: [],
      isLoading: false,
      search: null,
      address: null,
      filter: null,
      cancelSource: null
    };
  },
  computed: {
    items() {
      return this.entries;
    },
    geoRules() {
      if (this.required) {
        return [
          v => !!v || this.$t('required')
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
          this.address.key = '0';
          this.address.displayedLabel = `${this.address.displayLabel[0]}`;
          this.address.displayedSecondLabel = `${this.address.displayLabel[1]}`;
          if (this.address.home) {
            this.address.selectedDisplayedLabel = `${this.address.displayLabel[0]}`;
          } else if (this.address.name) {
            this.address.selectedDisplayedLabel = `${this.address.displayLabel[0]}`;
          } else if (this.address.relayPoint) {
            this.address.selectedDisplayedLabel = `${this.address.relayPoint.name}`;
          } else if (this.address.event) {
            this.address.selectedDisplayedLabel = `${this.address.displayLabel[0]}`;
          } else {
            this.address.selectedDisplayedLabel = `${this.address.displayLabel[0]}`;
          }
          this.entries.push(this.address);
        } 
      }
    }
  },
  mounted() {
    this.locale = localStorage.getItem("X-LOCALE");
    moment.locale(this.locale);
  },
  methods: {
    changedAddress() {
      this.$emit("address-selected", this.address);
    },
    getAsyncData: debounce(function(val) {
      this.isLoading = true;

      this.cancelRequest(); // CANCEL PREVIOUS REQUEST
      this.cancelSource = axios.CancelToken.source();

      this.getData(val);
    }, 1000),

    getData(val) {
      let self = this;
      axios
        .get(`${this.url}${val}`, {
          headers: { Authorization: 'Bearer ' + this.$store.getters['a/token'] },
          cancelToken: this.cancelSource.token
        })
        .then(res => {
          this.cancelSource = null;
          this.isLoading = false;

          let results = [];
          let resultsNamed = [];
          let resultsSig = [];
          let resultsRelayPoint = [];
          let resultsEvent = [];

          // Modify property displayLabel to be shown into the autocomplete field after selection
          let addresses = res.data["hydra:member"];
          // No Adresses return, we stop here
          if (!addresses.length) {
            return;
          }
          addresses.forEach((address, addressKey) => {
            addresses[addressKey].key = addressKey;
            addresses[addressKey].displayedLabel = `${address.displayLabel[0]}`;
            addresses[addressKey].displayedSecondLabel = `${address.displayLabel[1]}`;
            if (address.name) {
              addresses[addressKey].selectedDisplayedLabel = `${address.displayLabel[0]}`;
            } else if (address.relayPoint) {
              addresses[addressKey].selectedDisplayedLabel = `${address.relayPoint.name}`;
            } else if (address.event) {
              addresses[addressKey].selectedDisplayedLabel = `${address.displayedLabel}`;
            } else {
              addresses[addressKey].selectedDisplayedLabel = `${address.displayLabel[0]}`;
            }
          });

          addresses.forEach((address, addressKey) => {
            let addressLocality = address.addressLocality
              ? address.addressLocality
              : "";
            let addressStreet = address.streetAddress
              ? address.streetAddress
              : (address.street ? address.street : "");
            if (addressLocality || addressStreet) {
              // If there is no locality or street returned, do not show them (region, department ..)
              if (address.name) {
                resultsNamed.push(address);
              } else if (address.relayPoint) {
                resultsRelayPoint.push(address);
              } else if (address.event) {
                resultsEvent.push(address);
              } else {
                resultsSig.push(address);
              }
            } 
          });

          if (resultsNamed.length>0) {
            resultsNamed.forEach((address) => {
              results.push(address);
            });
          }

          if (resultsSig.length>0) {
            resultsSig.forEach((address) => {
              results.push(address);
            });
          }

          if (resultsRelayPoint.length>0) {
            if (results.length>0) {
              results.push({'divider':'true'});
              results.push({'header':this.$t('relayPoints')});
            }
            resultsRelayPoint.forEach((address) => {
              results.push(address);
            });
          }

          if (resultsEvent.length>0) {
            if (results.length>0) {
              results.push({'divider':'true'});
              results.push({'header':this.$t('events')});
            }
            resultsEvent.forEach((address) => {
              results.push(address);
            });
          }

          // Set Data & show them
          if (this.isLoading) return; // Another request is fetching, we do not show the previous one
          this.entries = [...results];
        })
        .catch(error => {
          if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx 
            switch (error.response.status) {
            case 401: 
              //  unauthorized
              if (error.response.data.message == 'Expired JWT Token') {
                // check refreshToken
                return self.refreshToken()
                  .then( () => {
                    // try again !
                    this.getData(val);
                  });
              }
            }
          }
          this.entries = [];
        })
        .finally(() => (this.isLoading = false));
    },
    cancelRequest() {
      if(this.cancelSource) {
        this.cancelSource.cancel('Start new search, stop active search');
      }
    },
    refreshToken() {
      return axios
        .post(this.$t('refreshRoute'))
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
  }
};
</script>