<template>
  <v-main>
    <v-container>
      <v-row justify="center">
        <v-col
          cols="12"
          align="center"
        >
          <!-- Event : avatar, title and description -->
          <event-infos
            :event="event"
            :url-alt-avatar="urlAltAvatar"
            :display-description="false"
            :is-widget="true"
            :justify-title="justifyTitle"
            :justify-address-locality="justifyAddressLocality"
          />
        </v-col>
      </v-row>
      <!-- search journey -->
      <p
        class="font-weight-bold"
        align="center"
      >
        {{ $t("title.searchCarpool") }}
      </p>
      <!-- event buttons and map -->
      <v-row
        align="center"
        justify="center"
      >
        <v-col col="12">
          <search
            :geo-search-url="geodata.geocompleteuri"
            :geo-complete-results-order="geoCompleteResultsOrder"
            :geo-complete-palette="geoCompletePalette"
            :geo-complete-chip="geoCompleteChip"
            :user="user"
            :params="params"
            :punctual-date-optional="punctualDateOptional"
            :regular="regular"
            :default-destination="defaultDestination"
            :hide-publish="true"
            :disable-search="disableSearch"
            :show-destination="false"
            :is-widget="true"
            :date-time-picker="dateTimePicker"
            :default-outward-date="defaultOutwardDate"
            :default-outward-time="defaultOutwardTime"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
<script>
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/event/Event/";
import EventInfos from "@components/event/EventInfos";
import Search from "@components/carpool/search/Search";
import moment from "moment";

export default {
  components: {
    EventInfos,
    Search
  },
  i18n: {
    messages: {
      en: messages_en,
      nl: messages_nl,
      fr: messages_fr,
      eu: messages_eu
    }
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    geodata: {
      type: Object,
      default: null
    },
    event: {
      type: Object,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    token: {
      type: String,
      default: ""
    },
    justifyTitle: {
      type: String,
      default: "text-h5 text-center font-weight-bold"
    },
    justifyAddressLocality: {
      type: String,
      default: "text-h5 text-center text-subtitle-1"
    },
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
    dateTimePicker: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      locale: "fr",
      params: { eventId: this.event.id },
      defaultDestination: this.event.address
    };
  },
  computed: {
    disableSearch() {
      let now = moment();
      if (now > moment(this.event.toDate.date)) return true;
      else return false;
    },
    defaultOutwardDate() {
      const now = moment();

      return now > moment(this.event.fromDate.date)
        ? now.format("YYYY-MM-DD HH:mm")
        : this.event.fromDate.date;
    },
    defaultOutwardTime() {
      if (!this.dateTimePicker) return null;

      const now = moment();

      return now > moment(this.event.fromDate.date)
        ? now.format("HH:mm")
        : this.event.fromDate.date;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    this.$store.commit("a/setToken", this.token);
  }
};
</script>

<style>
div {
  padding: 0px 3px !important;
}
div.row {
  display: block !important;
}
div.row p.text-body-2 {
  font-size: 0.75rem !important;
  line-height: 1rem !important;
  padding: 0px 3px !important;
}
</style>
