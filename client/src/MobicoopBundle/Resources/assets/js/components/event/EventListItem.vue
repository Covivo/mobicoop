<template>
  <v-card v-if="item">
    <v-row>
      <v-col
        cols="3"
        class="text-center"
      >
        <EventImage :event="item" />
        <v-row
          v-if="item.community"
          class="mt-8"
        >
          <v-col
            cols="2"
            class="ml-8 mr-4"
          >
            <v-avatar>
              <v-img :src="item.community.image" />
            </v-avatar>
          </v-col>
          <v-col cols="5">
            {{ item.community.name }}
          </v-col>
        </v-row>
      </v-col>

      <v-col
        cols="6"
        md="4"
        lg="5"
        xl="6"
      >
        <v-card-title>
          <div>
            <h4>
              <a :href="linkToEventShow(item)">{{ item.name }}</a>
            </h4>
            <p class="text-h5 text-justify text-subtitle-1">
              {{ item.address.addressLocality }}
            </p>
            <p v-if="dateLine1">
              <span class="text-subtitle-1">{{ dateLine1 }}</span><br>
              <span
                v-if="dateLine2"
                class="text-subtitle-1"
              >{{ dateLine2 }}</span>
            </p>
          </div>
        </v-card-title>
        <v-divider v-if="item.description && item.description !== 'null'" />
        <v-list
          v-if="item.description && item.description !== 'null'"
          dense
        >
          <v-list-item>
            <v-list-item-content class="text-justify">
              {{ item.description }}
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
            width="231px"
          >
            {{ $t('eventDetails') }}
          </v-btn>
        </div>
        <div
          class="mt-5"
        >
          <Report
            :event="item"
          />
        </div>
      </v-col>
    </v-row>
  </v-card>
</template>
<script>

import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/event/EventListItem/";
import Report from "@components/utilities/Report";
import EventImage from "./EventImage.vue";

export default {
  components:{
    EventImage,
    Report
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
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
      locale: localStorage.getItem("X-LOCALE"),
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
      return this.$t('routes.event', {id:item.id, urlKey:item.urlKey});
    },
    computeEventDate(){
      let fromDate = moment(this.item.fromDate.date).format(this.$t("shortCompleteDate"));
      let toDate = moment(this.item.toDate.date).format(this.$t("shortCompleteDate"));

      if(fromDate === toDate){
        if (this.item.useTime) {
          this.dateLine1 = this.$t("date.the")+" "+fromDate+" "+this.$t("date.at")+" "+moment(this.item.fromDate.date).format(this.$t("hourMinute"));
        }
        else {
          this.dateLine1 = this.$t("date.the")+" "+fromDate;
        }
      }
      else{
        if (this.item.useTime) {
          this.dateLine1 = this.$t("date.from")+" "+fromDate+" "+this.$t("date.at")+" "+moment(this.item.fromDate.date).format(this.$t("hourMinute"));
          this.dateLine2 = this.$t("date.to")+" "+toDate+" "+this.$t("date.at")+" "+moment(this.item.toDate.date).format(this.$t("hourMinute"));
        }
        else {
          this.dateLine1 = this.$t("date.from")+" "+fromDate;
          this.dateLine2 = this.$t("date.to")+" "+toDate;
        }
      }

    }
  }
}
</script>
