<template>
  <v-card
    v-if="item"
    flat
    class="secondary lighten-5 mx-auto"
    width="500"
    height="140"
  >
    <div
      class="d-flex "
    >
      <v-avatar
        size="140"
        tile
      >
        <v-img
          :src="(item['images'][0]) ? item['images'][0]['versions']['square_250'] : item.community ? item.community.image : '/images/avatarsDefault/avatar.svg'"
          class="grey lighten-2"
          :alt="$t('imageEvent',{name:name})"
        />
      </v-avatar>

      <div
        style="min-width:225px;max-width:300px"
        class="d-flex flex-column align-self-center"
      >
        <v-card-title
          class="text-left text-uppercase text-truncate font-weight-bold"
        >
          <h6 class="text-uppercase text-truncate">
            <a
              :href="linkToEventShow(item)"
              :aria-label="$t('linkToEvent')"
              :title="$t('linkToEvent')"
              style="text-decoration:none;"
              class="black--text"
            >
              {{ item.name }}
            </a>
          </h6>
        </v-card-title>
        <v-card-subtitle class="text-left">
          <span class="black--text font-italic">
            {{ item.address.addressLocality }}
          </span>
          <p
            v-if="dateLine1"
          >
            <span class="text-left black--text font-weight-bold">{{ dateLine1 }}</span>
            <span
              v-if="dateLine2"
              class="black--text font-weight-bold"
            >{{ dateLine2 }}</span>
          </p>
        </v-card-subtitle>
      </div>
      <v-card-actions>
        <v-spacer />
        <v-btn
          icon
          x-large
          color="black"
          :href="linkToEventShow(item)"
          :aria-label="$t('linkToEvent')"
        >
          <v-icon>
            mdi-chevron-right
          </v-icon>
        </v-btn>
      </v-card-actions>
    </div>
  </v-card>
</template>
<script>

import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MEventsListItem/";

export default {
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
      dateLine2:null,
      name: this.item.name
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
