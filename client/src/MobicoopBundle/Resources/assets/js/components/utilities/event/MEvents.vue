<template>
  <v-container>
    <v-row
      dense
    >
      <v-col
        v-for="item in eventscoming"
        :key="item.index"
        cols="12"
      >
        <MEventsListItem
          :item="item"
        />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/home/HomeContent/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/home/HomeContent/";
import MEventsListItem from "@components/utilities/event/MEventsListItem";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  components:{
    MEventsListItem
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  props: {
    eventDisplay: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      imageLink: "/images/pages/home/",
      coming:true,
      perPage:3,
      page:1,
      eventscoming:[]
    }
  },
  mounted() {
    //cherche les 3 évenements à venir;
    this.getEvents(this.coming);
  },
  methods:{
    getEvents(coming){
      let params = {
        'coming':coming,
        'fromDate':this.fromDate,
        'perPage':this.perPage,
        'page':this.page
      }
      axios
        .post(this.$t('routes.getList'),params)
        .then(response => {
          // console.error(response.data);
          this.eventscoming = response.data.eventComing;
        })
        .catch(function (error) {
          console.error(error);
        });        
    },
  }
}
</script>

<style lang="scss" scoped>
</style>
