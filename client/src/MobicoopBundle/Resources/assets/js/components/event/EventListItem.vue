<template>
  <v-card v-if="item">
    <v-row>
      <v-col cols="6">
        <v-card-title>
          <div>
            <h4>
              <a :href="linkToCommunityShow(item)">{{ item.name }}</a>
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
            :href="linkToCommunityShow(item)"
          >
            {{ $t('communityDetails') }}
          </v-btn>
        </div>
      </v-col>
    </v-row>
  </v-card>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/event/EventListItem.json";
import TranslationsClient from "@clientTranslations/components/event/EventListItem.json";
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
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
    linkToCommunityShow: function (item) {
      console.info(item.id)
      console.info(item.name)
      console.info(item.images)
      return this.$t('routes.community', {id:item.id});
    }
  }
}
</script>