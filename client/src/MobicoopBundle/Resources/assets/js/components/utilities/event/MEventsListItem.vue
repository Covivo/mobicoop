<template>
  <v-card
    v-if="item"
    flat
    class="secondary lighten-5"
  >
    <v-row
      justify="start"
      align="center"
    >
      <v-col
        cols="3"
        class="pa-0"
      >
        <v-img
          v-if="item['images'][0]"
          :src="item['images'][0]['versions']['square_100']"
          class="grey lighten-2"
          contain
          max-width="100"
          max-height="100"
        />
        <v-img
          v-else
          src="/images/avatarsDefault/avatar.svg"
          class="grey lighten-2"
          max-width="100"
          max-height="100"
        />
      </v-col>
      <v-col
        cols="5"
        align="left"
        class="ml-4"
      >
        <v-card-title>
          <h4 class="text-uppercase ml-n4">
            {{ item.name }}
          </h4>
        </v-card-title>
        <v-card-subtitle class="pa-0">
          <span class="text-subtitle-1 black--text font-italic">
            {{ item.address.addressLocality }}
          </span>
          <p
            v-if="dateLine1"
            class="pa-0 ma-0"
          >
            <span class="text-subtitle-1 black--text font-weight-bold">{{ dateLine1 }}</span>
            <span
              v-if="dateLine2"
              class="text-subtitle-1 black--text font-weight-bold"
            >{{ dateLine2 }}</span>
          </p>
        </v-card-subtitle>
      </v-col>
      <v-col
        cols="3"
        class="text-center align-self-center"
        justify="center"
        align="center"
      >
        <v-btn
          icon
          x-large
          color="black"

          :href="linkToEventShow(item)"
        >
          <v-icon>
            mdi-chevron-right
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>
  </v-card>
</template>
<script>

import moment from "moment";
import {messages_en, messages_fr} from "@translations/components/utilities/MEventsListItem/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    },
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
      let fromDate = moment(this.item.fromDate.date).format(this.$t("shortCompleteDate"));
      let toDate = moment(this.item.toDate.date).format(this.$t("shortCompleteDate"));
      
      if(fromDate === toDate){
        this.dateLine1 = this.$t("date.the")+" "+fromDate+" ";
      }
      else{
        this.dateLine1 = this.$t("date.from")+" "+fromDate+" ";
        this.dateLine2 = this.$t("date.to")+" "+toDate+" ";
      }
    }
  }
}
</script>
