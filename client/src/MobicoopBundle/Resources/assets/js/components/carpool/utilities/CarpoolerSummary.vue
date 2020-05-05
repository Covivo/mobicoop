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

      <!-- Button -->
      <v-col
        v-if="!externalRdexJourneys"
        cols="3"
        class="text-right"
      >
        <v-tooltip
          :disabled="!disabled"
          bottom
          color="info"
        >
          <template v-slot:activator="{ on }">
            <div v-on="on">
              <v-btn
                rounded
                color="secondary"
                large
                :disabled="disabled"
                @click="emitEvent"
              >
                <span>
                  {{ $t('carpool') }}
                </span>
              </v-btn>
            </div>
          </template>
          <span> {{ $t('needTobeConnected') }} </span>
        </v-tooltip>
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
  },
  data() {
    return {
      disabled: !this.user,
    };
  },
  methods: {
    buttonAlert(msg, e) {
      alert(msg);
    },
    emitEvent: function() {
      this.$emit("carpool");
    }
  }
};
</script>