<template>
  <v-row align="center">
    <v-col
      cols="4"
      md="4"
      justify="center"
      align="center"
    >
      <v-img
        :src="(community.images[0] && community['images'][0]['versions']['square_250']) ? community['images'][0]['versions']['square_250'] : urlAltAvatar"
        width="225"
        height="200"
        :aspect-ratio="1"
        :alt="community.name"
        contain
      />
    </v-col>

    <v-col
      cols="8"
      md="8"
    >
      <v-card
        flat
        justify="center"
      >
        <v-card-text>
          <h3 class="text-h5 text-justify font-weight-bold">
            {{ community.name }}
          </h3>
          <v-chip
            v-if="community.nbMembers"
            class="ma-2"
            color="secondary"
            small
            label
          >
            {{ $t('infos.members', {members:community.nbMembers}) }}
          </v-chip>
          <p
            class="text-body-1 text-justify"
            v-html="community.description"
          />
          <p
            class="text-body-2 text-justify"
            v-html="community.fullDescription"
          />
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>
<script>
import {messages_en, messages_fr, messages_eu} from "@translations/components/community/Community/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props: {
    community: {
      type: Object,
      default: null,
    },
    urlAltAvatar: {
      type: String,
      default: null,
    },
    avatarVersion: {
      type: String,
      default: null,
    }
  },
};
</script>
