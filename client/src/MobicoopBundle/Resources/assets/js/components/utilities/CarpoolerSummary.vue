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
              src="https://avataaars.io/?avatarStyle=Transparent&topType=ShortHairShortRound&accessoriesType=Blank&hairColor=BrownDark&facialHairType=Blank&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Default&skinColor=Light"
            />
          </v-list-item-avatar>
          <!--Carpooler data-->
          <v-list-item-content>
            <v-list-item-title class="font-weight-bold">
              {{ carpooler.givenName }} {{ carpooler.familyName.substr(0,1).toUpperCase()+"." }}
            </v-list-item-title>
            <v-list-item-title>{{ age }} </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-col>

      <!-- Carpooler rate -->
      <v-col
        cols="1"
      >
        <v-tooltip bottom>
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
          <span> {{ inDev }} </span>
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
          <v-btn
            color="success"
            small
            dark
            depressed
            rounded
            :hidden="!phoneButtonToggled"
            height="40px"
            @click="toggleButton"
          >
            <v-icon>mdi-phone</v-icon>
            {{ carpooler.telephone }}
          </v-btn>
          <v-btn
            color="success"
            small
            depressed
            fab
            :hidden="phoneButtonToggled"
            @click="toggleButton"
          >
            <v-icon>
              mdi-phone
            </v-icon>
          </v-btn>

          <v-btn
            :disabled="!contactAvailable"
            color="success"
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
        </v-row>
      </v-col>

      <!-- Button -->
      <v-col
        cols="3"
        class="text-right"
      >
        <v-btn
          :disabled="!contactAvailable"
          rounded
          color="success"
          large
          @click="emitEvent"
        >
          <span>
            {{ $t('carpool') }}
          </span>
        </v-btn>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/utilities/CarpoolerSummary.json";
import TranslationsClient from "@clientTranslations/components/utilities/CarpoolerSummary.json";

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
    user: {
      type: Object,
      default: null
    },
    carpooler: {
      type: Object,
      default: null
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
      phoneButtonToggled: false,
      inDev: "En cours de développement"
    };
  },
  computed: {
    age (){
      return moment().diff(moment([this.carpooler.birthYear]),'years')+' '+this.$t("birthYears")
    },
    contactAvailable () {
      return this.user ? this.user.id != this.carpooler.id : false;
    }
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