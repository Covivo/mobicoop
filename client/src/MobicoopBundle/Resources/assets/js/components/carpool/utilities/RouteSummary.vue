<template>
  <div>
    <v-row
      justify="center"
      :align="type==1 ? 'center' : 'start'"
      dense
    >
      <v-col
        v-if="time"
        col="2"
      >
        <span
          class="body-1 font-weight-bold"
          :class="textColorClass"
        >
          {{ formatTime(time) }}
        </span>
      </v-col>
      <!-- Origin -->
      <v-col
        :cols="time ? 4 : 5"
        align="left"
      >
        <v-list-item
          two-line
          :color="textColorClass"
        >
          <v-list-item-content>
            <v-list-item-title 
              :class="(regular && type==2) ? 'title' : 'title font-weight-bold'"
            >
              <span :class="textColorClass">{{ originFirstLine }}</span>
            </v-list-item-title>
            <v-list-item-title
              v-if="type==1 && regular"
              :class="'title font-weight-bold'"
            >
              <span :class="textColorClass">{{ originSecondLine }}</span>
            </v-list-item-title>
            <v-list-item-subtitle
              v-if="type==2"
              :class="(regular && type==2) ? 'subtitle-1 font-weight-bold' : ((regular) ? 'title font-weight-bold' : 'subtitle-1')"
            >
              <span :class="textColorClass">{{ originSecondLine }}</span>
            </v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-col>

      <!-- Icon -->
      <v-col
        cols="2"
      >
        <v-icon
          :color="iconColor"
          size="64"
        >
          mdi-ray-start-end
        </v-icon>
      </v-col>

      <!-- Destination -->
      <v-col
        :cols="time ? 4 : 5"
        class="title font-weight-bold mt-0"
        align="left"
      >
        <v-list-item
          two-line
          :color="textColorClass"
        >
          <v-list-item-content>
            <v-list-item-title 
              :class="(regular && type==2) ? 'title' : 'title font-weight-bold'"
            >
              <span :class="textColorClass">{{ destinationFirstLine }}</span>
            </v-list-item-title>
            <v-list-item-title
              v-if="type==1 && regular"
              :class="'title font-weight-bold'"
            >
              <span :class="textColorClass">{{ destinationSecondLine }}</span>
            </v-list-item-title>
            <v-list-item-subtitle
              v-if="type==2"
              :class="(regular && type==2) ? 'subtitle-1 font-weight-bold' : ((regular) ? 'title font-weight-bold' : 'subtitle-1')"
            >
              <span :class="textColorClass">{{ destinationSecondLine }}</span>
            </v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";

import Translations from "@translations/components/carpool/utilities/RouteSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/RouteSummary.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
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
    originFirst: {
      type: Boolean,
      default: false
    },
    destinationLast: {
      type: Boolean,
      default: false
    },
    type: {
      type: Number,
      default: 1
    },
    regular: {
      type: Boolean,
      default: false
    },
    textColorClass: {
      type: String,
      default: ""
    },
    iconColor: {
      type: String,
      default: "accent"
    },
    time: {
      type: String,
      default: null
    }
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
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    formatTime(time) {
      return moment.utc(time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
};
</script>