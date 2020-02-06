<template>
  <v-row
    align="center"
    justify="center"
  >
    <v-col
      cols="4"
      md="4"
    >
      <v-avatar
        color="grey lighten-3"
        :size="isWidget?150:225"
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
      md="8"
    >
      <v-card
        flat
        max-height="25vh"
        justify="center"
      >
        <v-card-text>
          <h3 class="headline text-justify font-weight-bold">
            {{ event.name }}
          </h3>
          <p class="headline text-justify subtitle-1">
            {{ event.address.addressLocality }}
          </p><p
            v-if="displayDescription"
            class="body-1 pa-3"
            md="6"
          >
            {{ event.description }}
          </p>
          <p
            v-if="displayDescription"
            class="body-2 pa-3"
            md="6"
          >
            {{ event.fullDescription }}
          </p>
          <v-row>
            <p class="body-2 pa-3">
              <span class="font-weight-black"> {{ $t('startEvent.label') }} :</span> {{ computedDateFormat(event.fromDate.date) }}
            </p>
            <v-spacer />
            <p class="body-2 pa-3">
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
    },
    displayDescription: {
      type: Boolean,
      default: true
    },
    isWidget: {
      type: Boolean,
      default: false
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
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    computedDateFormat(date) {
      return moment(date).format(this.$t("ui.i18n.date.format.shortCompleteDate") + (this.event.useTime ? (" " + this.$t("ui.i18n.time.format.hourMinute")) : ""));
    }
  },
}
</script>
