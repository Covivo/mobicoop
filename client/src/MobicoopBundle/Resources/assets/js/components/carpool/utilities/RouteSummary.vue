<template>
  <v-row
    justify="center"
    :align="type==1 ? 'center' : 'start'"
    dense
  >
    <v-col
      v-if="time"
      cols="2"
    >
      <span
        class="text-body-1 font-weight-bold"
        :class="textColorClass"
      >
        {{ formatTime(time) }}
      </span>
    </v-col>
    <!-- Origin -->
    <v-col
      v-if="!compact"
      cols="5"
      :class="type==1 ? 'text-left ml-6' : regular ? 'text-left' : 'text-left  mr-n4'"
    >
      <v-list-item
        two-line
        :color="textColorClass"
      >
        <v-list-item-content>
          <v-list-item-title
            :class="(regular && type==2 || !regular && type==1) ? 'text-subtitle-1' : 'text-subtitle-2'"
          >
            <span :class="textColorClass">{{ originFirstLine }}</span>
          </v-list-item-title>
          <v-list-item-title
            v-if="type==1 && regular"
            :class="'text-subtitle-2'"
          >
            <span :class="textColorClass">{{ originSecondLine }}</span>
          </v-list-item-title>
          <v-list-item-subtitle
            v-if="type==2"
            :class="(regular && type==2) ? 'text-subtitle-2' : ((regular) ? 'text-subtitle-2' : 'text-subtitle-2')"
          >
            <span :class="textColorClass">{{ originSecondLine }}</span>
          </v-list-item-subtitle>
          <v-list-item-subtitle
            v-if="!regular && type==1"
            class="text-subtitle-2"
          >
            <span :class="textColorClass">{{ originSecondLine }}</span>
          </v-list-item-subtitle>
        </v-list-item-content>
      </v-list-item>
    </v-col>
    <div
      v-else
      class="d-inline-flex text-right align-self-center"
    >
      <v-list-item
        two-line
        :color="textColorClass"
      >
        <v-list-item-content>
          <v-list-item-title
            :class="(regular && type==2 || !regular && type==1) ? 'text-subtitle-1' : 'text-subtitle-2'"
          >
            <span :class="textColorClass">{{ originFirstLine }}</span>
          </v-list-item-title>
          <v-list-item-title
            v-if="type==1 && regular"
            :class="'text-subtitle-2'"
          >
            <span :class="textColorClass">{{ originSecondLine }}</span>
          </v-list-item-title>
          <v-list-item-subtitle
            v-if="type==2"
            :class="(regular && type==2) ? 'text-subtitle-2' : ((regular) ? 'text-subtitle-2' : 'text-subtitle-2')"
          >
            <span :class="textColorClass">{{ originSecondLine }}</span>
          </v-list-item-subtitle>
          <v-list-item-subtitle
            v-if="!regular && type==1"
            class="text-subtitle-2"
          >
            <span :class="textColorClass">{{ originSecondLine }}</span>
          </v-list-item-subtitle>
        </v-list-item-content>
      </v-list-item>
    </div>

    <!-- Icon -->
    <v-col
      cols="1"
    >
      <v-icon
        size="60"
        :color="iconColor"
      >
        mdi-ray-start-end
      </v-icon>
    </v-col>

    <!-- Destination -->
    <v-col
      :cols="compact ? null : '5'"
      :class="type==1 ? 'text-left ml-3' : regular ? 'text-left ml-4' : 'text-right  ml-6'"
    >
      <v-list-item
        two-line
        :color="textColorClass"
      >
        <v-list-item-content>
          <v-list-item-title 
            :class="(regular && type==2 || !regular && type==1) ? 'text-subtitle-1' : 'text-subtitle-2'"
          >
            <span :class="textColorClass">{{ destinationFirstLine }}</span>
          </v-list-item-title>
          <v-list-item-title
            v-if="type==1 && regular"
            :class="'text-subtitle-2'"
          >
            <span :class="textColorClass">{{ destinationSecondLine }}</span>
          </v-list-item-title>
          <v-list-item-subtitle
            v-if="type==2"
            :class="(regular && type==2) ? 'text-subtitle-2' : ((regular) ? 'text-subtitle-2' : 'text-subtitle-2')"
          >
            <span :class="textColorClass">{{ destinationSecondLine }}</span>
          </v-list-item-subtitle>
          <v-list-item-subtitle
            v-if="!regular && type==1"
            class="text-subtitle-2"
          >
            <span :class="textColorClass">{{ destinationSecondLine }}</span>
          </v-list-item-subtitle>
        </v-list-item-content>
      </v-list-item>
    </v-col>
  </v-row>
</template>

<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu} from "@translations/components/carpool/utilities/RouteSummary/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
    },
    // if we want start-end ray closer from time
    compact: {
      type: Boolean,
      default: false
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
        // return (this.origin.streetAddress ? this.origin.streetAddress+', ' : '')+this.origin.addressLocality
        return (this.origin.streetAddress) ? this.origin.streetAddress : this.origin.addressLocality

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
        //        return (this.destination.streetAddress ? this.destination.streetAddress+', ' : '')+this.destination.addressLocality
        return (this.destination.streetAddress) ? this.destination.streetAddress : this.destination.addressLocality
      } else if (this.type == 1 && this.regular) {
        return (this.destination.streetAddress) ? this.destination.streetAddress : this.destination.addressLocality
      } else if (this.type == 2 && !this.regular) {
        return this.destination.addressLocality
      } else {
        return this.destination.streetAddress
      }
    },
    originSecondLine() {
      if (this.type == 1 && this.regular) {
        return this.origin.addressLocality
      } else if(this.type == 1 && !this.regular){
        return (this.origin.streetAddress) ? this.origin.addressLocality : ''
      } else if (this.type == 2 && !this.regular) {
        return this.origin.streetAddress
      } else if (this.type == 2 && this.regular) {
        return this.origin.addressLocality
      }
      return null;
    },
    destinationSecondLine() {
      let secondline = '';
      if (this.type == 1 && this.regular) {
        secondline = this.destination.addressLocality
      } else if(this.type == 1 && !this.regular){
        secondline = (this.destination.streetAddress) ? this.destination.addressLocality : ''
      } else if (this.type == 2 && !this.regular) {
        secondline = this.destination.streetAddress
      } else if (this.type == 2 && this.regular) {
        secondline = this.destination.addressLocality
      }
      return secondline === this.destinationFirstLine ? null : secondline;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    formatTime(time) {
      return moment(time).isValid() ? moment.utc(time).format(this.$t("hourMinute")) : time;
    }
  }
};
</script>