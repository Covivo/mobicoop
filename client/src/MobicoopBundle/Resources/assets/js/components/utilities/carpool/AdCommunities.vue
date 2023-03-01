<template>
  <div>
    <v-tooltip
      v-for="community in uniqueCommunities"
      :key="community.id"
      color="info"
      right
    >
      <template v-slot:activator="{ on }">
        <v-list-item-avatar
          class="grey lighten-2 ml-1 mr-1"
          contain
          size="35"
          v-on="on"
        >
          <a
            :href="$t('uri',{communityId:community.id})"
            alt=""
          >
            <v-img
              v-if="community.image"
              :src="community.image"
              alt="avatar"
            />
            <v-img
              v-else
              class="grey lighten-2"
              src="/images/avatarsDefault/avatar.svg"
              alt="avatar"
            />
          </a>
        </v-list-item-avatar>
      </template>
      <span> {{ community.name }}</span>
    </v-tooltip>
  </div>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/carpool/AdCommunities";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    communities:{
      type: Array,
      default: () => []
    }
  },
  computed:{
    uniqueCommunities(){
      return new Set(this.communities);
    },
  }
}
</script>
