<template>
  <v-card v-if="item">
    <v-row>
      <v-col cols="3">
        <v-img
          v-if="item['images'][0]"
          :src="item['images'][0]['versions']['square_250']"
          aspect-ratio="1"
          class="grey lighten-2"
          max-width="200"
          max-height="150"
        />
        <v-img
          v-else
          src="/images/avatarsDefault/avatar.svg"
          aspect-ratio="1"
          class="grey lighten-2"
          max-width="200"
          max-height="200"
        />
      </v-col>
      <v-col cols="6">
        <v-card-title>
          <div>
            <h4>
              <a :href="linkToEventShow(item)">{{ item.name }}</a>
            </h4>
            <p class="headline text-justify subtitle-1">
              {{ item.address.addressLocality }}
            </p>
            <p v-if="dateLine1">
              <span class="subtitle-1">{{ dateLine1 }}</span><br>
              <span
                v-if="dateLine2"
                class="subtitle-1"
              >{{ dateLine2 }}</span>
            </p>
          </div>
        </v-card-title>
        <v-divider />
        <v-list dense>
          <v-list-item>
            <v-list-item-content>
              {{ item.fullDescription }}
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-col>
      <v-col
        cols="3"
        class="text-center"
      >
        <div
          class="my-2"
        >
          <v-btn
            color="secondary"
            rounded
            :href="linkToEventShow(item)"
          >
            {{ $t('eventDetails') }}
          </v-btn>
        </div>
        <div
          class="mt-5"
        >
          <EventReport
            :event="item"
          />
        </div>
      </v-col>
    </v-row>
  </v-card>
</template>
<script>

import { merge } from "lodash";
import moment from "moment";
import EventReport from "@components/event/EventReport";
import Translations from "@translations/components/event/EventListItem.json";
import TranslationsClient from "@clientTranslations/components/event/EventListItem.json";
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components:{
    EventReport
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    item:{
      type: Object,
      default: null
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
      dateLine1:null,
      dateLine2:null
    };
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    this.computeEventDate();
  },
  methods:{
    linkToEventShow: function (item) {
      return this.$t('routes.event', {id:item.id});
    },
    computeEventDate(){
      let fromDate = moment(this.item.fromDate.date).format(this.$t("ui.i18n.date.format.shortCompleteDate"));
      let toDate = moment(this.item.toDate.date).format(this.$t("ui.i18n.date.format.shortCompleteDate"));
      
      if(fromDate === toDate){
        this.dateLine1 = this.$t("date.the")+" "+fromDate+" "+this.$t("date.at")+" "+moment(this.item.fromDate.date).format(this.$t("ui.i18n.time.format.hourMinute"));
      }
      else{
        this.dateLine1 = this.$t("date.from")+" "+fromDate+" "+this.$t("date.at")+" "+moment(this.item.fromDate.date).format(this.$t("ui.i18n.time.format.hourMinute"));
        this.dateLine2 = this.$t("date.to")+" "+toDate+" "+this.$t("date.at")+" "+moment(this.item.toDate.date).format(this.$t("ui.i18n.time.format.hourMinute"));
      }
    }
  }
}
</script>
