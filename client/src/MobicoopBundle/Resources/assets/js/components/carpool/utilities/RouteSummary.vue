<template>
  <div>
    <v-row
      justify="center"
      :align="type==1 ? 'center' : 'start'"
      dense
    >
      <!-- Origin -->
      <v-col
        cols="5"
        align="left"
      >
        <v-list-item two-line>
          <v-list-item-content>
            <v-list-item-title 
              :class="(regular && type==2) ? 'title' : 'title font-weight-bold'"
            >
              {{ originFirstLine }}
            </v-list-item-title>
            <v-list-item-title
              v-if="type==1 && regular"
              :class="'title font-weight-bold'"
            >
              {{ originSecondLine }}
            </v-list-item-title>
            <v-list-item-subtitle
              v-if="type==2"
              :class="(regular && type==2) ? 'subtitle-1 font-weight-bold' : ((regular) ? 'title font-weight-bold' : 'subtitle-1')"
            >
              {{ originSecondLine }}
            </v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-col>

      <!-- Icon -->
      <v-col
        cols="2"
      >
        <v-icon
          :color="'yellow darken-2'"
          size="64"
        >
          mdi-ray-start-end
        </v-icon>
      </v-col>

      <!-- Destination -->
      <v-col
        cols="5"
        class="title font-weight-bold mt-0"
        align="left"
      >
        <v-list-item two-line>
          <v-list-item-content>
            <v-list-item-title 
              :class="(regular && type==2) ? 'title' : 'title font-weight-bold'"
            >
              {{ destinationFirstLine }}
            </v-list-item-title>
            <v-list-item-title
              v-if="type==1 && regular"
              :class="'title font-weight-bold'"
            >
              {{ destinationSecondLine }}
            </v-list-item-title>
            <v-list-item-subtitle
              v-if="type==2"
              :class="(regular && type==2) ? 'subtitle-1 font-weight-bold' : ((regular) ? 'title font-weight-bold' : 'subtitle-1')"
            >
              {{ destinationSecondLine }}
            </v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/utilities/RouteSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/RouteSummary.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
    origin: {
      type: Object,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    type: {
      type: Number,
      default: 1
    },
    regular: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
    };
  },
  computed: {
    originFirstLine() {
      if (this.type == 1 && !this.regular) {
        return (this.origin.streetAddress ? this.origin.streetAddress+', ' : '')+this.origin.addressLocality
      } else if (this.type == 1 && this.regular) {
        return this.origin.streetAddress
      } else if (this.type == 2 && !this.regular) {
        return this.origin.addressLocality
      } else {
        return this.origin.streetAddress
      }
    },
    destinationFirstLine() {
      if (this.type == 1 && !this.regular) {
        return (this.destination.streetAddress ? this.destination.streetAddress+', ' : '')+this.destination.addressLocality
      } else if (this.type == 1 && this.regular) {
        return this.destination.streetAddress
      } else if (this.type == 2 && !this.regular) {
        return this.destination.addressLocality
      } else {
        return this.destination.streetAddress
      }
    },
    originSecondLine() {
      if (this.type == 1 && this.regular) {
        return this.origin.addressLocality
      } else if (this.type == 2 && !this.regular) {
        return this.origin.streetAddress
      } else if (this.type == 2 && this.regular) {
        return this.origin.addressLocality
      }
      return null;
    },
    destinationSecondLine() {
      if (this.type == 1 && this.regular) {
        return this.destination.addressLocality
      } else if (this.type == 2 && !this.regular) {
        return this.destination.streetAddress
      } else if (this.type == 2 && this.regular) {
        return this.destination.addressLocality
      }
      return null;
    },
  },
  methods: {
  }
};
</script>