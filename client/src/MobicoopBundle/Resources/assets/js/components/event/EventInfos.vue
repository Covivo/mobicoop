<template>
  <v-row
    align="center"
    justify="center"
  >
    <v-col cols="4">
      <v-avatar
        color="grey lighten-3"
        size="225"
      >
        <img
          v-if="event.images[0]"
          :src="event['images'][0]['versions'][avatarVersion]"
          alt="avatar"
        >
        <img
          v-else
          :src="urlAltAvatar"
          alt="avatar"
        >
      </v-avatar>
    </v-col>
      
    <v-col
      cols="8"
    >
      <v-card
        flat
        height="25vh"
      >
        <v-card-text>
          <h3 class="headline">
            {{ event.name }}
          </h3>
          <p class="body-1">
            {{ event.description }}
          </p>
          <p class="body-2">
            {{ event.fullDescription }}
          </p>
          <v-row>
            <p class="body-2">
              <span class="font-weight-black"> {{ $t('startEvent.label') }} :</span> {{ computedDateFormat(event.fromDate.date) }}
            </p>
            <v-spacer />
            <p class="body-2">
              <span class="font-weight-black"> {{ $t('endEvent.label') }} :  </span>{{ computedDateFormat(event.toDate.date) }}
            </p>
          </v-row>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>
<script>
import moment from "moment";
import { merge } from "lodash";
import Translations from "@translations/components/event/EventInfos.json";
import TranslationsClient from "@clientTranslations/components/event/EventInfos.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  props:{
    event: {
      type: Object,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    avatarVersion: {
      type: String,
      default: null
    }
  },
  i18n: {
    messages: TranslationsMerged,
  },
  data() {
    return {
      locale: this.$i18n.locale,
      origin: this.initOrigin,
    };
  },
  methods: {
    computedDateFormat(date) {
      // moment.locale(this.locale);
      // return this.date
      //   ? moment(this.date).format(this.$t("ui.i18n.date.format.fullDate"))
      //   : null;
      return moment(date).format(this.$t("DD/MM/YYYY HH:mm"));
    }
  },
}
</script>