<template>
  <div>
    <v-row
      align="center"
      dense
    >
      <!-- Carpooler identity -->
      <v-col
        cols="4"
      >
        <carpooler-identity
          :carpooler="carpooler"
        />
      </v-col>

      <!-- Carpooler rate -->
      <v-col
        v-if="carpoolerRate"
        cols="1"
      >
        <v-tooltip
          bottom
          color="info"
        >
          <template v-slot:activator="{ on }">
            <v-container
              class="pt-0 pb-0 pl-0 pr-0"
            >
              <v-row
                align="center"
                dense
                v-on="on"
              >
                <span
                  class="yellow--text text--darken-2"
                >
                  4.7
                </span>

                <v-icon
                  :color="'yellow darken-2'"
                  class="ml-1"
                >
                  mdi-star
                </v-icon>
              </v-row>
            </v-container>
          </template>
          <span> {{ $t('inDev') }} </span>
        </v-tooltip>

        <!-- Community -->
        <v-tooltip
          color="info"
          right
        >
          <template v-slot:activator="{ on }">
            <v-row
              align="center"
              dense
              v-on="on"
            >
              <v-avatar
                v-for="community in communities"
                :key="community.id"
                cols="1"
                color="grey darken-3"
                size="26"
                class="ml-0 mr-1"
              >
                <!-- {{ community }} -->
                <v-img
                  src="https://cdn.vuetifyjs.com/images/john.jpg"
                  alt="avatar"
                />
              </v-avatar>
            </v-row>
          </template>
          <!-- mettre le nom de la communauté -->
          <span>
            bouh</span> 
        </v-tooltip>
      </v-col>

      <!-- Carpooler contact -->
      <v-col
        cols="4"
      >
        <carpooler-contact
          :carpooler="carpooler"
          :user="user"
        />
      </v-col>

      <!-- Carpool button -->
      <v-col
        v-if="!externalRdexJourneys"
        cols="3"
        class="text-right"
      >
        <v-btn
          rounded
          color="secondary"
          large
          @click="emitCarpoolEvent"
        >
          <span>
            {{ $t('carpool') }}
          </span>
        </v-btn>
      </v-col>
      <v-col
        v-else
        cols="3"
        class="text-right"
      >
        <v-btn
          rounded
          color="secondary"
          large
          type="button"
          :href="externalUrl"
          target="_blank"
          class="mt-1"
        >
          <span>
            {{ $t('externalUrl') }}
          </span>
        </v-btn>
        <br>
        <v-card-text class="py-0">
          <em>{{ externalOrigin }}</em>
        </v-card-text>
      </v-col>      
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/carpool/utilities/CarpoolerSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/CarpoolerSummary.json";

import CarpoolerIdentity from "./CarpoolerIdentity";
import CarpoolerContact from "./CarpoolerContact";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
  },
  components: {
    CarpoolerIdentity,
    CarpoolerContact
  },
  props: {
    proposal: {
      type: Object,
      default: null
    },
    carpooler: {
      type: Object,
      default: null
    },
    carpoolerRate: {
      type: Boolean,
      default: true
    },
    user: {
      type: Object,
      default: null
    },
    externalRdexJourneys: {
      type: Boolean,
      default: true
    },
    externalUrl: {
      type: String,
      default: null
    },    
    externalOrigin: {
      type: String,
      default: null
    },
    communities: {
      type: Array,
      default: null
    }
  },
  data() {
    return {
      connected: this.user !== null,
    };
  },
  methods: {
    buttonAlert(msg, e) {
      alert(msg);
    },
    emitCarpoolEvent: function() {
      if (this.connected) {
        this.$emit("carpool");
      } else {
        this.$emit("loginOrRegister");
      }
    }
  }
};
</script>