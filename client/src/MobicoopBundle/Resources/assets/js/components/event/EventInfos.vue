<template>
  <v-row align="center">
    <v-col cols="12">
      <v-row>
        <v-col
          cols="8"
          md="8"
          justify="center"
          align="center"
        >
          <v-img
            v-if="event.images[0]"
            :src="event['images'][0]['versions']['square_250']"
            width="225"
            height="200"
            :aspect-ratio="1"
            :alt="event.name"
            contain
          />
          <v-img
            v-else
            :src="urlAltAvatar"
            width="225"
            height="200"
            :aspect-ratio="1"
            :alt="event.name"
            contain
          />
        </v-col>
      </v-row>
      <v-row>
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
                <v-chip 
                  v-if="event.private" 
                  small
                  color="warning"
                >
                  {{ $t('private') }}
                </v-chip>
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
    </v-col>
  </v-row>
</template>
<script>
import moment from "moment";
import {messages_en, messages_fr} from "@translations/components/event/EventInfos/";

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
    messages: {
      'en': messages_en,
      'fr': messages_fr
    },
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
      return moment(date).format(this.$t("shortCompleteDate") + (this.event.useTime ? (" " + this.$t("hourMinute")) : ""));
    }
  },
}
</script>
