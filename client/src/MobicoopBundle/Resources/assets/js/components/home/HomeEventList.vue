<template>
  <div>
    <v-row>
      <!-- events -->
      <v-col
        cols="4"
        md="8"
        lg="4"
        xl="4"
        class="text-left"
      >
        <p class="success--text text-h4 font-weight-black">
          {{ $t('events.title') }}
        </p>
        <v-btn
          rounded
          color="secondary"
          :href="this.$t('events.button1.route')"
          class="white--text"
        >
          {{ $t('events.button1.label') }}
        </v-btn>
        <v-btn
          rounded
          color="secondary"
          :href="this.$t('events.button2.route')"
          class="white--text mt-4"
        >
          {{ $t('events.button2.label') }}
        </v-btn>
        <img
          class="mt-6"
          :src="imageLink + 'van_evenement.svg'"
        >
      </v-col>
      <v-col
        v-for="item in eventscoming"
        :key="item.index"
        cols="8"
        outlined
        tile
        class=""
      >
        <HomeEventListItem
          :item="item"
        />
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col
        cols="6"
        class="text-center mt-16"
      >
        <p class="success--text text-h4 font-weight-black">
          {{ $t('privateEvent.title') }}
        </p>
        <p class="success--text text-h6 font-italic mt-n4">
          {{ $t('privateEvent.subtitle') }}
        </p>
        <p>
          {{ $t('privateEvent.text') }}
        </p>
        <v-btn
          rounded
          color="secondary"
          :href="this.$t('privateEvent.button.route')"
          class="white--text mt-4"
        >
          {{ $t('privateEvent.button.label') }}
        </v-btn>
        <p class="mt-4">
          <a
            :href="this.$t('privateEvent.coviEvent.link')"
          >
            {{ $t('privateEvent.coviEvent.text') }}
          </a>
        </p>
      </v-col>
    </v-row>
    <!-- end events -->
  </div>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/home/HomeContent/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/home/HomeContent/";
import HomeEventListItem from "@components/home/HomeEventListItem";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  components:{
    HomeEventListItem
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
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
