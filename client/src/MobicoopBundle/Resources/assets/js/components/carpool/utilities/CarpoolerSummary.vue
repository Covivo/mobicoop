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
        <v-list-item>
          <!--Carpooler avatar-->
          <v-list-item-avatar
            color="grey darken-3"
            size="50"
          >
            <v-img
              aspect-ratio="2"
              :src="carpooler.avatars[0]"
            />
          </v-list-item-avatar>
          <!--Carpooler data-->
          <v-list-item-content>
            <v-list-item-title class="font-weight-bold">
              {{ carpooler.givenName }} {{ carpooler.shortFamilyName }}
            </v-list-item-title>
            <v-list-item-title>{{ age }} </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
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
        <v-row
          align="center"
          justify="end"
          class="min-width-no-flex"
        >
          <div v-if="user && carpooler.phoneDisplay == 2">
            <v-btn
              v-show="!phoneButtonToggled"
              color="secondary"
              small
              depressed
              fab
              @click="toggleButton"
            >
              <v-icon>
                mdi-phone
              </v-icon>
            </v-btn>
            <v-btn
              v-show="phoneButtonToggled"
              color="secondary"
              small
              dark
              depressed
              rounded
              height="40px"
              @click="toggleButton"
            >
              <v-icon>mdi-phone</v-icon>
              {{ carpooler.phone }}
            </v-btn>
          </div>
          <!-- <div>
            <v-btn
              color="secondary"
              small
              depressed
              fab
              class="ml-2"
            >
              <v-icon
                @click="buttonAlert(inDev,$event);"
              >
                mdi-email
              </v-icon>
            </v-btn>
          </div> -->
        </v-row>
      </v-col>

      <!-- Button -->
      <v-col
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
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/carpool/utilities/CarpoolerSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/CarpoolerSummary.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
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
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
      phoneButtonToggled: false,
      disabled: this.user ? false : true
    };
  },
  computed: {
    age (){
      if (this.carpooler.birthYear) {
        return moment().diff(moment([this.carpooler.birthYear]),'years')+' '+this.$t("birthYears");
      } else {
        return null;
      }
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    toggleButton: function(){
      this.phoneButtonToggled = !this.phoneButtonToggled;
    },
    buttonAlert(msg, e) {
      alert(msg);
    },
    emitEvent: function() {
      this.$emit("carpool");
    }
  }
};
</script>