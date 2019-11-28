<template>
  <v-card v-if="item">
    <v-row>
      <v-col cols="3">
        <v-img
          v-if="item['images'][0]"
          :src="item['images'][0]['versions']['square_250']"
          lazy-src="https://picsum.photos/id/11/10/6"
          aspect-ratio="1"
          class="grey lighten-2"
          max-width="200"
          max-height="150"
        />
        <v-img
          v-else
          src="https://picsum.photos/id/11/500/300"
          lazy-src="https://picsum.photos/id/11/10/6"
          aspect-ratio="1"
          class="grey lighten-2"
          max-width="200"
          max-height="150"
        />
      </v-col>
      <v-col cols="6">
        <v-card-title>
          <div>
            <h4>
              <a :href="linkToEventShow(item)">{{ item.name }}</a>
            </h4>
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
  methods:{
    linkToEventShow: function (item) {
      return this.$t('routes.event', {id:item.id});
    }
  }
}
</script>
