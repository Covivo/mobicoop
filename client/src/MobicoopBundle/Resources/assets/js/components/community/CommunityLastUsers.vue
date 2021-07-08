<template>
  <div>
    <p
      class="text-h5 text-justify text-no-wrap font-weight-bold mt-6"
    >
      {{ $t('title') }}
    </p>

    <div v-if="!hidden">
      <v-list
        v-if="!loading"
        shaped
      >
        <v-list-item-group class="text-end">
          <v-list-item
            v-for="(comUser, i) in lastUsers"
            :key="i"
          >
            <v-list-item-avatar>
              <v-avatar color="tertiary">
                <v-icon light>
                  mdi-account-circle
                </v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-content v-text="comUser.name" />
              <v-list-item-content v-text="comUser.acceptedDate" />
            </v-list-item-content>
          </v-list-item>
        </v-list-item-group>
      </v-list>
      <div
        v-else
        align="center"
        justify="center"
      >
        <v-progress-circular
          indeterminate
          color="tertiary"
        />
      </div>
    </div>
    <v-card-text v-else>
      {{ $t('hidden') }}
    </v-card-text>
  </div>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/CommunityLastUsers/";

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
    community: {
      type: Object,
      default: null
    },
    refresh: {
      type: Boolean,
      default: false
    },
    hidden: {
      type: Boolean,
      default: false
    },
    givenLastUsers: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      lastUsers: this.givenLastUsers ? this.givenLastUsers : null,
      loading: false
    }
  },
}
</script>
