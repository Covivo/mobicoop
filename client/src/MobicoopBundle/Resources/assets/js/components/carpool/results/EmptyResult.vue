<template>
  <div>
    <v-row>
      <v-col
        align="left"
      >
        {{ $t('noResultForDate', {date: getSearchedDate}) }}
      </v-col>
    </v-row>
    <v-row>
      <v-col align="center">
        <v-btn
          class="mt-5"
          color="secondary"
          rounded
          @click="publish()"
        >
          {{ $t('publishAd') }}
        </v-btn>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/carpool/results/EmptyResult/";
import moment from "moment";

export default {
  i18n: {
    messages: {
      en: messages_en,
      nl: messages_nl,
      fr: messages_fr,
      eu: messages_eu
    }
  },
  props: {
    date: {
      type: String,
      default: null
    },
  },
  data: () => {
    return{}
  },
  computed: {
    getSearchedDate() {
      const dateFormat = "ddd Do MMMM YYYY";
      if (!this.date) {
        return new moment().format(dateFormat);
      };

      return new moment(this.date).format(dateFormat);
    }
  },
  methods: {
    publish() {
      this.$emit('publishAd');
    },
  }
}
</script>
