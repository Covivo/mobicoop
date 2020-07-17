<template>
  <v-row align="center">
    <v-col
      cols="4"
      md="4"
      justify="center"
      align="center"
    >
      <v-img
        v-if="event.images[0]"
        :src="event['images'][0]['versions']['square_250']"
        alt="avatar"
        aspect-ratio="1"
        max-width="225"
        max-height="225"
      />
      <v-img
        v-else
        :src="urlAltAvatar"
        alt="avatar"
      />
    </v-col>
      
    <v-col
      cols="8"
      md="8"
    >
      <v-card
        flat
        justify="center"
      >
        <v-card-text>
          <h3 class="text-h5  text-left font-weight-bold">
            {{ event.name }}
          </h3>
          <p class="text-h5 text-left text-subtitle-1">
            {{ event.address.addressLocality }}
          </p>
          <p
            v-if="displayDescription && formatedDescription!==''"
            class="text-body-1"
            md="6"
            v-html="formatedDescription"
          />
          <p
            v-if="displayDescription && formatedFullDescription!==''"
            class="text-body-2"
            md="6"
            v-html="formatedFullDescription"
          />
          <v-row>
            <p class="text-body-2 pa-3">
              <span class="font-weight-black"> {{ $t('startEvent.label') }} :</span> {{ computedDateFormat(event.fromDate.date) }}
            </p>
            <v-spacer />
            <p class="text-body-2 pa-3">
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
  computed:{
    formatedDescription(){
      if(this.event.description){
        return this.event.description.replace('\n','<br />');
      }
      return '';
    },
    formatedFullDescription(){
      if(this.event.fullDescription){
        return this.event.fullDescription.replace('\n','<br />');
      }
      return '';
    }
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
