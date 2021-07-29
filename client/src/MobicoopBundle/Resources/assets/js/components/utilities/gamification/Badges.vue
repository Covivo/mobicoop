<template>
  <v-container fluid>
    <v-row
      no-gutters
    >
      <v-col
        cols="12"
        class="text-left title"
      >
        {{ $t('badgesTitle') }}
      </v-col>
    </v-row>
    <v-row
      no-gutters
    >
      <v-col
        v-for="badge in badges"
        :key="badge.id"
        cols="6"
      >
        <v-row
          justify="center"
          align="center"
        >
          <v-col cols="4">
            <v-avatar
              size="60"
            >
              <v-img :src="badge.icon.avatar" />
            </v-avatar>
          </v-col>
          <v-col
            class="text-left"
          >
            <p class="mt-4 ml-n12">
              {{ badge.title }}
            </p>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/gamification/Badges/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    badges:{
      type:Array,
      default: null
    }
  }
}
</script>